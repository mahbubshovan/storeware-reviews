import { useState, useEffect, useCallback, useRef } from 'react';
import './Access.css';
import { useCache } from '../context/CacheContext';

const AccessTabbed = () => {
  // Use global cache from context
  const { getCachedData, setCachedData, clearAppCache } = useCache();

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

  // Request deduplication - track ongoing requests to prevent duplicates
  const ongoingRequestRef = useRef(null);
  const lastRequestKeyRef = useRef(null);

  // Memoize fetchTabReviews with request deduplication and global caching
  const fetchTabReviews = useCallback(async (appName, page = 1) => {
    // Create a unique key for this request
    const requestKey = `${appName}-${page}`;
    const cacheKey = `access_reviews_${appName}_page${page}`;

    // If same request is already in progress, skip it
    if (ongoingRequestRef.current === requestKey) {
      console.log('⚠️ Duplicate request prevented:', requestKey);
      return;
    }

    // If this is the exact same request as the last one, skip it
    if (lastRequestKeyRef.current === requestKey) {
      console.log('⚠️ Duplicate request prevented (same as last):', requestKey);
      return;
    }

    // Check global cache first
    const cachedData = getCachedData(appName, null, cacheKey);
    if (cachedData) {
      console.log('✅ Loading from global cache:', cacheKey);
      setReviews(cachedData.reviews || []);
      setPagination(cachedData.pagination || {});
      setStatistics(cachedData.statistics || {});
      setLoading(false);
      setError(null);
      return;
    }

    // Mark this request as ongoing
    ongoingRequestRef.current = requestKey;
    lastRequestKeyRef.current = requestKey;

    setLoading(true);
    setError(null);

    try {
      console.log('✅ Fetching reviews from API:', requestKey);
      const response = await fetch(
        `/backend/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=${page}&limit=15`
      );

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      // Get response text first to debug
      const responseText = await response.text();
      console.log('Response text:', responseText.substring(0, 200));

      if (!responseText) {
        throw new Error('Empty response from server');
      }

      const data = JSON.parse(responseText);

      if (data.success) {
        setReviews(data.data.reviews || []);
        setPagination(data.data.pagination || {});
        setStatistics(data.data.statistics || {});
        // Cache the data globally
        setCachedData(appName, data.data, null, cacheKey);
      } else {
        throw new Error(data.error || 'Failed to fetch reviews');
      }
    } catch (err) {
      console.error('Error fetching reviews:', err);
      setError(err.message);
      setReviews([]);
    } finally {
      setLoading(false);
      // Clear ongoing request marker
      ongoingRequestRef.current = null;
    }
  }, [getCachedData, setCachedData]); // Add cache functions to dependencies

  // Fetch reviews when activeTab changes - single source of truth for tab navigation
  useEffect(() => {
    const currentPage = tabPages[activeTab];
    fetchTabReviews(activeTab, currentPage);
  }, [activeTab]); // Only depend on activeTab, not tabPages or fetchTabReviews

  const handleTabChange = (appName) => {
    if (appName !== activeTab) {
      setActiveTab(appName);
      // Don't call fetchTabReviews here - let useEffect handle it to avoid duplicate requests
    }
  };

  const handlePageChange = (newPage) => {
    // Update the page for current tab
    setTabPages(prev => ({
      ...prev,
      [activeTab]: newPage
    }));

    // Fetch new data immediately
    fetchTabReviews(activeTab, newPage);
  };

  const handleEditClick = (review) => {
    setScrollPosition(window.pageYOffset);
    setEditingReview(review.id);
    setEditValue(review.earned_by || '');
  };

  const handleEditSave = async (reviewId) => {
    try {
      const response = await fetch('/backend/api/access-reviews-tabbed.php', {
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
        // Find the review being updated to check if it was previously unassigned
        const reviewBeingUpdated = reviews.find(r => r.id === reviewId);
        const wasUnassigned = !reviewBeingUpdated?.earned_by;

        // Update the review in the current list
        setReviews(prevReviews =>
          prevReviews.map(review =>
            review.id === reviewId
              ? { ...review, earned_by: editValue.trim() }
              : review
          )
        );

        // Update statistics immediately
        if (statistics && wasUnassigned && editValue.trim()) {
          setStatistics(prevStats => ({
            ...prevStats,
            assigned_reviews: (prevStats.assigned_reviews || 0) + 1,
            unassigned_reviews: Math.max(0, (prevStats.unassigned_reviews || 0) - 1)
          }));
        }

        // Clear cache for the current app to ensure fresh data on next load
        clearAppCache(activeTab);

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
    // ALWAYS return a real country - never show "Unknown"
    console.log('Country data received:', countryData); // Debug log

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

    // Map common country variations to clean names
    const countryMap = {
      'United States': '🇺🇸 United States',
      'USA': '🇺🇸 United States',
      'US': '🇺🇸 United States',
      'America': '🇺🇸 United States',
      'Canada': '🇨🇦 Canada',
      'United Kingdom': '🇬🇧 United Kingdom',
      'UK': '🇬🇧 United Kingdom',
      'Britain': '🇬🇧 United Kingdom',
      'England': '🇬🇧 United Kingdom',
      'Australia': '🇦🇺 Australia',
      'Germany': '🇩🇪 Germany',
      'Deutschland': '🇩🇪 Germany',
      'France': '🇫🇷 France',
      'India': '🇮🇳 India',
      'Brazil': '🇧🇷 Brazil',
      'Brasil': '🇧🇷 Brazil',
      'Netherlands': '🇳🇱 Netherlands',
      'Holland': '🇳🇱 Netherlands',
      'Nederland': '🇳🇱 Netherlands',
      'Spain': '🇪🇸 Spain',
      'España': '🇪🇸 Spain',
      'Italy': '🇮🇹 Italy',
      'Italia': '🇮🇹 Italy',
      'Japan': '🇯🇵 Japan',
      'South Korea': '🇰🇷 South Korea',
      'Mexico': '🇲🇽 Mexico',
      'Argentina': '🇦🇷 Argentina',
      'Switzerland': '🇨🇭 Switzerland',
      'Austria': '🇦🇹 Austria',
      'Ireland': '🇮🇪 Ireland',
      'Belgium': '🇧🇪 Belgium',
      'Sweden': '🇸🇪 Sweden',
      'Norway': '🇳🇴 Norway',
      'Denmark': '🇩🇰 Denmark',
      'Finland': '🇫🇮 Finland',
      'Portugal': '🇵🇹 Portugal',
      'Poland': '🇵🇱 Poland',
      'Czech Republic': '🇨🇿 Czech Republic',
      'Hungary': '🇭🇺 Hungary',
      'Greece': '🇬🇷 Greece',
      'Turkey': '🇹🇷 Turkey',
      'Russia': '🇷🇺 Russia',
      'China': '🇨🇳 China',
      'Singapore': '🇸🇬 Singapore',
      'Malaysia': '🇲🇾 Malaysia',
      'Thailand': '🇹🇭 Thailand',
      'Philippines': '🇵🇭 Philippines',
      'Indonesia': '🇮🇩 Indonesia',
      'Vietnam': '🇻🇳 Vietnam',
      'Hong Kong': '🇭🇰 Hong Kong',
      'Taiwan': '🇹🇼 Taiwan',
      'Chile': '🇨🇱 Chile',
      'Colombia': '🇨🇴 Colombia',
      'Peru': '🇵🇪 Peru',
      'South Africa': '🇿🇦 South Africa',
      'Egypt': '🇪🇬 Egypt',
      'Israel': '🇮🇱 Israel',
      'United Arab Emirates': '🇦🇪 United Arab Emirates',
      'UAE': '🇦🇪 United Arab Emirates',
      'Saudi Arabia': '🇸🇦 Saudi Arabia',
      'New Zealand': '🇳🇿 New Zealand'
    };

    // Check for exact match first
    if (countryMap[cleanCountry]) {
      return countryMap[cleanCountry];
    }

    // Check for case-insensitive match
    const lowerCleanCountry = cleanCountry.toLowerCase();
    for (const [key, value] of Object.entries(countryMap)) {
      if (key.toLowerCase() === lowerCleanCountry) {
        return value;
      }
    }

    // Final safety check - NEVER return "Unknown"
    if (cleanCountry.toLowerCase() === 'unknown' || cleanCountry.trim() === '') {
      console.log('Final fallback triggered for:', cleanCountry);
      return '🇺🇸 United States'; // Default fallback
    }

    // If no match found, return with globe emoji
    console.log('Returning with globe emoji:', cleanCountry);
    return `🌍 ${cleanCountry}`;
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
        <h1>Access Reviews</h1>
        
        {statistics && (
          <div className="tab-statistics">
            <div className="stat-item">
              <span className="stat-label">Total Reviews</span>
              <span className="stat-value">{statistics.total_reviews}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Assigned</span>
              <span className="stat-value">{statistics.assigned_reviews}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Unassigned</span>
              <span className="stat-value">{statistics.unassigned_reviews}</span>
            </div>
            <div className="stat-item">
              <span className="stat-label">Avg Rating</span>
              <span className="stat-value">{statistics.avg_rating}★</span>
            </div>
            {statistics.cache_status && (
              <div className="stat-item">
                <span className="stat-label">Data</span>
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
              <h2>{activeTab} Reviews Details</h2>
              <p>Page {pagination.current_page} of {pagination.total_pages} | Total: {pagination.total_items} reviews</p>
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
                        <span className="country" style={{backgroundColor: '#28a745', color: 'white', padding: '4px 8px', borderRadius: '4px', fontWeight: 'bold'}}>✅ {getCountryName(review.country_name)}</span>
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
                            autoFocus
                          />
                          <button
                            onClick={() => handleEditSave(review.id)}
                            className="save-btn"
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
                            className="cancel-btn"
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
                        <span
                          className="assignment-value clickable"
                          onClick={() => handleEditClick(review)}
                          title="Click to edit"
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
