import React, { useState, useEffect } from 'react';
import './Access.css';

const Access = () => {
  const [reviews, setReviews] = useState({});
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [editingReview, setEditingReview] = useState(null);
  const [editValue, setEditValue] = useState('');

  useEffect(() => {
    fetchAccessReviews();
  }, []);

  const fetchAccessReviews = async () => {
    try {
      setLoading(true);
      const response = await fetch('http://localhost:8000/api/access-reviews.php');
      const data = await response.json();
      
      if (data.success) {
        setReviews(data.reviews || {});
        setStats(data.stats || null);
      } else {
        console.error('Failed to fetch access reviews:', data.message);
      }
    } catch (error) {
      console.error('Error fetching access reviews:', error);
    } finally {
      setLoading(false);
    }
  };

  const syncAccessReviews = async () => {
    try {
      setSyncing(true);
      const response = await fetch('http://localhost:8000/api/sync-access-reviews.php', {
        method: 'POST'
      });
      const data = await response.json();
      
      if (data.success) {
        await fetchAccessReviews();
      } else {
        console.error('Failed to sync access reviews:', data.message);
      }
    } catch (error) {
      console.error('Error syncing access reviews:', error);
    } finally {
      setSyncing(false);
    }
  };

  const handleEditStart = (reviewId, currentValue) => {
    setEditingReview(reviewId);
    setEditValue(currentValue || '');
  };

  const handleEditSave = async (reviewId) => {
    try {
      const response = await fetch('http://localhost:8000/api/update-earned-by.php', {
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
        await fetchAccessReviews();
        setEditingReview(null);
        setEditValue('');
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
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
  };

  const truncateContent = (content, maxLength = 100) => {
    if (content.length <= maxLength) return content;
    return content.substring(0, maxLength) + '...';
  };

  const getCountryName = (countryCode) => {
    const countryNames = {
      'US': 'United States',
      'CA': 'Canada',
      'UK': 'United Kingdom',
      'AU': 'Australia',
      'DE': 'Germany',
      'FR': 'France',
      'NL': 'Netherlands',
      'SE': 'Sweden',
      'NO': 'Norway',
      'DK': 'Denmark',
      'FI': 'Finland',
      'BE': 'Belgium',
      'CH': 'Switzerland',
      'AT': 'Austria',
      'IE': 'Ireland'
    };
    return countryNames[countryCode] || countryCode;
  };

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
            <p>
              Manage and assign reviews from the last 30 days across all apps
            </p>
          </div>
          <button
            onClick={syncAccessReviews}
            disabled={syncing}
            className="sync-button"
          >
            {syncing ? 'Syncing...' : 'Sync Reviews'}
          </button>
        </div>

        {/* Stats Cards */}
        {stats && (
          <div className="stats-grid">
            <div className="stat-card">
              <p className="stat-label">Total Reviews</p>
              <p className="stat-value total">{stats.total_reviews}</p>
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

        {/* Reviews by App */}
        <div className="access-content">
          {Object.keys(reviews).length === 0 ? (
            <div className="app-section">
              <h3>No Reviews Found</h3>
              <p>No reviews from the last 30 days. Try syncing to get the latest data.</p>
            </div>
          ) : (
            Object.entries(reviews).map(([appName, appReviews]) => (
              <div key={appName} className="app-section">
                <h2>
                  {appName}
                  <span className="app-badge">{appReviews.length} reviews</span>
                </h2>
                <div className="reviews-grid">
                  {appReviews.map((review) => (
                    <div key={review.id} className="review-item">
                      <div className="review-header">
                        <div className="review-meta">
                          <div className="store-name">{review.app_name}</div>
                          <div className="meta-info">
                            <span>{formatDate(review.review_date)}</span>
                            <span>{getCountryName(review.country_name)}</span>
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
                                onKeyPress={(e) => {
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
                              >
                                Save
                              </button>
                              <button
                                onClick={handleEditCancel}
                                className="btn-cancel"
                              >
                                Cancel
                              </button>
                            </div>
                          ) : (
                            <div
                              onClick={() => handleEditStart(review.id, review.earned_by)}
                            >
                              {review.earned_by ? (
                                <span className="earned-by-display">{review.earned_by}</span>
                              ) : (
                                <span className="earned-by-empty">Click to assign</span>
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
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default Access;
