import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';
import { useFreshData } from '../hooks/useFreshData';

const LatestReviews = ({ selectedApp, refreshKey }) => {
  // Clean up country names from messy database format
  const getCountryName = (countryData) => {
    if (!countryData || countryData === 'Unknown') {
      return 'Unknown';
    }

    // Clean up the country data - extract country name from mixed format
    const cleanCountry = countryData
      .split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .pop(); // Get the last non-empty line (usually the country)

    // Map common country variations to clean names with flags (short format for latest reviews)
    const countryMap = {
      'United States': '🇺🇸 US',
      'Canada': '🇨🇦 CA',
      'United Kingdom': '🇬🇧 UK',
      'Australia': '🇦🇺 AU',
      'Germany': '🇩🇪 DE',
      'France': '🇫🇷 FR',
      'South Africa': '🇿🇦 ZA',
      'India': '🇮🇳 IN',
      'Japan': '🇯🇵 JP',
      'Singapore': '🇸🇬 SG',
      'Costa Rica': '🇨🇷 CR',
      'Netherlands': '🇳🇱 NL',
      'Sweden': '🇸🇪 SE',
      'Norway': '🇳🇴 NO',
      'Denmark': '🇩🇰 DK',
      'Finland': '🇫🇮 FI',
      'Belgium': '🇧🇪 BE',
      'Switzerland': '🇨🇭 CH',
      'Austria': '🇦🇹 AT',
      'Ireland': '🇮🇪 IE'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [lastUpdated, setLastUpdated] = useState(null);

  // Use fresh data hook for intelligent data management
  const { isRefreshing, refreshData, needsRefresh } = useFreshData(selectedApp);

  const fetchLatestReviews = async (forceFresh = false) => {
    // Don't fetch if no app is selected
    if (!selectedApp) {
      setLoading(false);
      return;
    }

    try {
      setLoading(true);

      const response = forceFresh
        ? await reviewsAPI.getFreshReviews(selectedApp)
        : await reviewsAPI.getLatestReviews(selectedApp);

      if (forceFresh && response.data.success) {
        setReviews(response.data.reviews);
      } else if (!forceFresh) {
        setReviews(response.data.reviews);
      }

      setLastUpdated(new Date().toLocaleString());
      setError(null);
    } catch (err) {
      setError(forceFresh ? 'Failed to fetch fresh reviews' : 'Failed to fetch latest reviews');
      console.error('Error fetching reviews:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleRefreshClick = async () => {
    const freshData = await refreshData(selectedApp, true);
    if (freshData && freshData.reviews) {
      setReviews(freshData.reviews);
      setLastUpdated(new Date().toLocaleString());
    }
  };

  useEffect(() => {
    fetchLatestReviews();
  }, [selectedApp, refreshKey]);

  const renderStars = (rating) => {
    const numRating = parseInt(rating);
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return '❓'; // Show question mark for invalid/missing ratings
    }
    return '⭐'.repeat(numRating);
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  if (loading) {
    return <div className="loading">Loading latest reviews...</div>;
  }

  if (error) {
    return <div className="error">{error}</div>;
  }

  if (reviews.length === 0) {
    return (
      <section className="latest-reviews">
        <h2>Latest Reviews</h2>
        <p>No reviews found. Start by scraping some Shopify app reviews!</p>
      </section>
    );
  }

  return (
    <section className="latest-reviews">
      <div className="latest-reviews-header">
        <h2>Latest Reviews</h2>
        <div className="reviews-controls">
          {lastUpdated && (
            <span className="last-updated">
              Last updated: {lastUpdated}
            </span>
          )}
          <button
            onClick={handleRefreshClick}
            disabled={isRefreshing || loading}
            className="refresh-btn"
          >
            {isRefreshing ? '🔄 Refreshing...' : '🔄 Refresh Data'}
          </button>
        </div>
      </div>

      <div className="reviews-list">
        {reviews.map((review, index) => (
          <div key={index} className="review-item">
            <div className="review-header">
              <div className="review-meta">
                <span><strong>{review.store_name}</strong></span>
                <span>{getCountryName(review.country_name)}</span>
                <span>{renderStars(review.rating)}</span>
                <span>{formatDate(review.review_date)}</span>
              </div>
            </div>
            <div className="review-content">
              {review.review_content}
            </div>
          </div>
        ))}
      </div>
    </section>
  );
};

export default LatestReviews;
