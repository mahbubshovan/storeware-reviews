import { useState, useEffect } from 'react';
import './Access.css';

const AccessTabbed = () => {
  // App configuration
  const apps = [
    { name: 'StoreSEO', slug: 'storeseo' },
    { name: 'StoreFAQ', slug: 'storefaq' },
    { name: 'EasyFlow', slug: 'product-options-4' },
    { name: 'TrustSync', slug: 'customer-review-app' },
    { name: 'Vidify', slug: 'vidify' },
    { name: 'BetterDocs FAQ Knowledge Base', slug: 'betterdocs-knowledgebase' }
  ];

  // State management
  const [activeTab, setActiveTab] = useState('StoreSEO');
  const [reviews, setReviews] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Pagination state
  const [pagination, setPagination] = useState({
    current_page: 1,
    total_pages: 0,
    total_items: 0,
    items_per_page: 15,
    has_next_page: false,
    has_prev_page: false,
    page_numbers: []
  });
  
  // Current page for each tab
  const [tabPages, setTabPages] = useState({
    'StoreSEO': 1,
    'StoreFAQ': 1,
    'EasyFlow': 1,
    'TrustSync': 1,
    'Vidify': 1,
    'BetterDocs FAQ Knowledge Base': 1
  });
  

  
  // Edit state
  const [editingReview, setEditingReview] = useState(null);
  const [editValue, setEditValue] = useState('');
  const [scrollPosition, setScrollPosition] = useState(0);

  useEffect(() => {
    fetchTabReviews(activeTab, tabPages[activeTab]);
  }, [activeTab]);



  const fetchTabReviews = async (appName, page = 1) => {
    setLoading(true);
    setError(null);

    try {
      // Use the new cached API for fast tab switching and accurate counts
      const response = await fetch(
        `http://localhost:8000/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=${page}&limit=15&_t=${Date.now()}&_cache_bust=${Math.random()}`
      );

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        setReviews(data.data.reviews || []);
        setPagination(data.data.pagination || {});
        setStatistics(data.data.statistics || {});
      } else {
        throw new Error(data.error || 'Failed to fetch reviews');
      }
    } catch (err) {
      console.error('Error fetching reviews:', err);
      setError(err.message);
      setReviews([]);
    } finally {
      setLoading(false);
    }
  };

  const handleTabChange = (appName) => {
    if (appName !== activeTab) {
      setActiveTab(appName);
      // Use the stored page for this tab
      fetchTabReviews(appName, tabPages[appName]);
    }
  };

  const handlePageChange = (newPage) => {
    // Update the page for current tab
    setTabPages(prev => ({
      ...prev,
      [activeTab]: newPage
    }));
    fetchTabReviews(activeTab, newPage);
  };

  const handleEditClick = (review) => {
    setScrollPosition(window.pageYOffset);
    setEditingReview(review.id);
    setEditValue(review.earned_by || '');
  };

  const handleEditSave = async (reviewId) => {
    try {
      const response = await fetch('http://localhost:8000/api/access-reviews-tabbed.php', {
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
        // Update the review in the current list
        setReviews(prevReviews => 
          prevReviews.map(review => 
            review.id === reviewId 
              ? { ...review, earned_by: editValue.trim() }
              : review
          )
        );
        setEditingReview(null);
        setEditValue('');
        
        // Restore scroll position
        setTimeout(() => {
          window.scrollTo(0, scrollPosition);
        }, 100);
      } else {
        alert('Error updating assignment: ' + data.error);
      }
    } catch (error) {
      console.error('Error updating assignment:', error);
      alert('Error updating assignment');
    }
  };

  const handleEditCancel = () => {
    setEditingReview(null);
    setEditValue('');
    setTimeout(() => {
      window.scrollTo(0, scrollPosition);
    }, 100);
  };

  const formatDate = (dateString) => {
    if (!dateString || dateString === '1970-01-01') return 'Unknown Date';
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
    // Handle formats like "StoreName\n      \n          CountryName"
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
    const stars = [];
    const numRating = parseInt(rating);

    // Handle invalid ratings
    if (isNaN(numRating) || numRating < 1 || numRating > 5) {
      return <span className="invalid-rating">❓</span>;
    }

    for (let i = 1; i <= 5; i++) {
      stars.push(
        <span key={i} className={i <= numRating ? 'star filled' : 'star'}>
          ★
        </span>
      );
    }
    return stars;
  };



  return (
    <div className="access-container">
      <div className="access-header">
        <h1>Access Reviews - App Tabs</h1>
        <p>Browse reviews with name assignments by app</p>
        
        {statistics && (
          <div className="tab-statistics">
            <div className="stat-item">
              <span className="stat-label">Total Reviews:</span>
              <span className="stat-value">{statistics.total_reviews}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Assigned:</span>
              <span className="stat-value">{statistics.assigned_reviews}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Unassigned:</span>
              <span className="stat-value">{statistics.unassigned_reviews}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Avg Rating:</span>
              <span className="stat-value">{statistics.avg_rating}★</span>
            </div>
            {statistics.cache_status && (
              <div className="stat-item">
                <span className="stat-label">Data:</span>
                <span className={`stat-value cache-${statistics.cache_status}`}>
                  {statistics.cache_status === 'hit' ? '⚡ Cached' : '🔄 Fresh'}
                </span>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Tab Navigation */}
      <div className="tab-navigation">
        {apps.map(app => (
          <button
            key={app.name}
            className={`tab-button ${activeTab === app.name ? 'active' : ''}`}
            onClick={() => handleTabChange(app.name)}
          >
            {app.name}
          </button>
        ))}
      </div>

      {/* Tab Content */}
      <div className="tab-content">
        {loading ? (
          <div className="loading-message">
            <p>Loading {activeTab} reviews...</p>
          </div>
        ) : error ? (
          <div className="error-message">
            <p>Error: {error}</p>
            <button onClick={() => fetchTabReviews(activeTab, tabPages[activeTab])}>
              Retry
            </button>
          </div>
        ) : (
          <>
            <div className="reviews-header">
              <h2>{activeTab} Reviews ({pagination.total_items} assigned)</h2>
              <p>Page {pagination.current_page} of {pagination.total_pages}</p>
            </div>

            {reviews.length === 0 ? (
              <div className="no-reviews">
                <p>No assigned reviews found for {activeTab}</p>
              </div>
            ) : (
              <div className="reviews-list">
                {reviews.map((review) => (
                  <div key={review.id} className="review-item">
                    <div className="review-header">
                      <div className="review-meta">
                        <span className="store-name">{review.store_name}</span>
                        <span className="review-date">{formatDate(review.review_date)}</span>
                        <span className="country">{getCountryName(review.country_name)}</span>
                      </div>
                      <div className="review-rating">
                        {renderStars(review.rating)}
                      </div>
                    </div>
                    
                    <div className="review-content">
                      <p>{review.review_content}</p>
                    </div>
                    
                    <div className="review-assignment">
                      <label>Assigned to:</label>
                      {editingReview === review.id ? (
                        <div className="edit-assignment">
                          <input
                            type="text"
                            value={editValue}
                            onChange={(e) => setEditValue(e.target.value)}
                            placeholder="Enter name"
                            className="assignment-input"
                            autoFocus
                          />
                          <button 
                            onClick={() => handleEditSave(review.id)}
                            className="save-btn"
                          >
                            Save
                          </button>
                          <button 
                            onClick={handleEditCancel}
                            className="cancel-btn"
                          >
                            Cancel
                          </button>
                        </div>
                      ) : (
                        <span 
                          className="assignment-value clickable"
                          onClick={() => handleEditClick(review)}
                          title="Click to edit"
                        >
                          {review.earned_by || 'Unassigned'}
                        </span>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Pagination */}
            {pagination.total_pages > 1 && (
              <div className="pagination">
                <button
                  onClick={() => handlePageChange(pagination.current_page - 1)}
                  disabled={!pagination.has_prev_page}
                  className="pagination-btn"
                >
                  Previous
                </button>
                
                {pagination.page_numbers.map(pageNum => (
                  <button
                    key={pageNum}
                    onClick={() => handlePageChange(pageNum)}
                    className={`pagination-btn ${pageNum === pagination.current_page ? 'active' : ''}`}
                  >
                    {pageNum}
                  </button>
                ))}
                
                <button
                  onClick={() => handlePageChange(pagination.current_page + 1)}
                  disabled={!pagination.has_next_page}
                  className="pagination-btn"
                >
                  Next
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default AccessTabbed;
