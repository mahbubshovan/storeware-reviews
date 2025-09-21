import { useState, useEffect } from 'react';
import './Analytics.css';

const Analytics = () => {
  const [selectedApp, setSelectedApp] = useState('StoreSEO'); // Default to StoreSEO
  const [analyticsData, setAnalyticsData] = useState(null);
  const [latestReviews, setLatestReviews] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [loadingStep, setLoadingStep] = useState(0);
  const [reviewsFilter, setReviewsFilter] = useState('this_month');
  const [customDateRange, setCustomDateRange] = useState({ start: '', end: '' });
  const [showCustomDate, setShowCustomDate] = useState(false);


  const handleAppChange = (app) => {
    setSelectedApp(app);
  };

  const fetchAnalyticsData = async (appName) => {
    if (!appName) return;

    setLoading(true);
    setError(null);
    setLoadingStep(0);

    try {
      // Step 1: Connecting to Shopify
      setLoadingStep(1);
      await new Promise(resolve => setTimeout(resolve, 800)); // Simulate connection time

      // Step 2: Fetching review data
      setLoadingStep(2);
      const response = await fetch(`/backend/api/enhanced-analytics.php?app=${encodeURIComponent(appName)}`);

      // Step 3: Processing analytics
      setLoadingStep(3);
      await new Promise(resolve => setTimeout(resolve, 500)); // Simulate processing time

      const data = await response.json();

      if (data.success) {
        setAnalyticsData(data.data);
        // Fetch filtered reviews separately
        await fetchFilteredReviews(appName, reviewsFilter);
      } else {
        setError(data.error || 'Failed to fetch analytics data');
      }
    } catch (err) {
      setError('Network error: ' + err.message);
    } finally {
      setLoading(false);
      setLoadingStep(0);
    }
  };

  const fetchFilteredReviews = async (appName, filter) => {
    if (!appName) return;

    try {
      // Dynamic limit based on filter to show better representation
      const limit = filter === 'last_90_days' ? 30 :
                   filter === 'all' ? 50 :
                   filter === 'custom' ? 25 : 15;

      let url = `/backend/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=1&limit=${limit}&_t=${Date.now()}&_cache_bust=${Math.random()}`;

      if (filter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      } else if (filter !== 'all') {
        url += `&filter=${filter}`;
      }

      console.log('Fetching reviews with filter:', filter, 'URL:', url);
      const response = await fetch(url);
      const data = await response.json();

      console.log('Filter response:', filter, 'Reviews count:', data.data?.reviews?.length);
      if (data.success && data.data && data.data.reviews) {
        setLatestReviews(data.data.reviews);
      }
    } catch (err) {
      console.error('Error fetching filtered reviews:', err);
    }
  };

  const fetchLatestReviews = async (appName) => {
    if (!appName) return;

    try {
      // Fetch latest 5 reviews without any filters
      const url = `/backend/api/access-reviews-cached.php?app=${encodeURIComponent(appName)}&page=1&limit=5`;

      const response = await fetch(url);
      const data = await response.json();

      if (data.success && data.data && data.data.reviews) {
        setLatestReviews(data.data.reviews);
      }
    } catch (err) {
      console.error('Error fetching latest reviews:', err);
    }
  };

  useEffect(() => {
    if (selectedApp) {
      fetchAnalyticsData(selectedApp);
      // Also fetch reviews with the current filter on app change
      if (reviewsFilter !== 'custom') {
        fetchFilteredReviews(selectedApp, reviewsFilter);
      } else if (customDateRange.start && customDateRange.end) {
        fetchFilteredReviews(selectedApp, reviewsFilter);
      }
    }
  }, [selectedApp]);

  useEffect(() => {
    console.log('Filter changed to:', reviewsFilter, 'for app:', selectedApp);
    if (selectedApp && reviewsFilter !== 'custom') {
      fetchFilteredReviews(selectedApp, reviewsFilter);
    }
  }, [reviewsFilter]);

  useEffect(() => {
    if (selectedApp && reviewsFilter === 'custom' && customDateRange.start && customDateRange.end) {
      fetchFilteredReviews(selectedApp, reviewsFilter);
    }
  }, [customDateRange]);

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
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
  };

  return (
    <div className="analytics-dashboard">
      {/* Header with App Selector */}
      <div className="analytics-header">
        <div className="analytics-title">
          <h1>📊 Analytics Dashboard</h1>
          <p>Real-time insights from Shopify app reviews</p>
        </div>
        <div className="app-selector-container">
          <select
            value={selectedApp}
            onChange={(e) => handleAppChange(e.target.value)}
            className="app-selector-dropdown"
          >
            <option value="">Select an app...</option>
            <option value="StoreSEO">StoreSEO</option>
            <option value="StoreFAQ">StoreFAQ</option>
            <option value="EasyFlow">EasyFlow</option>
            <option value="BetterDocs FAQ Knowledge Base">BetterDocs FAQ Knowledge Base</option>
            <option value="Vidify">Vidify</option>
            <option value="TrustSync">TrustSync</option>
          </select>
        </div>
      </div>

      {/* Main Content */}
      {selectedApp ? (
        <div className="analytics-main">
          {loading ? (
            <div className="smart-loading-container">
              <div className="loading-animation">
                {/* Animated Shopify-style loader */}
                <div className="shopify-loader">
                  <div className="loader-circle"></div>
                  <div className="loader-circle"></div>
                  <div className="loader-circle"></div>
                </div>

                {/* App icon and name */}
                <div className="loading-app-info">
                  <div className="app-icon">📱</div>
                  <h3>Analyzing {selectedApp}</h3>
                </div>

                {/* Loading steps */}
                <div className="loading-steps">
                  <div className={`loading-step ${loadingStep >= 1 ? 'active' : ''} ${loadingStep > 1 ? 'completed' : ''}`}>
                    <div className="step-icon">{loadingStep > 1 ? '✅' : '🔍'}</div>
                    <span>Connecting to Shopify...</span>
                  </div>
                  <div className={`loading-step ${loadingStep >= 2 ? 'active' : ''} ${loadingStep > 2 ? 'completed' : ''}`}>
                    <div className="step-icon">{loadingStep > 2 ? '✅' : '📊'}</div>
                    <span>Fetching review data...</span>
                  </div>
                  <div className={`loading-step ${loadingStep >= 3 ? 'active' : ''} ${loadingStep > 3 ? 'completed' : ''}`}>
                    <div className="step-icon">{loadingStep > 3 ? '✅' : '⚡'}</div>
                    <span>Processing analytics...</span>
                  </div>
                </div>

                {/* Progress bar */}
                <div className="progress-container">
                  <div className="progress-bar">
                    <div className="progress-fill"></div>
                  </div>
                  <div className="progress-text">Loading real-time data...</div>
                </div>
              </div>
            </div>
          ) : error ? (
            <div className="error-state">
              <div className="error-icon">⚠️</div>
              <h3>Error Loading Data</h3>
              <p>{error}</p>
              <button onClick={() => fetchAnalyticsData(selectedApp)} className="retry-btn">
                🔄 Retry
              </button>
            </div>
          ) : analyticsData ? (
            <>
              {/* Statistics Cards */}
              <div className="stats-grid">
                <div className="stat-card this-month">
                  <div className="stat-icon">📈</div>
                  <div className="stat-content">
                    <h3>This Month</h3>
                    <div className="stat-value">{analyticsData.this_month_count}</div>
                    <div className="stat-label">Reviews</div>
                  </div>
                </div>

                <div className="stat-card last-30-days">
                  <div className="stat-icon">📅</div>
                  <div className="stat-content">
                    <h3>Last 30 Days</h3>
                    <div className="stat-value">{analyticsData.last_30_days_count}</div>
                    <div className="stat-label">Reviews</div>
                  </div>
                </div>

                <div className="stat-card total-reviews">
                  <div className="stat-icon">📊</div>
                  <div className="stat-content">
                    <h3>Total Reviews</h3>
                    <div className="stat-value">{analyticsData.rating_distribution_total || analyticsData.total_reviews || 0}</div>
                    <div className="stat-label">All Time</div>
                  </div>
                </div>

                <div className="stat-card average-rating">
                  <div className="stat-icon">⭐</div>
                  <div className="stat-content">
                    <h3>Average Rating</h3>
                    <div className="stat-value">{analyticsData.shopify_display_rating || analyticsData.average_rating}</div>
                    <div className="stat-label">Stars</div>
                  </div>
                </div>
              </div>

              {/* Rating Distribution */}
              <div className="rating-distribution-section">
                <div className="section-header">
                  <h2>📊 Rating Distribution</h2>
                  <div className="data-source-info">
                    <span className="data-badge live-scraping">
                      🔴 Live from Shopify
                    </span>
                    {analyticsData.rating_distribution_total && (
                      <span className="total-analyzed">
                        {analyticsData.rating_distribution_total} reviews analyzed
                      </span>
                    )}
                  </div>
                </div>
                <div className="rating-bars">
                  {[5, 4, 3, 2, 1].map(rating => {
                    const count = analyticsData.rating_distribution[rating] || 0;
                    // Use rating_distribution_total for accurate percentages (live Shopify data)
                    const total = analyticsData.rating_distribution_total || analyticsData.total_reviews || 0;
                    const percentage = total > 0
                      ? (count / total * 100).toFixed(1)
                      : 0;

                    return (
                      <div key={rating} className="rating-bar">
                        <div className="rating-label">
                          <span className="stars">{renderStars(rating)}</span>
                          <span className="rating-number">{rating}</span>
                        </div>
                        <div className="bar-container">
                          <div
                            className="bar-fill"
                            style={{ width: `${percentage}%` }}
                          ></div>
                        </div>
                        <div className="rating-stats">
                          <span className="count">{count}</span>
                          <span className="percentage">({percentage}%)</span>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Reviews Details */}
              <div className="latest-reviews-section">
                <div className="section-header">
                  <h2>📝 Reviews Details</h2>
                  <div className="reviews-filter-container">
                    <select
                      value={reviewsFilter}
                      onChange={(e) => {
                        const value = e.target.value;
                        setReviewsFilter(value);
                        setShowCustomDate(value === 'custom');
                      }}
                      className="reviews-filter-select"
                    >
                      <option value="all">All Reviews</option>
                      <option value="last_30_days">Last 30 Days</option>
                      <option value="this_month">This Month</option>
                      <option value="last_month">Last Month</option>
                      <option value="last_90_days">Last 90 Days</option>
                      <option value="custom">Custom Date Range</option>
                    </select>
                  </div>
                </div>

                {showCustomDate && (
                  <div className="custom-date-container">
                    <div className="custom-date-header">
                      <span className="date-icon">📅</span>
                      <h4>Select Date Range</h4>
                    </div>
                    <div className="custom-date-inputs">
                      <div className="date-input-group">
                        <label htmlFor="start-date">From</label>
                        <input
                          id="start-date"
                          type="date"
                          value={customDateRange.start}
                          onChange={(e) => setCustomDateRange(prev => ({ ...prev, start: e.target.value }))}
                          className="date-input"
                          max={customDateRange.end || new Date().toISOString().split('T')[0]}
                        />
                      </div>
                      <div className="date-separator">
                        <span>→</span>
                      </div>
                      <div className="date-input-group">
                        <label htmlFor="end-date">To</label>
                        <input
                          id="end-date"
                          type="date"
                          value={customDateRange.end}
                          onChange={(e) => setCustomDateRange(prev => ({ ...prev, end: e.target.value }))}
                          className="date-input"
                          min={customDateRange.start}
                          max={new Date().toISOString().split('T')[0]}
                        />
                      </div>
                    </div>
                    <div className="date-quick-actions">
                      <button
                        onClick={() => {
                          const today = new Date();
                          const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                          setCustomDateRange({
                            start: lastWeek.toISOString().split('T')[0],
                            end: today.toISOString().split('T')[0]
                          });
                        }}
                        className="quick-date-btn"
                      >
                        Last 7 Days
                      </button>
                      <button
                        onClick={() => {
                          const today = new Date();
                          const lastMonth = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                          setCustomDateRange({
                            start: lastMonth.toISOString().split('T')[0],
                            end: today.toISOString().split('T')[0]
                          });
                        }}
                        className="quick-date-btn"
                      >
                        Last 30 Days
                      </button>
                      <button
                        onClick={() => {
                          setCustomDateRange({ start: '', end: '' });
                        }}
                        className="quick-date-btn clear-btn"
                      >
                        Clear
                      </button>
                    </div>
                  </div>
                )}

                {/* Date Range Indicator */}
                {latestReviews.length > 0 && (
                  <div className="date-range-indicator">
                    <span className="range-label">Showing reviews from:</span>
                    <span className="date-range">
                      {latestReviews[latestReviews.length - 1]?.review_date} to {latestReviews[0]?.review_date}
                    </span>
                    <span className="review-count">({latestReviews.length} reviews displayed)</span>
                  </div>
                )}

                <div className="reviews-list">
                  {latestReviews.length > 0 ? (
                    latestReviews.map((review, index) => (
                      <div key={index} className="review-item">
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
                      </div>
                    ))
                  ) : (
                    <div className="no-reviews">
                      <p>No recent reviews found</p>
                    </div>
                  )}
                </div>
              </div>
            </>
          ) : null}
        </div>
      ) : (
        <div className="no-app-selected">
          <div className="no-app-message">
            <div className="no-app-icon">📊</div>
            <h2>Choose an app to analyze</h2>
            <p>Select an app from the dropdown above to view comprehensive analytics</p>
          </div>
        </div>
      )}
    </div>
  );
};

export default Analytics;
