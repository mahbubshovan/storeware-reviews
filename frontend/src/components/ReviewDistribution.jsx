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
        return;
      }

      try {
        setLoading(true);
        const response = await reviewsAPI.getReviewDistribution(selectedApp);
        setDistribution(response.data);
        setError(null);
      } catch (err) {
        setError('Failed to fetch review distribution');
        console.error('Error fetching distribution:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchDistribution();
  }, [selectedApp, refreshKey]);

  if (loading) {
    return <div className="loading">Loading review distribution...</div>;
  }

  if (error) {
    return <div className="error">{error}</div>;
  }

  const { total_reviews, distribution: dist } = distribution;

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
        <div className="distribution-item">
          <div className="star-label">⭐⭐⭐⭐⭐</div>
          <div className="count">{dist.five_star}</div>
        </div>

        <div className="distribution-item">
          <div className="star-label">⭐⭐⭐⭐</div>
          <div className="count">{dist.four_star}</div>
        </div>

        <div className="distribution-item">
          <div className="star-label">⭐⭐⭐</div>
          <div className="count">{dist.three_star}</div>
        </div>

        <div className="distribution-item">
          <div className="star-label">⭐⭐</div>
          <div className="count">{dist.two_star}</div>
        </div>

        <div className="distribution-item">
          <div className="star-label">⭐</div>
          <div className="count">{dist.one_star}</div>
        </div>
      </div>
    </section>
  );
};

export default ReviewDistribution;
