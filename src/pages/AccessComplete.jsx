import { useState, useEffect } from 'react';
import './Access.css';

const AccessComplete = () => {
  const [reviews, setReviews] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [availableApps, setAvailableApps] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Pagination state
  const [pagination, setPagination] = useState({
    current_page: 1,
    total_pages: 0,
    total_items: 0,
    items_per_page: 20,
    has_next_page: false,
    has_prev_page: false,
    page_numbers: []
  });
  
  // Filter state
  const [filters, setFilters] = useState({
    app: 'all',
    show_assigned: 'all',
    group_by_app: 'true',
    search: '',
    page: 1,
    limit: 20
  });
  

  
  // Edit state
  const [editingReview, setEditingReview] = useState(null);
  const [editValue, setEditValue] = useState('');
  const [scrollPosition, setScrollPosition] = useState(0);

  useEffect(() => {
    fetchAccessReviews();
  }, [filters]);



  const fetchAccessReviews = async () => {
    try {
      setLoading(true);
      setError(null);

      const params = new URLSearchParams(filters);
      const response = await fetch(`/backend/api/access-reviews-complete.php?${params}`);
      const data = await response.json();

      if (data.success) {
        setReviews(data.data.reviews);
        setStatistics(data.data.statistics);
        setAvailableApps(data.data.available_apps);
        setPagination(data.data.pagination);
      } else {
        throw new Error(data.error || 'Failed to fetch access reviews');
      }
    } catch (err) {
      setError(err.message);
      console.error('Error fetching access reviews:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (filterType, value) => {
    setFilters(prev => ({ 
      ...prev, 
      [filterType]: value,
      page: filterType !== 'page' ? 1 : value // Reset to page 1 when changing filters
    }));
  };

  const handlePageChange = (page) => {
    if (page >= 1 && page <= pagination.total_pages) {
      handleFilterChange('page', page);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const searchTerm = e.target.search.value.trim();
    handleFilterChange('search', searchTerm);
  };

  const startEdit = (reviewId, currentValue) => {
    setScrollPosition(window.pageYOffset);
    setEditingReview(reviewId);
    setEditValue(currentValue || '');
  };

  const cancelEdit = () => {
    setEditingReview(null);
    setEditValue('');
    window.scrollTo(0, scrollPosition);
  };

  const saveEdit = async (reviewId) => {
    try {
      const response = await fetch('/backend/api/access-reviews-complete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          earned_by: editValue.trim()
        })
      });

      const data = await response.json();
      
      if (data.success) {
        // Update the local state
        if (filters.group_by_app === 'true') {
          setReviews(prevReviews => 
            prevReviews.map(appGroup => ({
              ...appGroup,
              reviews: appGroup.reviews.map(review => 
                review.id === reviewId 
                  ? { ...review, earned_by: editValue.trim() }
                  : review
              )
            }))
          );
        } else {
          setReviews(prevReviews => 
            prevReviews.map(review => 
              review.id === reviewId 
                ? { ...review, earned_by: editValue.trim() }
                : review
            )
          );
        }
        
        setEditingReview(null);
        setEditValue('');
        window.scrollTo(0, scrollPosition);
      } else {
        alert('Failed to update assignment: ' + data.error);
      }
    } catch (error) {
      console.error('Error updating assignment:', error);
      alert('Error updating assignment. Please try again.');
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

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

  const renderStars = (rating) => {
    const numRating = parseInt(rating);
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return '❓'; // Show question mark for invalid/missing ratings
    }
    return '⭐'.repeat(numRating);
  };



  if (loading) {
    return (
      <div className="access-page">
        <div className="loading-container">
          <div className="loading-spinner"></div>
          <p>Loading complete review database...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="access-page">
        <div className="error-container">
          <h2>Error Loading Reviews</h2>
          <p>{error}</p>
          <button onClick={fetchAccessReviews} className="retry-button">
            Retry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="access-page">
      <div className="access-header">
        <h1>Complete Access Reviews</h1>
        <p>Comprehensive review management system - All {statistics?.total_reviews || 0} reviews in database</p>
      </div>

      {/* Statistics */}
      {statistics && (
        <div className="access-stats">
          <div className="stat-card">
            <div className="stat-number">{statistics.total_reviews}</div>
            <div className="stat-label">Total Reviews</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{statistics.assigned_reviews}</div>
            <div className="stat-label">Assigned</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{statistics.unassigned_reviews}</div>
            <div className="stat-label">Unassigned</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{statistics.average_rating}⭐</div>
            <div className="stat-label">Avg Rating</div>
          </div>
          <div className="stat-card">
            <div className="stat-number">{statistics.total_apps}</div>
            <div className="stat-label">Apps</div>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="access-filters">
        <div className="filter-row">
          <div className="filter-group">
            <label>App Filter:</label>
            <select 
              value={filters.app} 
              onChange={(e) => handleFilterChange('app', e.target.value)}
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
            <label>Assignment Status:</label>
            <select 
              value={filters.show_assigned} 
              onChange={(e) => handleFilterChange('show_assigned', e.target.value)}
            >
              <option value="all">All Reviews</option>
              <option value="unassigned">Unassigned Only</option>
              <option value="assigned">Assigned Only</option>
            </select>
          </div>

          <div className="filter-group">
            <label>Display Mode:</label>
            <select 
              value={filters.group_by_app} 
              onChange={(e) => handleFilterChange('group_by_app', e.target.value)}
            >
              <option value="true">Grouped by App</option>
              <option value="false">Flat List</option>
            </select>
          </div>

          <div className="filter-group">
            <label>Per Page:</label>
            <select 
              value={filters.limit} 
              onChange={(e) => handleFilterChange('limit', parseInt(e.target.value))}
            >
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>

        <div className="filter-row">
          <form onSubmit={handleSearch} className="search-form">
            <input
              type="text"
              name="search"
              placeholder="Search reviews, stores, or apps..."
              defaultValue={filters.search}
              className="search-input"
            />
            <button type="submit" className="search-button">Search</button>
            {filters.search && (
              <button 
                type="button" 
                onClick={() => handleFilterChange('search', '')}
                className="clear-search"
              >
                Clear
              </button>
            )}
          </form>
        </div>
      </div>

      {/* Pagination Info */}
      <div className="pagination-info">
        Showing {pagination.start_item}-{pagination.end_item} of {pagination.total_items} reviews
        (Page {pagination.current_page} of {pagination.total_pages})
      </div>

      {/* Reviews Display */}
      <div className="reviews-container">
        {reviews.length === 0 ? (
          <div className="no-reviews">
            <h3>No Reviews Found</h3>
            <p>No reviews match your current filters. Try adjusting your search criteria.</p>
          </div>
        ) : filters.group_by_app === 'true' ? (
          // Grouped by app display
          reviews.map((appGroup, appIndex) => (
            <div key={appIndex} className="app-group">
              <div className="app-group-header">
                <h2>{appGroup.app_name}</h2>
                <div className="app-stats">
                  <span>Total: {appGroup.stats.total}</span>
                  <span>Assigned: {appGroup.stats.assigned}</span>
                  <span>Unassigned: {appGroup.stats.unassigned}</span>
                  <span>Avg Rating: {appGroup.stats.average_rating}⭐</span>
                </div>
              </div>
              
              <div className="reviews-grid">
                {appGroup.reviews.map((review) => (
                  <div key={review.id} className="review-item">
                    <div className="review-header">
                      <div className="review-meta">
                        <span className="store-name"><strong>{review.store_name}</strong></span>
                        <span className="country">{getCountryName(review.country_name)}</span>
                        <span className="rating">{renderStars(review.rating)}</span>
                        <span className="date">{formatDate(review.review_date)}</span>
                        <span className={`badge ${review.time_category}`}>{review.time_category}</span>
                      </div>
                    </div>
                    
                    <div className="review-content">
                      {review.review_content}
                    </div>
                    
                    <div className="review-assignment">
                      <label>Earned By:</label>
                      {editingReview === review.id ? (
                        <div className="edit-controls">
                          <input
                            type="text"
                            value={editValue}
                            onChange={(e) => setEditValue(e.target.value)}
                            placeholder="Enter name..."
                            autoFocus
                          />
                          <button onClick={() => saveEdit(review.id)} className="save-btn">Save</button>
                          <button onClick={cancelEdit} className="cancel-btn">Cancel</button>
                        </div>
                      ) : (
                        <span 
                          className={`earned-by ${!review.earned_by ? 'unassigned' : ''}`}
                          onClick={() => startEdit(review.id, review.earned_by)}
                        >
                          {review.earned_by || 'Click to assign'}
                        </span>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))
        ) : (
          // Flat list display
          <div className="reviews-list">
            {reviews.map((review, index) => (
              <div key={index} className="review-item">
                <div className="review-header">
                  <div className="review-meta">
                    <span className="app-name">{review.app_name}</span>
                    <span className="store-name"><strong>{review.store_name}</strong></span>
                    <span className="country">{getCountryName(review.country_name)}</span>
                    <span className="rating">{renderStars(review.rating)}</span>
                    <span className="date">{formatDate(review.review_date)}</span>
                    <span className={`badge ${review.time_category}`}>{review.time_category}</span>
                  </div>
                </div>
                
                <div className="review-content">
                  {review.review_content}
                </div>
                
                <div className="review-assignment">
                  <label>Earned By:</label>
                  {editingReview === review.id ? (
                    <div className="edit-controls">
                      <input
                        type="text"
                        value={editValue}
                        onChange={(e) => setEditValue(e.target.value)}
                        placeholder="Enter name..."
                        autoFocus
                      />
                      <button onClick={() => saveEdit(review.id)} className="save-btn">Save</button>
                      <button onClick={cancelEdit} className="cancel-btn">Cancel</button>
                    </div>
                  ) : (
                    <span 
                      className={`earned-by ${!review.earned_by ? 'unassigned' : ''}`}
                      onClick={() => startEdit(review.id, review.earned_by)}
                    >
                      {review.earned_by || 'Click to assign'}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Pagination */}
      {pagination.total_pages > 1 && (
        <div className="pagination">
          <button 
            onClick={() => handlePageChange(pagination.current_page - 1)}
            disabled={!pagination.has_prev_page}
            className="pagination-btn prev"
          >
            ← Previous
          </button>

          <div className="page-numbers">
            {pagination.page_numbers.map(pageNum => (
              <button
                key={pageNum}
                onClick={() => handlePageChange(pageNum)}
                className={`page-number ${pageNum === pagination.current_page ? 'active' : ''}`}
              >
                {pageNum}
              </button>
            ))}
          </div>

          <button 
            onClick={() => handlePageChange(pagination.current_page + 1)}
            disabled={!pagination.has_next_page}
            className="pagination-btn next"
          >
            Next →
          </button>
        </div>
      )}
    </div>
  );
};

export default AccessComplete;
