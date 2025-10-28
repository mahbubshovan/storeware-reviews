import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';
import './Access.css';

const Access = () => {
  const [reviews, setReviews] = useState({});
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  // Password protection state
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [password, setPassword] = useState('');
  const [passwordError, setPasswordError] = useState('');

  const [editingReview, setEditingReview] = useState(null);
  const [editValue, setEditValue] = useState('');
  const [scrollPosition, setScrollPosition] = useState(0);

  useEffect(() => {
    if (isAuthenticated) {
      fetchAccessReviews();
    }
  }, [isAuthenticated]);

  const handlePasswordSubmit = (e) => {
    e.preventDefault();
    if (password === 'admin123') {
      setIsAuthenticated(true);
      setPasswordError('');
    } else {
      setPasswordError('Invalid password. Please try again.');
      setPassword('');
    }
  };

  const fetchAccessReviews = async () => {
    try {
      setLoading(true);
      const response = await reviewsAPI.getAccessReviews();

      if (response.data.success) {
        setReviews(response.data.reviews || {});
        setStats(response.data.stats || null);
      } else {
        console.error('Failed to fetch access reviews:', response.data.message);
      }
    } catch (error) {
      console.error('Error fetching access reviews:', error);
    } finally {
      setLoading(false);
    }
  };



  const handleEditStart = (reviewId, currentValue) => {
    // Save current scroll position before starting edit
    setScrollPosition(window.pageYOffset || document.documentElement.scrollTop);
    setEditingReview(reviewId);
    setEditValue(currentValue || '');
  };

  const handleEditSave = async (reviewId) => {
    try {
      const response = await reviewsAPI.updateEarnedBy(reviewId, editValue.trim());
      const data = response.data;

      if (data.success) {
        // Update local state immediately instead of refetching all data
        const updatedReviews = { ...reviews };
        const newEarnedBy = editValue.trim();

        // Find and update the specific review in the local state
        Object.keys(updatedReviews).forEach(appName => {
          updatedReviews[appName] = updatedReviews[appName].map(review => {
            if (review.id === reviewId) {
              return { ...review, earned_by: newEarnedBy };
            }
            return review;
          });
        });

        setReviews(updatedReviews);

        // Update stats to reflect the change
        if (stats) {
          // Find the original review to check if it was previously assigned
          let wasAssigned = false;
          for (const appName of Object.keys(reviews)) {
            const review = reviews[appName].find(r => r.id === reviewId);
            if (review) {
              wasAssigned = review.earned_by && review.earned_by.trim() !== '';
              break;
            }
          }

          const newStats = { ...stats };
          if (!wasAssigned && newEarnedBy) {
            // Was unassigned, now assigned
            newStats.assigned_reviews += 1;
            newStats.unassigned_reviews -= 1;
          } else if (wasAssigned && !newEarnedBy) {
            // Was assigned, now unassigned
            newStats.assigned_reviews -= 1;
            newStats.unassigned_reviews += 1;
          }
          setStats(newStats);
        }

        setEditingReview(null);
        setEditValue('');

        // Restore scroll position after a brief delay to ensure DOM updates
        setTimeout(() => {
          window.scrollTo(0, scrollPosition);
        }, 50);
      } else {
        console.error('Failed to update earned_by:', data.message);
      }
    } catch (error) {
      console.error('Error updating earned_by:', error);
    }
  };

  const handleEditCancel = () => {
    setEditingReview(null);
    setEditValue('');

    // Restore scroll position when canceling edit
    setTimeout(() => {
      window.scrollTo(0, scrollPosition);
    }, 50);
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
  };

  const renderStars = (rating) => {
    const numRating = parseInt(rating);
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return '❓'; // Show question mark for invalid/missing ratings
    }
    return '⭐'.repeat(numRating);
  };

  const getAppColor = (index) => {
    const colors = [
      '#4f46e5', // Indigo
      '#059669', // Emerald
      '#dc2626', // Red
      '#d97706', // Amber
      '#7c3aed', // Violet
      '#0891b2', // Cyan
      '#be185d', // Pink
      '#65a30d', // Lime
    ];
    return colors[index % colors.length];
  };

  const truncateContent = (content, maxLength = 100) => {
    if (content.length <= maxLength) return content;
    return content.substring(0, maxLength) + '...';
  };

  const getCountryName = (countryData) => {
    // Since we now have accurate country data, handle edge cases gracefully
    if (!countryData || countryData.trim() === '') {
      return '🌍 Unknown Location';
    }

    // Clean up the country data - extract country name from mixed format
    // Handle formats like "StoreName\n      \n          CountryName"
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
      'South Africa': '🇿🇦 South Africa',
      'India': '🇮🇳 India',
      'Japan': '🇯🇵 Japan',
      'Singapore': '🇸🇬 Singapore',
      'Costa Rica': '🇨🇷 Costa Rica',
      'Netherlands': '🇳🇱 Netherlands',
      'Sweden': '🇸🇪 Sweden',
      'Norway': '🇳🇴 Norway',
      'Denmark': '🇩🇰 Denmark',
      'Finland': '🇫🇮 Finland',
      'Belgium': '🇧🇪 Belgium',
      'Switzerland': '🇨🇭 Switzerland',
      'Austria': '🇦🇹 Austria',
      'Ireland': '🇮🇪 Ireland',
      'Brazil': '🇧🇷 Brazil',
      'Italy': '🇮🇹 Italy',
      'Spain': '🇪🇸 Spain'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };

  // Show password form if not authenticated
  if (!isAuthenticated) {
    return (
      <div className="access-container">
        <div className="password-protection-container">
          <div className="password-form-card">
            <h2>Access Reviews</h2>
            <p>This page is password protected. Please enter the password to continue.</p>

            <form onSubmit={handlePasswordSubmit} className="password-form">
              <div className="password-input-group">
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter password"
                  className="password-input"
                  autoFocus
                />
                <button type="submit" className="password-submit-btn">
                  Access
                </button>
              </div>
              {passwordError && (
                <div className="password-error">
                  {passwordError}
                </div>
              )}
            </form>
          </div>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="loading-container">
        <div className="loading-spinner"></div>
        <span>Loading access reviews...</span>
      </div>
    );
  }

  return (
    <div className="access-container">
      <div className="access-content">
        {/* Header */}
        <div className="access-header">
          <div className="access-header-content">
            <h1>Access Reviews</h1>
          </div>
        </div>

        {/* Stats Cards */}
        {stats && (
          <div className="stats-grid">
            <div className="stat-card">
              <p className="stat-label">Total Reviews</p>
              <p className="stat-value total">{stats.shopify_total_reviews || stats.total_reviews}</p>
            </div>

            <div className="stat-card">
              <p className="stat-label">Assigned</p>
              <p className="stat-value assigned">{stats.assigned_reviews}</p>
            </div>

            <div className="stat-card">
              <p className="stat-label">Unassigned</p>
              <p className="stat-value unassigned">{stats.unassigned_reviews}</p>
            </div>

            <div className="stat-card">
              <p className="stat-label">Apps</p>
              <p className="stat-value apps">{stats.reviews_by_app.length}</p>
            </div>
          </div>
        )}

        {/* App-wise Review Counts */}
        {stats && stats.reviews_by_app && stats.reviews_by_app.length > 0 && (
          <div className="app-counts-section">
            <h3 className="app-counts-title">Review Counts by App (Last 30 Days)</h3>
            <div className="app-counts-grid">
              {stats.reviews_by_app.map((app, index) => (
                <div key={app.app_name} className="app-count-card">
                  <div className="app-count-header">
                    <h4 className="app-count-name">{app.app_name}</h4>
                    <span className="app-count-badge">{app.count} reviews</span>
                  </div>
                  <div className="app-count-details">
                    <div className="app-count-bar">
                      <div
                        className="app-count-fill"
                        style={{
                          width: `${(app.count / stats.total_reviews) * 100}%`,
                          backgroundColor: getAppColor(index)
                        }}
                      ></div>
                    </div>
                    <div className="app-count-percentage">
                      {((app.count / stats.total_reviews) * 100).toFixed(1)}% of total
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Reviews by App */}
        <div className="access-content">
          {Object.keys(reviews).length === 0 ? (
            <div className="app-section">
              <h3>No Reviews Found</h3>
              <p>No reviews from the last 30 days. Reviews will appear here automatically when apps are scraped.</p>
            </div>
          ) : (
            Object.entries(reviews).map(([appName, appReviews]) => {
              // Count assigned reviews for this app
              const assignedCount = appReviews.filter(r => r.earned_by && r.earned_by.trim() !== '').length;
              const totalCount = appReviews.length;

              return (
              <div key={appName} className="app-section">
                <div className="app-section-header">
                  <div className="app-section-title">
                    <h2>{appName} Reviews Details</h2>
                    <span className="app-badge">{assignedCount} assigned / {totalCount} total</span>
                  </div>
                </div>
                <div className="reviews-grid">
                  {appReviews.map((review) => (
                    <div key={review.id} className="review-item">
                      <div className="review-header">
                        <div className="review-meta">
                          <div className="store-name">{review.app_name}</div>
                          <div className="meta-info">
                            <span>{formatDate(review.review_date)}</span>
                            <span>{getCountryName(review.country_name)}</span>
                            <span>{renderStars(review.rating)}</span>
                            <div className="review-id">ID: {review.id}</div>
                          </div>
                        </div>
                        <div className="earned-by-section">
                          <span className="earned-by-label">Earned By:</span>
                          {editingReview === review.id ? (
                            <div className="action-buttons">
                              <input
                                type="text"
                                value={editValue}
                                onChange={(e) => setEditValue(e.target.value)}
                                placeholder="Enter name..."
                                className="earned-by-input"
                                style={{
                                  padding: '8px 14px',
                                  border: '1px solid #D1D5DB',
                                  borderRadius: '20px',
                                  fontSize: '14px',
                                  background: '#F9FAFB',
                                  color: '#1F2937',
                                  minWidth: '140px',
                                  fontWeight: '500',
                                  transition: 'all 0.2s ease'
                                }}
                                onKeyDown={(e) => {
                                  if (e.key === 'Enter') {
                                    handleEditSave(review.id);
                                  } else if (e.key === 'Escape') {
                                    handleEditCancel();
                                  }
                                }}
                                autoFocus
                              />
                              <button
                                onClick={() => handleEditSave(review.id)}
                                className="btn-save"
                                style={{
                                  background: '#10B981',
                                  color: 'white',
                                  border: 'none',
                                  padding: '8px 18px',
                                  borderRadius: '20px',
                                  fontSize: '13px',
                                  fontWeight: '600',
                                  cursor: 'pointer',
                                  transition: 'all 0.2s ease'
                                }}
                              >
                                Save
                              </button>
                              <button
                                onClick={handleEditCancel}
                                className="btn-cancel"
                                style={{
                                  background: '#F3F4F6',
                                  color: '#6B7280',
                                  border: '1px solid #D1D5DB',
                                  padding: '8px 18px',
                                  borderRadius: '20px',
                                  fontSize: '13px',
                                  fontWeight: '600',
                                  cursor: 'pointer',
                                  transition: 'all 0.2s ease'
                                }}
                              >
                                Cancel
                              </button>
                            </div>
                          ) : (
                            <div
                              onClick={() => handleEditStart(review.id, review.earned_by)}
                            >
                              {review.earned_by ? (
                                <span
                                  className="earned-by-display"
                                  style={{
                                    background: '#F0FDF4',
                                    color: '#15803D',
                                    padding: '6px 16px',
                                    borderRadius: '20px',
                                    fontSize: '14px',
                                    fontWeight: '600',
                                    cursor: 'pointer',
                                    border: '1px solid #DCFCE7',
                                    display: 'inline-block'
                                  }}
                                >
                                  {review.earned_by}
                                </span>
                              ) : (
                                <span
                                  className="earned-by-empty"
                                  style={{
                                    color: '#9CA3AF',
                                    cursor: 'pointer',
                                    padding: '6px 16px',
                                    border: '1px dashed #D1D5DB',
                                    borderRadius: '20px',
                                    fontSize: '14px',
                                    background: 'transparent',
                                    display: 'inline-block'
                                  }}
                                >
                                  Click to assign
                                </span>
                              )}
                            </div>
                          )}
                        </div>
                      </div>
                      <div className="review-content">
                        {truncateContent(review.review_content)}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            );
            })
          )}
        </div>
      </div>
    </div>
  );
};

export default Access;
