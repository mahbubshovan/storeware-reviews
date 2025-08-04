import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';

const LatestReviews = ({ selectedApp, refreshKey }) => {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchLatestReviews = async () => {
      // Don't fetch if no app is selected
      if (!selectedApp) {
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        const response = await reviewsAPI.getLatestReviews(selectedApp);
        setReviews(response.data.reviews);
        setError(null);
      } catch (err) {
        setError('Failed to fetch latest reviews');
        console.error('Error fetching reviews:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchLatestReviews();
  }, [selectedApp, refreshKey]);

  const renderStars = (rating) => {
    return '⭐'.repeat(rating);
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
      <h2>Latest Reviews</h2>
      
      <div className="reviews-list">
        {reviews.map((review, index) => (
          <div key={index} className="review-item">
            <div className="review-header">
              <div className="review-meta">
                <span><strong>{review.store_name}</strong></span>
                <span>{review.country_name}</span>
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
