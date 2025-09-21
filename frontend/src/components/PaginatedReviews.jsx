import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';

const PaginatedReviews = ({ selectedApp, refreshKey }) => {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [pagination, setPagination] = useState({
    current_page: 1,
    total_pages: 0,
    total_items: 0,
    items_per_page: 10,
    has_next_page: false,
    has_prev_page: false,
    page_numbers: []
  });
  const [filters, setFilters] = useState({
    app: selectedApp || 'all',
    rating: null,
    sort: 'newest'
  });
  const [availableApps, setAvailableApps] = useState([]);

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

    // Map common country variations to clean names
    const countryMap = {
      'United States': '🇺🇸 United States',
      'Canada': '🇨🇦 Canada',
      'United Kingdom': '🇬🇧 United Kingdom',
      'Australia': '🇦🇺 Australia',
      'Germany': '🇩🇪 Germany',
      'France': '🇫🇷 France',
      'India': '🇮🇳 India',
      'Brazil': '🇧🇷 Brazil',
      'Netherlands': '🇳🇱 Netherlands',
      'Spain': '🇪🇸 Spain',
      'Italy': '🇮🇹 Italy',
      'Japan': '🇯🇵 Japan',
      'South Korea': '🇰🇷 South Korea',
      'Mexico': '🇲🇽 Mexico',
      'Argentina': '🇦🇷 Argentina',
      'Switzerland': '🇨🇭 Switzerland',
      'Austria': '🇦🇹 Austria',
      'Ireland': '🇮🇪 Ireland'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const renderStars = (rating) => {
    const numRating = parseInt(rating);
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return '❓'; // Show question mark for invalid/missing ratings
    }
    return '⭐'.repeat(numRating);
  };

  const fetchPaginatedReviews = async (page = 1, newFilters = null) => {
    try {
      setLoading(true);
      setError(null);

      const currentFilters = newFilters || filters;

      // Don't fetch if no app is selected and we're not showing all apps
      if (!selectedApp && (!currentFilters.app || currentFilters.app === 'all')) {
        setLoading(false);
        setReviews([]);
        setPagination({
          current_page: 1,
          total_pages: 0,
          total_items: 0,
          items_per_page: 10,
          has_next_page: false,
          has_prev_page: false,
          page_numbers: []
        });
        return;
      }

      const params = {
        page,
        limit: 10,
        ...currentFilters
      };

      // Remove null/empty values
      Object.keys(params).forEach(key => {
        if (params[key] === null || params[key] === '' || params[key] === 'all') {
          delete params[key];
        }
      });

      const response = await reviewsAPI.getPaginatedReviews(params);
      
      if (response.data.success) {
        setReviews(response.data.data.reviews);
        setPagination(response.data.data.pagination);
        setAvailableApps(response.data.data.available_apps);
        setFilters(response.data.data.filters);
      } else {
        throw new Error(response.data.error || 'Failed to fetch reviews');
      }
    } catch (err) {
      // Only show error if it's not a timeout from a previous request
      if (!err.message?.includes('timeout') || selectedApp) {
        setError(`Failed to fetch paginated reviews: ${err.message || 'Unknown error'}`);
        console.error('Error fetching paginated reviews for', selectedApp, ':', err.message);
      }
    } finally {
      setLoading(false);
    }
  };

  const handlePageChange = (page) => {
    if (page >= 1 && page <= pagination.total_pages) {
      fetchPaginatedReviews(page);
    }
  };

  const handleFilterChange = (filterType, value) => {
    const newFilters = { ...filters, [filterType]: value };
    setFilters(newFilters);
    fetchPaginatedReviews(1, newFilters); // Reset to page 1 when filtering
  };

  const handleAppFilterChange = (appName) => {
    handleFilterChange('app', appName);
  };

  const handleRatingFilterChange = (rating) => {
    handleFilterChange('rating', rating === filters.rating ? null : rating);
  };

  const handleSortChange = (sortOrder) => {
    handleFilterChange('sort', sortOrder);
  };

  useEffect(() => {
    const handleAppChange = () => {
      // Update app filter when selectedApp changes
      if (selectedApp !== filters.app) {
        const newFilters = { ...filters, app: selectedApp || 'all' };
        setFilters(newFilters);
        fetchPaginatedReviews(1, newFilters);
      } else if (selectedApp) {
        // Only fetch if an app is selected
        fetchPaginatedReviews();
      } else {
        // Clear reviews if no app selected
        setReviews([]);
        setLoading(false);
        setError(null);
      }
    };

    // Debounce the app change to prevent rapid API calls
    const timeoutId = setTimeout(handleAppChange, 500);
    return () => clearTimeout(timeoutId);
  }, [selectedApp, refreshKey]);

  if (loading && reviews.length === 0) {
    return <div className="loading">Loading reviews...</div>;
  }

  if (error) {
    return <div className="error">Error: {error}</div>;
  }

  return (
    <section className="paginated-reviews">
      <div className="reviews-header">
        <h2>Latest Reviews</h2>
        <div className="reviews-stats">
          {pagination.total_items > 0 && (
            <span className="stats-text">
              Showing {pagination.start_item}-{pagination.end_item} of {pagination.total_items} reviews
            </span>
          )}
        </div>
      </div>

      {/* Filters */}
      <div className="reviews-filters">
        <div className="filter-group">
          <label>App:</label>
          <select 
            value={filters.app || 'all'} 
            onChange={(e) => handleAppFilterChange(e.target.value)}
            className="filter-select"
          >
            <option value="all">All Apps</option>
            {availableApps.map(app => (
              <option key={app.app_name} value={app.app_name}>
                {app.app_name} ({app.review_count})
              </option>
            ))}
          </select>
        </div>

        <div className="filter-group">
          <label>Rating:</label>
          <div className="rating-filters">
            {[5, 4, 3, 2, 1].map(rating => (
              <button
                key={rating}
                onClick={() => handleRatingFilterChange(rating)}
                className={`rating-filter ${filters.rating === rating ? 'active' : ''}`}
              >
                {rating}⭐
              </button>
            ))}
            {filters.rating && (
              <button 
                onClick={() => handleRatingFilterChange(null)}
                className="clear-rating"
              >
                Clear
              </button>
            )}
          </div>
        </div>

        <div className="filter-group">
          <label>Sort:</label>
          <select 
            value={filters.sort || 'newest'} 
            onChange={(e) => handleSortChange(e.target.value)}
            className="filter-select"
          >
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="rating_high">Highest Rating</option>
            <option value="rating_low">Lowest Rating</option>
          </select>
        </div>
      </div>

      {/* Reviews List */}
      <div className="reviews-list">
        {reviews.length === 0 ? (
          <div className="no-reviews">
            <p>No reviews found matching your criteria.</p>
          </div>
        ) : (
          reviews.map((review, index) => (
            <div key={`${review.id}-${index}`} className="review-item">
              <div className="review-header">
                <div className="review-meta">
                  <span className="app-name">{review.app_name}</span>
                  <span className="store-name"><strong>{review.store_name}</strong></span>
                  <span className="country">{getCountryName(review.country_name)}</span>
                  <span className="rating">{renderStars(review.rating)}</span>
                  <span className="date">{formatDate(review.review_date)}</span>
                  {review.time_category === 'recent' && (
                    <span className="badge recent">Recent</span>
                  )}
                  {review.is_featured && (
                    <span className="badge featured">Featured</span>
                  )}
                  {review.earned_by && (
                    <span className="badge assigned">Assigned to {review.earned_by}</span>
                  )}
                </div>
              </div>
              <div className="review-content">
                {review.review_content}
              </div>
            </div>
          ))
        )}
      </div>

      {/* Pagination */}
      {pagination.total_pages > 1 && (
        <div className="pagination">
          <button 
            onClick={() => handlePageChange(pagination.current_page - 1)}
            disabled={!pagination.has_prev_page || loading}
            className="pagination-btn prev"
          >
            ← Previous
          </button>

          <div className="page-numbers">
            {pagination.page_numbers.map(pageNum => (
              <button
                key={pageNum}
                onClick={() => handlePageChange(pageNum)}
                disabled={loading}
                className={`page-number ${pageNum === pagination.current_page ? 'active' : ''}`}
              >
                {pageNum}
              </button>
            ))}
          </div>

          <button 
            onClick={() => handlePageChange(pagination.current_page + 1)}
            disabled={!pagination.has_next_page || loading}
            className="pagination-btn next"
          >
            Next →
          </button>
        </div>
      )}

      {loading && reviews.length > 0 && (
        <div className="loading-overlay">Loading...</div>
      )}
    </section>
  );
};

export default PaginatedReviews;
