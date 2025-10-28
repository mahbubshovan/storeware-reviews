import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';

const ReviewDistribution = ({ selectedApp, refreshKey }) => {
  const [distribution, setDistribution] = useState({
    total_reviews: 0,
    distribution: {
      five_star: 0,
      four_star: 0,
      three_star: 0,
      two_star: 0,
      one_star: 0
    }
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchDistribution = async () => {
      // Don't fetch if no app is selected
      if (!selectedApp) {
        setLoading(false);
        setError(null);
        return;
      }

      try {
        setLoading(true);
        setError(null);

        // Add a small delay to prevent rapid-fire requests
        await new Promise(resolve => setTimeout(resolve, 150));

        const response = await reviewsAPI.getReviewDistribution(selectedApp);
        setDistribution(response.data);
        setError(null);
        console.log('📈 Distribution loaded for', selectedApp);
      } catch (err) {
        // Only show error if it's not a timeout from a previous request
        if (!err.message?.includes('timeout') || selectedApp) {
          setError(`Failed to fetch review distribution: ${err.message || 'Unknown error'}`);
          console.error('Error fetching distribution for', selectedApp, ':', err.message);
        }
      } finally {
        setLoading(false);
      }
    };

    // Debounce the API call
    const timeoutId = setTimeout(fetchDistribution, 400);
    return () => clearTimeout(timeoutId);
  }, [selectedApp, refreshKey]);

  if (loading) {
    return <div className="loading">Loading review distribution...</div>;
  }

  if (error) {
    return <div className="error">{error}</div>;
  }

  const { total_reviews, distribution: dist } = distribution;

  // Calculate max count for progress bar
  const maxCount = Math.max(dist.five_star, dist.four_star, dist.three_star, dist.two_star, dist.one_star);

  const renderDistributionItem = (stars, count, label) => {
    const percentage = maxCount > 0 ? (count / maxCount) * 100 : 0;
    return (
      <div key={label} className="distribution-item">
        <div className="star-label">{stars}</div>
        <div className="distribution-bar-container">
          <div className="distribution-bar">
            <div
              className="distribution-bar-fill"
              style={{ width: `${percentage}%` }}
            ></div>
          </div>
        </div>
        <div className="count">{count}</div>
      </div>
    );
  };

  return (
    <section className="review-distribution">
      <div className="distribution-header">
        <h2>Rating Distribution</h2>
        <div className="total-reviews-badge">
          <span className="total-count">{total_reviews}</span>
          <span className="total-label">Total Reviews</span>
        </div>
      </div>

      <div className="distribution-grid">
        {renderDistributionItem('5 ⭐', dist.five_star, 'five')}
        {renderDistributionItem('4 ⭐', dist.four_star, 'four')}
        {renderDistributionItem('3 ⭐', dist.three_star, 'three')}
        {renderDistributionItem('2 ⭐', dist.two_star, 'two')}
        {renderDistributionItem('1 ⭐', dist.one_star, 'one')}
      </div>
    </section>
  );
};

export default ReviewDistribution;
