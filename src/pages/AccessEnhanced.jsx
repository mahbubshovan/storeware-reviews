import { useState, useEffect } from 'react';
import './Access.css';

const AccessEnhanced = () => {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [statistics, setStatistics] = useState({});
  const [availableApps, setAvailableApps] = useState([]);
  const [filters, setFilters] = useState({
    app: 'all',
    date_range: 'last_30_days',
    show_assigned: 'all',
    group_by_app: 'true'
  });

  const fetchAccessReviews = async () => {
    try {
      setLoading(true);
      setError(null);

      const params = new URLSearchParams(filters);
      const response = await fetch(`/api/access-reviews-enhanced.php?${params}`);
      const data = await response.json();

      if (data.success) {
        setReviews(data.data.reviews);
        setStatistics(data.data.statistics);
        setAvailableApps(data.data.available_apps);
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
    setFilters(prev => ({ ...prev, [filterType]: value }));
  };

  const handleAssignmentUpdate = async (reviewId, earnedBy) => {
    try {
      const response = await fetch('/api/access-reviews-enhanced.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          earned_by: earnedBy,
          assigned_by: 'user'
        })
      });

      const data = await response.json();
      
      if (data.success) {
        // Refresh the reviews to show updated assignments
        fetchAccessReviews();
      } else {
        throw new Error(data.error || 'Failed to update assignment');
      }
    } catch (err) {
      console.error('Error updating assignment:', err);
      alert('Failed to update assignment: ' + err.message);
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

  const renderStars = (rating) => {
    const numRating = parseInt(rating);
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return '❓'; // Show question mark for invalid/missing ratings
    }
    return '⭐'.repeat(numRating);
  };

  const getCountryName = (countryData) => {
    // Since we now have accurate country data, handle edge cases gracefully
    if (!countryData || countryData.trim() === '') {
      return '🌍 Unknown Location';
    }

    // Clean up the country data - extract country name from mixed format
    const cleanCountry = countryData
      .split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .pop(); // Get the last non-empty line (usually the country)

    // Map common country variations to clean names with flags
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
      'Ireland': '🇮🇪 Ireland',
      'South Africa': '🇿🇦 South Africa',
      'Sweden': '🇸🇪 Sweden',
      'Norway': '🇳🇴 Norway',
      'Denmark': '🇩🇰 Denmark'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };

  useEffect(() => {
    fetchAccessReviews();
  }, [filters]);

  if (loading) {
    return <div className="loading">Loading access reviews...</div>;
  }

  if (error) {
    return <div className="error">Error: {error}</div>;
  }

  return (
    <div className="access-page">
      <div className="access-header">
        <h1>Access Reviews - Enhanced</h1>
        <p>Permanent repository of all reviews with advanced filtering and assignment management</p>
      </div>

      {/* Statistics Summary */}
      <div className="statistics-summary">
        <div className="stat-card">
          <h3>Total Reviews</h3>
          <span className="stat-number">{statistics.total_reviews || 0}</span>
        </div>
        <div className="stat-card">
          <h3>Assigned</h3>
          <span className="stat-number assigned">{statistics.assigned_reviews || 0}</span>
        </div>
        <div className="stat-card">
          <h3>Unassigned</h3>
          <span className="stat-number unassigned">{statistics.unassigned_reviews || 0}</span>
        </div>
        <div className="stat-card">
          <h3>Apps</h3>
          <span className="stat-number">{statistics.apps_count || 0}</span>
        </div>
      </div>

      {/* Filters */}
      <div className="access-filters">
        <div className="filter-group">
          <label>App:</label>
          <select 
            value={filters.app} 
            onChange={(e) => handleFilterChange('app', e.target.value)}
          >
            <option value="all">All Apps</option>
            {availableApps.map(app => (
              <option key={app.app_name} value={app.app_name}>
                {app.app_name} ({app.total_reviews})
              </option>
            ))}
          </select>
        </div>

        <div className="filter-group">
          <label>Date Range:</label>
          <select 
            value={filters.date_range} 
            onChange={(e) => handleFilterChange('date_range', e.target.value)}
          >
            <option value="last_30_days">Last 30 Days</option>
            <option value="current_month">Current Month</option>
            <option value="last_7_days">Last 7 Days</option>
            <option value="all">All Time</option>
          </select>
        </div>

        <div className="filter-group">
          <label>Assignment:</label>
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
          <label>
            <input
              type="checkbox"
              checked={filters.group_by_app === 'true'}
              onChange={(e) => handleFilterChange('group_by_app', e.target.checked ? 'true' : 'false')}
            />
            Group by App
          </label>
        </div>
      </div>

      {/* Reviews Display */}
      <div className="reviews-container">
        {filters.group_by_app === 'true' ? (
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
              
              <div className="reviews-list">
                {appGroup.reviews.map((review, reviewIndex) => (
                  <div key={`${appIndex}-${reviewIndex}`} className="review-item">
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
                    
                    <div className="assignment-section">
                      <label>Earned By:</label>
                      <input
                        type="text"
                        value={review.earned_by || ''}
                        onChange={(e) => handleAssignmentUpdate(review.id, e.target.value)}
                        placeholder="Enter name or leave empty"
                        className="assignment-input"
                      />
                      {review.assigned_at && (
                        <span className="assignment-date">
                          Assigned: {formatDate(review.assigned_at)}
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
                
                <div className="assignment-section">
                  <label>Earned By:</label>
                  <input
                    type="text"
                    value={review.earned_by || ''}
                    onChange={(e) => handleAssignmentUpdate(review.id, e.target.value)}
                    placeholder="Enter name or leave empty"
                    className="assignment-input"
                  />
                  {review.assigned_at && (
                    <span className="assignment-date">
                      Assigned: {formatDate(review.assigned_at)}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {reviews.length === 0 && (
        <div className="no-reviews">
          <p>No reviews found matching your criteria.</p>
          <p>Try adjusting your filters or check back later.</p>
        </div>
      )}
    </div>
  );
};

export default AccessEnhanced;
