import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';

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
