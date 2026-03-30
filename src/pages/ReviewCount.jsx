import React, { useState, useEffect } from 'react';
import { useCache } from '../context/CacheContext';

const ReviewCount = () => {
  // Use global cache from context
  const { getCachedData, setCachedData } = useCache();

  // Clean up country names from database format
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
      'New Zealand': '🇳🇿 New Zealand',
      'Portugal': '🇵🇹 Portugal',
      'Spain': '🇪🇸 Spain',
      'China': '🇨🇳 China',
      'Malaysia': '🇲🇾 Malaysia',
      'Mexico': '🇲🇽 Mexico',
      'Ukraine': '🇺🇦 Ukraine',
      'Vietnam': '🇻🇳 Vietnam',
      'Poland': '🇵🇱 Poland',
      'Hungary': '🇭🇺 Hungary',
      'Czech Republic': '🇨🇿 Czech Republic',
      'Romania': '🇷🇴 Romania',
      'Greece': '🇬🇷 Greece',
      'Italy': '🇮🇹 Italy',
      'Brazil': '🇧🇷 Brazil',
      'Argentina': '🇦🇷 Argentina',
      'Chile': '🇨🇱 Chile',
      'Colombia': '🇨🇴 Colombia',
      'Thailand': '🇹🇭 Thailand',
      'Indonesia': '🇮🇩 Indonesia',
      'Philippines': '🇵🇭 Philippines',
      'South Korea': '🇰🇷 South Korea',
      'Taiwan': '🇹🇼 Taiwan',
      'Hong Kong': '🇭🇰 Hong Kong',
      'Pakistan': '🇵🇰 Pakistan',
      'Bangladesh': '🇧🇩 Bangladesh',
      'Turkey': '🇹🇷 Turkey',
      'Saudi Arabia': '🇸🇦 Saudi Arabia',
      'United Arab Emirates': '🇦🇪 United Arab Emirates',
      'Israel': '🇮🇱 Israel',
      'Egypt': '🇪🇬 Egypt',
      'Nigeria': '🇳🇬 Nigeria',
      'Kenya': '🇰🇪 Kenya',
      'Peru': '🇵🇪 Peru',
      'Bulgaria': '🇧🇬 Bulgaria'
    };

    return countryMap[cleanCountry] || `🌍 ${cleanCountry}`;
  };
  const [apps, setApps] = useState([]);
  const [selectedApp, setSelectedApp] = useState('');
  const [agentStats, setAgentStats] = useState([]);
  const [countryStats, setCountryStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [countryLoading, setCountryLoading] = useState(false);
  const [error, setError] = useState(null);
  const [countryError, setCountryError] = useState(null);
  const [timeFilter, setTimeFilter] = useState('last_30_days'); // Default to last 30 days
  const [customDateRange, setCustomDateRange] = useState({ start: '', end: '' });
  const [showCustomDatePicker, setShowCustomDatePicker] = useState(false);

  // Fetch available apps on component mount
  useEffect(() => {
    fetchApps();
  }, []);

  // Fetch agent stats and country stats when selected app or filter changes
  useEffect(() => {
    if (selectedApp) {
      fetchAgentStats(selectedApp);
      fetchCountryStats(selectedApp);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedApp, timeFilter, customDateRange.start, customDateRange.end]);

  const fetchApps = async () => {
    try {
      const response = await fetch('/backend/api/apps.php');
      if (!response.ok) throw new Error('Failed to fetch apps');
      const data = await response.json();
      setApps(data);
      // Don't set a default app - let user choose
    } catch (err) {
      setError('Failed to load apps');
      console.error('Error fetching apps:', err);
    }
  };

  const fetchAgentStats = async (appName) => {
    // Check global cache first
    const cacheKey = `agent_stats_${appName}_${timeFilter}_${customDateRange.start}_${customDateRange.end}`;
    const cachedData = getCachedData(appName, null, cacheKey);
    if (cachedData) {
      console.log('✅ Loading agent stats from global cache:', cacheKey);
      setAgentStats(cachedData);
      setLoading(false);
      setError(null);
      return;
    }

    setLoading(true);
    setError(null);
    try {
      // Add cache-busting for real-time updates
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/agent-stats.php?app_name=${encodeURIComponent(appName)}&filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch agent stats');
      const data = await response.json();

      // Handle the case where there are reviews but no assignments
      if (data.message === 'no_assignments') {
        setAgentStats([]);
        setError(`📊 ${data.info} You can assign reviews in the Access Review (Tabs) page.`);
      } else {
        setAgentStats(data);
        // Cache the data globally
        setCachedData(appName, data, null, cacheKey);
      }
    } catch (err) {
      setError('Failed to load agent statistics');
      console.error('Error fetching agent stats:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchCountryStats = async (appName) => {
    // Check global cache first
    const cacheKey = `country_stats_${appName}_${timeFilter}_${customDateRange.start}_${customDateRange.end}`;
    const cachedData = getCachedData(appName, null, cacheKey);
    if (cachedData) {
      console.log('✅ Loading country stats from global cache:', cacheKey);
      setCountryStats(cachedData);
      setCountryLoading(false);
      setCountryError(null);
      return;
    }

    setCountryLoading(true);
    setCountryError(null);
    try {
      // Add cache-busting for real-time updates
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/country-stats.php?app_name=${encodeURIComponent(appName)}&filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch country stats');
      const data = await response.json();
      if (data.success) {
        setCountryStats(data.country_stats || []);
        // Cache the data globally
        setCachedData(appName, data.country_stats || [], null, cacheKey);
      } else {
        throw new Error(data.message || 'Failed to fetch country stats');
      }
    } catch (err) {
      setCountryError('Failed to load country statistics');
      console.error('Error fetching country stats:', err);
      setCountryStats([]);
    } finally {
      setCountryLoading(false);
    }
  };

  // Handle app selection and clear old data
  const handleAppSelect = (app) => {
    setSelectedApp(app);
    // Clear old data immediately when app changes
    setAgentStats([]);
    setCountryStats([]);
    setError(null);
    setCountryError(null);
  };

  // Helper function to format app names consistently
  const formatAppName = (appName) => {
    if (!appName) return '';

    const nameMap = {
      'BetterDocs FAQ': 'BetterDocs FAQ',
      'StoreFAQ': 'StoreFAQ',
      'StoreSEO': 'StoreSEO',
      'EasyFlow': 'EasyFlow',
      'TrustSync': 'TrustSync'
    };

    // Return mapped name or apply consistent formatting
    if (nameMap[appName]) {
      return nameMap[appName];
    }

    // For any other app names, apply consistent formatting
    return appName
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ');
  };

  return (
    <div className="review-count-page" style={{
      // minHeight: '100vh',
      background: 'white',
      maxWidth: '1400px',
      width: '100%',
      margin: '0 auto',
      padding: '20px',
      borderRadius: '16px'
    }}>
      <style>
        {`
          .time-filter-tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            flex-wrap: wrap;
            gap: 4px;
          }

          .time-filter-tab {
            flex: 1;
            min-width: 120px;
            padding: 12px 0;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #666;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
          }

          .time-filter-tab.active {
            background: linear-gradient(135deg, #10B981 0%, #0d9488 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
          }

          .time-filter-tab:hover:not(.active) {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
          }

          @media (max-width: 768px) {
            .time-filter-tabs {
              width: 100%;
              padding: 4px;
            }

            .time-filter-tab {
              min-width: 100px;
              padding: 10px 0;
              font-size: 0.85rem;
            }
          }

          @media (max-width: 480px) {
            .time-filter-tab {
              min-width: 80px;
              padding: 8px 0;
              font-size: 0.75rem;
            }
          }
        `}
      </style>
      <style>
        {`
          @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
          }

          @keyframes fadeInUp {
            from {
              opacity: 0;
              transform: translateY(30px);
            }
            to {
              opacity: 1;
              transform: translateY(0);
            }
          }

          .stats-grid > div {
            animation: fadeInUp 0.6s ease forwards;
          }

          .stats-grid > div:nth-child(1) { animation-delay: 0.1s; }
          .stats-grid > div:nth-child(2) { animation-delay: 0.2s; }
          .stats-grid > div:nth-child(3) { animation-delay: 0.3s; }
          .stats-grid > div:nth-child(4) { animation-delay: 0.4s; }
          .stats-grid > div:nth-child(5) { animation-delay: 0.5s; }
          .stats-grid > div:nth-child(6) { animation-delay: 0.6s; }

          @media (max-width: 768px) {
            .review-count-page {
              padding: 12px !important;
            }

            .agent-stats-section h3 {
              font-size: 1.1rem !important;
            }

            .stats-grid {
              grid-template-columns: repeat(2, 1fr) !important;
              gap: 12px !important;
            }

            .stat-card {
              padding: 12px !important;
            }

            .stat-value {
              font-size: 1.8rem !important;
            }

            .stat-label {
              font-size: 0.85rem !important;
            }

            .country-stats-section {
              padding: 15px !important;
            }

            .country-stats-section h3 {
              font-size: 1.1rem !important;
            }

            .country-list {
              gap: 10px !important;
            }

            .country-item {
              padding: 10px !important;
              font-size: 0.9rem !important;
            }

            .country-name {
              font-size: 0.9rem !important;
            }

            .country-count {
              font-size: 1.2rem !important;
            }
          }

          @media (max-width: 480px) {
            .review-count-page {
              padding: 8px !important;
            }

            .agent-stats-section {
              padding: 12px !important;
            }

            .agent-stats-section h3 {
              font-size: 1rem !important;
            }

            .stats-grid {
              grid-template-columns: 1fr !important;
              gap: 10px !important;
            }

            .stat-card {
              padding: 10px !important;
            }

            .stat-value {
              font-size: 1.5rem !important;
            }

            .stat-label {
              font-size: 0.8rem !important;
            }

            .country-stats-section {
              padding: 12px !important;
            }

            .country-stats-section h3 {
              font-size: 1rem !important;
            }

            .country-item {
              padding: 8px !important;
              font-size: 0.85rem !important;
            }

            .country-name {
              font-size: 0.85rem !important;
            }

            .country-count {
              font-size: 1.1rem !important;
            }
          }
        `}
      </style>
      {/* <div className="page-header" style={{
        marginBottom: '0',
        textAlign: 'center',
        background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
        padding: '30px 20px',
        borderRadius: '0',
        color: 'white',
        position: 'relative',
        overflow: 'hidden'
      }}>
        <div style={{ position: 'relative', zIndex: 1 }}>
          <h1 style={{
            fontSize: '2rem',
            fontWeight: 'bold',
            color: 'white',
            marginBottom: '8px',
            margin: '0'
          }}>
            📊 Appwise Reviews Dashboard
          </h1>
          <p style={{
            fontSize: '0.95rem',
            color: 'rgba(255,255,255,0.9)',
            marginBottom: '0',
            maxWidth: '600px',
            margin: '0 auto'
          }}>
            Track and analyze support agent performance
          </p>
        </div>
      </div> */}

      {/* <div className="container" style={{ padding: '30px 20px', maxWidth: '1400px', margin: '0 auto', background: 'white' }}> */}
      <div className="container">
        <div className="two-section-layout" style={{
          display: 'grid',
          gridTemplateColumns: '300px 1fr',
          gap: '30px',
          // height: 'calc(100vh - 200px)'
        }}>
          <style>
            {`
              @media (max-width: 1024px) {
                .two-section-layout {
                  grid-template-columns: 250px 1fr !important;
                  gap: 20px !important;
                }
              }

              @media (max-width: 768px) {
                .two-section-layout {
                  grid-template-columns: 1fr !important;
                  gap: 20px !important;
                }

                .app-selection-section {
                  order: 2 !important;
                }

                .agent-stats-section {
                  order: 1 !important;
                }

                .app-list {
                  display: grid !important;
                  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)) !important;
                  gap: 10px !important;
                }

                .custom-selection-button {
                  padding: 8px 12px !important;
                  font-size: 0.9rem !important;
                }

                .time-filter-tabs {
                  width: 100% !important;
                }

                .time-filter-tab {
                  min-width: 100px !important;
                  padding: 10px 0 !important;
                  font-size: 0.85rem !important;
                }
              }

              @media (max-width: 480px) {
                .two-section-layout {
                  gap: 15px !important;
                }

                .app-selection-section {
                  padding: 15px !important;
                }

                .agent-stats-section {
                  padding: 15px !important;
                }

                .app-list {
                  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)) !important;
                  gap: 8px !important;
                }

                .custom-selection-button {
                  padding: 6px 10px !important;
                  font-size: 0.85rem !important;
                }

                .time-filter-tab {
                  min-width: 80px !important;
                  padding: 8px 0 !important;
                  font-size: 0.75rem !important;
                }

                .stats-grid {
                  grid-template-columns: 1fr !important;
                  gap: 12px !important;
                }

                .stat-card {
                  padding: 12px !important;
                }

                .stat-value {
                  font-size: 1.8rem !important;
                }

                .stat-label {
                  font-size: 0.85rem !important;
                }
              }
            `}
          </style>
          {/* Left Section - App Selection */}
          <div className="app-selection-section" style={{
            background: 'white',
            borderRadius: '8px',
            padding: '20px',
            boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
            height: 'fit-content',
            border: '1px solid #e5e7eb'
          }}>
            {/* Header */}
            <div style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              gap: '10px'
            }}>
              <div style={{
                fontSize: '2.5rem',
                marginBottom: '10px'
              }}>
                🎯
              </div>
              <div>
              <h3 style={{
                fontSize: '1.5rem',
                fontWeight: 'bold',
                color: '#333',
                marginBottom: '8px'
              }}>
                Appwise Reviews
              </h3>
              <p style={{
                fontSize: '0.9rem',
                color: '#666',
                margin: '0'
              }}>
                Choose an app to view agent statistics
              </p>
              </div>
            </div>

            {/* Currently Selected App Display */}
            {/* {selectedApp && (
              <div style={{
                background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
                padding: '20px',
                borderRadius: '16px',
                marginBottom: '25px',
                color: 'white',
                textAlign: 'center',
                position: 'relative',
                overflow: 'hidden'
              }}> */}
                {/* Background decoration */}
                {/* <div style={{
                  position: 'absolute',
                  top: '-50%',
                  right: '-50%',
                  width: '200%',
                  height: '200%',
                  background: 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
                  pointerEvents: 'none'
                }} />

                <div style={{ position: 'relative', zIndex: 1 }}>
                  <div style={{
                    fontSize: '0.85rem',
                    color: 'rgba(255,255,255,0.8)',
                    marginBottom: '8px',
                    textTransform: 'uppercase',
                    letterSpacing: '1px'
                  }}>
                    Currently Analyzing
                  </div>
                  <div style={{
                    fontSize: '1.3rem',
                    fontWeight: 'bold',
                    color: 'white',
                    textShadow: '0 2px 4px rgba(0,0,0,0.3)'
                  }}>
                    {formatAppName(selectedApp)}
                  </div>
                  <div style={{
                    width: '40px',
                    height: '2px',
                    background: 'rgba(255,255,255,0.5)',
                    margin: '10px auto 0',
                    borderRadius: '1px'
                  }} />
                </div>
              </div>
            )} */}

            {/* Time Filter Options */}
            <div style={{
              marginBottom: '25px',
              marginTop: '25px'
            }}>
              {/* <div style={{
                textAlign: 'center',
                marginBottom: '15px'
              }}>
                <h4 style={{
                  fontSize: '1.1rem',
                  fontWeight: 'bold',
                  color: '#333',
                  marginBottom: '5px'
                }}>
                  📅 Time Period
                </h4>
                <p style={{
                  fontSize: '0.85rem',
                  color: '#666',
                  margin: '0'
                }}>
                  Select data range
                </p>
              </div> */}

              {/* Time Filter Tabs */}
              <div className="time-filter-tabs">
                <button
                  className={`time-filter-tab ${timeFilter === 'last_30_days' ? 'active' : ''}`}
                  onClick={() => {
                    setTimeFilter('last_30_days');
                    setShowCustomDatePicker(false);
                  }}
                >
                  📊 Last 30 Days
                </button>

                <button
                  className={`time-filter-tab ${timeFilter === 'all_time' ? 'active' : ''}`}
                  onClick={() => {
                    setTimeFilter('all_time');
                    setShowCustomDatePicker(false);
                  }}
                >
                  🏆 All Time
                </button>

                <button
                  className={`time-filter-tab ${timeFilter === 'custom' ? 'active' : ''}`}
                  onClick={() => {
                    setShowCustomDatePicker(!showCustomDatePicker);
                    if (!showCustomDatePicker) {
                      setTimeFilter('custom');
                    }
                  }}
                >
                  📅 Custom Range
                </button>
              </div>

              {/* Custom Date Range Picker */}
              {showCustomDatePicker && (
                <div style={{
                  marginTop: '15px',
                  padding: '25px',
                  background: 'white',
                  borderRadius: '12px',
                  boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                  display: 'flex',
                  flexDirection: 'column',
                  gap: '20px',
                  maxWidth: '650px',
                  marginLeft: 'auto',
                  marginRight: 'auto'
                }}>
                  <div style={{
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'flex-start',
                    gap: '20px',
                    flexWrap: 'wrap'
                  }}>
                    <div style={{ flex: '0 1 auto', minWidth: '200px', maxWidth: '250px' }}>
                      <label style={{
                        display: 'block',
                        fontSize: '0.9rem',
                        fontWeight: '600',
                        color: '#333',
                        marginBottom: '8px'
                      }}>
                        Start Date
                      </label>
                      <input
                        type="date"
                        value={customDateRange.start}
                        onChange={(e) => setCustomDateRange({ ...customDateRange, start: e.target.value })}
                        style={{
                          width: '100%',
                          padding: '10px',
                          border: '2px solid #e5e7eb',
                          borderRadius: '8px',
                          fontSize: '0.95rem',
                          outline: 'none',
                          transition: 'border-color 0.3s ease'
                        }}
                        onFocus={(e) => e.target.style.borderColor = '#10B981'}
                        onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
                      />
                    </div>

                    <div style={{ flex: '0 1 auto', minWidth: '200px', maxWidth: '250px' }}>
                      <label style={{
                        display: 'block',
                        fontSize: '0.9rem',
                        fontWeight: '600',
                        color: '#333',
                        marginBottom: '8px'
                      }}>
                        End Date
                      </label>
                      <input
                        type="date"
                        value={customDateRange.end}
                        onChange={(e) => setCustomDateRange({ ...customDateRange, end: e.target.value })}
                        style={{
                          width: '100%',
                          padding: '10px',
                          border: '2px solid #e5e7eb',
                          borderRadius: '8px',
                          fontSize: '0.95rem',
                          outline: 'none',
                          transition: 'border-color 0.3s ease'
                        }}
                        onFocus={(e) => e.target.style.borderColor = '#10B981'}
                        onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
                      />
                    </div>
                  </div>

                  <div style={{
                    display: 'flex',
                    justifyContent: 'center'
                  }}>
                    <button
                      onClick={() => {
                        if (customDateRange.start && customDateRange.end) {
                          setTimeFilter('custom');
                          // Force re-fetch by triggering the useEffect
                          if (selectedApp) {
                            fetchAgentStats(selectedApp);
                            fetchCountryStats(selectedApp);
                          }
                        } else {
                          alert('Please select both start and end dates');
                        }
                      }}
                      disabled={!customDateRange.start || !customDateRange.end}
                      style={{
                        padding: '12px 32px',
                        background: customDateRange.start && customDateRange.end ? '#10B981' : '#d1d5db',
                        color: 'white',
                        border: 'none',
                        borderRadius: '8px',
                        fontSize: '1rem',
                        fontWeight: '600',
                        cursor: customDateRange.start && customDateRange.end ? 'pointer' : 'not-allowed',
                        transition: 'all 0.3s ease',
                        whiteSpace: 'nowrap'
                      }}
                      onMouseEnter={(e) => {
                        if (customDateRange.start && customDateRange.end) {
                          e.target.style.background = '#0d9488';
                        }
                      }}
                      onMouseLeave={(e) => {
                        if (customDateRange.start && customDateRange.end) {
                          e.target.style.background = '#10B981';
                        }
                      }}
                    >
                      Apply Filter
                    </button>
                  </div>

                  {customDateRange.start && customDateRange.end && (
                    <div style={{
                      fontSize: '0.85rem',
                      color: '#666',
                      textAlign: 'center',
                      padding: '8px',
                      background: '#f0fdf4',
                      borderRadius: '6px'
                    }}>
                      📊 Showing data from <strong>{customDateRange.start}</strong> to <strong>{customDateRange.end}</strong>
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* App List - Updated for consistent styling */}
            <div className="app-list" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {apps.map((app, index) => {
                const isSelected = selectedApp === app;
                const gradients = [
                  'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
                  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                  'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                  'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
                ];

                return (
                  <button
                    key={app}
                    className="custom-selection-button"
                    onClick={() => handleAppSelect(app)}
                    style={{
                      width: '100%',
                      padding: '10px 20px',
                      border: 'none',
                      borderRadius: '12px',
                      background: isSelected
                        ? gradients[index % gradients.length]
                        : 'rgba(255,255,255,0.98)',
                      color: isSelected ? 'white' : '#1a202c',
                      cursor: 'pointer',
                      textAlign: 'left',
                      fontSize: '1rem',
                      fontWeight: isSelected ? '600' : '500',
                      transition: 'all 0.3s ease',
                      position: 'relative',
                      overflow: 'hidden',
                      boxShadow: isSelected
                        ? '0 8px 25px rgba(0,0,0,0.15)'
                        : '0 2px 8px rgba(0,0,0,0.08)',
                      transform: isSelected ? 'translateY(-2px)' : 'translateY(0)',
                      outline: 'none !important'
                    }}

                    onFocus={(e) => {
                      e.target.style.outline = 'none';
                    }}
                    onBlur={(e) => {
                      e.target.style.outline = 'none';
                    }}
                  >
                    {/* Background decoration for selected item */}
                    {isSelected && (
                      <div style={{
                        position: 'absolute',
                        top: '-50%',
                        right: '-50%',
                        width: '200%',
                        height: '200%',
                        background: 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
                        pointerEvents: 'none'
                      }} />
                    )}

                    <div style={{
                      position: 'relative',
                      zIndex: 1,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between'
                    }}>
                      <span>{formatAppName(app)}</span>
                      {isSelected && (
                        <span style={{ fontSize: '1.2rem' }}>✓</span>
                      )}
                    </div>
                  </button>
                );
              })}
            </div>

            {/* Footer info */}
            <div style={{
              marginTop: '25px',
              padding: '15px',
              background: 'rgba(102, 126, 234, 0.1)',
              borderRadius: '12px',
              textAlign: 'center'
            }}>
              <div style={{ fontSize: '0.85rem', color: '#666' }}>
                📈 {apps.length} applications available for analysis
              </div>
            </div>
          </div>

          {/* Right Section - Agent Statistics */}
          <div className="agent-stats-section" style={{
            backgroundColor: 'white',
            borderRadius: '8px',
            padding: '20px',
            boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
            border: '1px solid #e5e7eb'
          }}>
            <h3 style={{
              fontSize: '1.3rem',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '20px',
              borderBottom: '2px solid #28a745',
              paddingBottom: '10px'
            }}>
              Support Agent Statistics
              {selectedApp && (
                <span style={{ fontSize: '1rem', fontWeight: 'normal', color: '#666' }}>
                  {' '}for {formatAppName(selectedApp)} ({timeFilter === 'last_30_days' ? 'Last 30 Days' : 'All Time'})
                </span>
              )}
            </h3>

            {!selectedApp && !loading && !error && (
              <div style={{
                textAlign: 'center',
                padding: '60px 40px',
                background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
                borderRadius: '16px',
                color: 'white'
              }}>
                <div style={{ fontSize: '4rem', marginBottom: '20px' }}>🎯</div>
                <div style={{ fontSize: '1.4rem', fontWeight: '600', marginBottom: '10px' }}>
                  Choose an app to analyze
                </div>
                <div style={{
                  fontSize: '1.1rem',
                  color: 'rgba(255,255,255,0.9)',
                  maxWidth: '400px',
                  margin: '0 auto'
                }}>
                  Select an application from the left panel to view support agent statistics and performance metrics.
                </div>
              </div>
            )}

            {loading && (
              <div style={{
                textAlign: 'center',
                padding: '60px 40px',
                background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                borderRadius: '16px',
                color: 'white'
              }}>
                <div style={{
                  fontSize: '4rem',
                  marginBottom: '20px',
                  animation: 'pulse 2s infinite'
                }}>
                  ⏳
                </div>
                <div style={{ fontSize: '1.3rem', fontWeight: '500' }}>
                  Loading agent statistics...
                </div>
                <div style={{
                  fontSize: '1rem',
                  marginTop: '10px',
                  color: 'rgba(255,255,255,0.8)'
                }}>
                  Analyzing performance data for {formatAppName(selectedApp)}
                </div>
              </div>
            )}

            {error && (
              <div style={{
                background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
                color: 'white',
                padding: '24px',
                borderRadius: '16px',
                textAlign: 'center',
                boxShadow: '0 8px 32px rgba(255,107,107,0.3)'
              }}>
                <div style={{ fontSize: '3rem', marginBottom: '15px' }}>⚠️</div>
                <div style={{ fontSize: '1.2rem', fontWeight: '600', marginBottom: '8px' }}>
                  Oops! Something went wrong
                </div>
                <div style={{ fontSize: '1rem', color: 'rgba(255,255,255,0.9)' }}>
                  {error}
                </div>
              </div>
            )}

            {!loading && !error && agentStats.length === 0 && selectedApp && (
              <div style={{
                textAlign: 'center',
                padding: '60px 40px',
                background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                borderRadius: '16px',
                color: 'white'
              }}>
                <div style={{ fontSize: '4rem', marginBottom: '20px' }}>📊</div>
                <div style={{ fontSize: '1.4rem', fontWeight: '600', marginBottom: '10px' }}>
                  No Data Available
                </div>
                <div style={{
                  fontSize: '1.1rem',
                  color: 'rgba(255,255,255,0.9)',
                  maxWidth: '400px',
                  margin: '0 auto'
                }}>
                  No review data found for <strong>{formatAppName(selectedApp)}</strong> in the last 30 days.
                  Try selecting a different app or check back later.
                </div>
              </div>
            )}

            {!loading && !error && agentStats.length > 0 && (
              <>
                {/* Summary Statistics */}
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                  gap: '15px',
                  marginBottom: '30px'
                }}>
                  <div style={{
                    background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
                    padding: '20px',
                    borderRadius: '12px',
                    color: 'white',
                    textAlign: 'center'
                  }}>
                    <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                      {agentStats.length}
                    </div>
                    <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                      {timeFilter === 'all_time' ? 'Total Agents' : 'Active Agents'}
                    </div>
                  </div>

                  <div style={{
                    background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                    padding: '20px',
                    borderRadius: '12px',
                    color: 'white',
                    textAlign: 'center'
                  }}>
                    <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                      {agentStats.reduce((sum, stat) => sum + stat.review_count, 0)}
                    </div>
                    <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                      {timeFilter === 'all_time' ? 'All-Time Reviews' : 'Recent Reviews'}
                    </div>
                  </div>

                  <div style={{
                    background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                    padding: '20px',
                    borderRadius: '12px',
                    color: 'white',
                    textAlign: 'center'
                  }}>
                    <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                      {Math.round(agentStats.reduce((sum, stat) => sum + stat.review_count, 0) / agentStats.length)}
                    </div>
                    <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                      Avg per Agent
                    </div>
                  </div>
                </div>

                <div className="stats-grid" style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                  gap: '12px',
                  marginTop: '20px'
                }}>
                {agentStats
                  .sort((a, b) => b.review_count - a.review_count) // Sort by review count descending
                  .map((stat, index) => {
                    const isTopPerformer = index === 0 && stat.review_count > 0;
                    const isHighPerformer = index < 3 && stat.review_count >= 5;

                    return (
                      <div
                        key={index}
                        style={{
                          background: 'white',
                          borderRadius: '8px',
                          padding: '16px',
                          color: '#333',
                          border: isTopPerformer ? '2px solid #10B981' : '1px solid #e0e0e0',
                          boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
                          transition: 'all 0.2s ease',
                          position: 'relative'
                        }}

                      >
                        {/* Top performer badge */}
                        {isTopPerformer && (
                          <div style={{
                            position: 'absolute',
                            top: '-18px',
                            right: '8px',
                            background: 'white',
                            border: '2px solid #10B981',
                            color: 'white',
                            padding: '2px 6px',
                            borderRadius: '4px',
                            fontSize: '1.2rem',
                            fontWeight: 'bold'
                          }}>
                            👑
                          </div>
                        )}

                        {/* Agent name */}
                        <div style={{
                          fontSize: '1.1rem',
                          fontWeight: '500',
                          margin: '0 0 8px 0',
                          color: '#333',
                          display: 'flex',
                          alignItems: 'center',
                          gap: '8px'
                        }}>
                          <span>{stat.agent_name}</span>
                          {timeFilter === 'all_time' && (
                            <span style={{
                              fontSize: '0.7rem',
                              background: '#10B981',
                              color: 'white',
                              padding: '2px 6px',
                              borderRadius: '10px',
                              fontWeight: 'bold'
                            }}>
                              ALL TIME
                            </span>
                          )}
                        </div>

                        {/* Review count */}
                        <div style={{
                          display: 'flex',
                          alignItems: 'center',
                          gap: '8px',
                          color: '#666'
                        }}>
                          <div style={{
                            fontSize: '1.5rem',
                            fontWeight: 'bold',
                            color: '#333'
                          }}>
                            {stat.review_count}
                          </div>
                          <div style={{
                            fontSize: '0.9rem',
                            color: '#666'
                          }}>
                            reviews
                          </div>
                        </div>

                        {/* Progress bar */}
                        <div style={{
                          marginTop: '12px',
                          height: '3px',
                          background: '#f0f0f0',
                          borderRadius: '2px',
                          overflow: 'hidden'
                        }}>
                          <div style={{
                            height: '100%',
                            background: isTopPerformer ? '#10B981' : '#14b8a6',
                            borderRadius: '2px',
                            width: `${Math.min((stat.review_count / Math.max(...agentStats.map(s => s.review_count))) * 100, 100)}%`,
                            transition: 'width 0.3s ease'
                          }} />
                        </div>
                      </div>
                    );
                  })}
              </div>
              </>
            )}

            {/* Country-wise Review Count Section */}
            {selectedApp && (
              <div style={{
                background: 'rgba(255, 255, 255, 0.95)',
                backdropFilter: 'blur(10px)',
                borderRadius: '20px',
                padding: '30px',
                boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
                border: '1px solid rgba(255,255,255,0.2)',
                marginTop: '30px'
              }}>
                {/* Header */}
                <div style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '10px',
                  marginBottom: '30px',
                  marginTop: '0'
                }}>
                  <div style={{
                    fontSize: '2.5rem',
                    // marginBottom: '10px'
                  }}>
                    🌍
                  </div>
                  <div>
                    <h3 style={{
                      fontSize: '1.8rem',
                      fontWeight: 'bold',
                      color: '#333',
                      marginBottom: '8px',
                      marginTop: '0'
                    }}>
                      Country-wise Review Count
                    </h3>
                    <p style={{
                      fontSize: '1rem',
                      color: '#666',
                      margin: '0'
                    }}>
                      Review distribution by country for {formatAppName(selectedApp)} ({timeFilter === 'last_30_days' ? 'Last 30 Days' : 'All Time'})
                    </p>
                  </div>
                </div>

                {/* Loading State */}
                {countryLoading && (
                  <div style={{
                    textAlign: 'center',
                    padding: '60px 40px',
                    background: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                    borderRadius: '16px',
                    color: 'white'
                  }}>
                    <div style={{
                      fontSize: '4rem',
                      marginBottom: '20px',
                      animation: 'pulse 2s infinite'
                    }}>
                      🌍
                    </div>
                    <div style={{ fontSize: '1.3rem', fontWeight: '500' }}>
                      Loading country statistics...
                    </div>
                    <div style={{
                      fontSize: '1rem',
                      marginTop: '10px',
                      color: 'rgba(255,255,255,0.8)'
                    }}>
                      Analyzing global review distribution
                    </div>
                  </div>
                )}

                {/* Error State */}
                {countryError && (
                  <div style={{
                    background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
                    color: 'white',
                    padding: '24px',
                    borderRadius: '16px',
                    textAlign: 'center',
                    boxShadow: '0 8px 32px rgba(255,107,107,0.3)'
                  }}>
                    <div style={{ fontSize: '3rem', marginBottom: '15px' }}>⚠️</div>
                    <div style={{ fontSize: '1.2rem', fontWeight: '600', marginBottom: '8px' }}>
                      Oops! Something went wrong
                    </div>
                    <div style={{ fontSize: '1rem', color: 'rgba(255,255,255,0.9)' }}>
                      {countryError}
                    </div>
                  </div>
                )}

                {/* Empty State */}
                {!countryLoading && !countryError && countryStats.length === 0 && (
                  <div style={{
                    textAlign: 'center',
                    padding: '60px 40px',
                    background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                    borderRadius: '16px',
                    color: 'white'
                  }}>
                    <div style={{ fontSize: '4rem', marginBottom: '20px' }}>🌐</div>
                    <div style={{ fontSize: '1.4rem', fontWeight: '600', marginBottom: '10px' }}>
                      No Country Data Available
                    </div>
                    <div style={{
                      fontSize: '1.1rem',
                      color: 'rgba(255,255,255,0.9)',
                      maxWidth: '400px',
                      margin: '0 auto'
                    }}>
                      No country-specific review data found for <strong>{formatAppName(selectedApp)}</strong> {timeFilter === 'last_30_days' ? 'in the last 30 days' : 'in all time'}.
                    </div>
                  </div>
                )}

                {/* Country Statistics */}
                {!countryLoading && !countryError && countryStats.length > 0 && (
                  <>
                    {/* Summary Statistics */}
                    <div style={{
                      display: 'grid',
                      gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                      gap: '15px',
                      marginBottom: '30px'
                    }}>
                      <div style={{
                        background: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                        padding: '20px',
                        borderRadius: '12px',
                        color: 'white',
                        textAlign: 'center'
                      }}>
                        <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                          {countryStats.length}
                        </div>
                        <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                          Countries
                        </div>
                      </div>

                      <div style={{
                        background: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        padding: '20px',
                        borderRadius: '12px',
                        color: 'white',
                        textAlign: 'center'
                      }}>
                        <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                          {countryStats.reduce((sum, stat) => sum + stat.review_count, 0)}
                        </div>
                        <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                          Total Reviews
                        </div>
                      </div>

                      <div style={{
                        background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        padding: '20px',
                        borderRadius: '12px',
                        color: 'white',
                        textAlign: 'center'
                      }}>
                        <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                          {countryStats.length > 0 ? Math.round(countryStats.reduce((sum, stat) => sum + stat.review_count, 0) / countryStats.length) : 0}
                        </div>
                        <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                          Avg per Country
                        </div>
                      </div>
                    </div>

                    <div className="country-stats-grid" style={{
                      display: 'grid',
                      gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
                      gap: '20px',
                      marginTop: '20px'
                    }}>
                      {countryStats
                        .sort((a, b) => b.review_count - a.review_count) // Sort by review count descending
                        .map((stat, index) => {
                          const isTopCountry = index === 0 && stat.review_count > 0;
                          const isHighContributor = index < 3 && stat.review_count >= 3;

                          const gradients = [
                            'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                            'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
                          ];

                          return (
                            <div
                              key={stat.country_name}
                              style={{
                                background: isTopCountry
                                  ? 'linear-gradient(135deg, #28a745 0%, #20c997 100%)'
                                  : isHighContributor
                                  ? gradients[index % gradients.length]
                                  : 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                                borderRadius: '16px',
                                padding: '24px',
                                color: 'white',
                                position: 'relative',
                                overflow: 'hidden',
                                cursor: 'pointer',
                                transition: 'all 0.3s ease',
                                boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
                                border: '1px solid rgba(255,255,255,0.2)'
                              }}

                            >
                              {/* Background decoration */}
                              <div style={{
                                position: 'absolute',
                                top: '-50%',
                                right: '-50%',
                                width: '200%',
                                height: '200%',
                                background: 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
                                pointerEvents: 'none'
                              }} />

                              {/* Rank badge for top country */}
                              {isTopCountry && (
                                <div style={{
                                  position: 'absolute',
                                  top: '16px',
                                  right: '16px',
                                  background: 'rgba(255,215,0,0.9)',
                                  color: '#333',
                                  padding: '4px 8px',
                                  borderRadius: '12px',
                                  fontSize: '0.75rem',
                                  fontWeight: 'bold',
                                  display: 'flex',
                                  alignItems: 'center',
                                  gap: '4px'
                                }}>
                                  👑 #1
                                </div>
                              )}

                              {isHighContributor && !isTopCountry && (
                                <div style={{
                                  position: 'absolute',
                                  top: '16px',
                                  right: '16px',
                                  background: 'rgba(255,255,255,0.2)',
                                  color: 'white',
                                  padding: '4px 8px',
                                  borderRadius: '12px',
                                  fontSize: '0.75rem',
                                  fontWeight: 'bold'
                                }}>
                                  ⭐ Top {index + 1}
                                </div>
                              )}

                              {/* Country flag/icon */}
                              {/* <div style={{
                                width: '60px',
                                height: '60px',
                                borderRadius: '50%',
                                background: 'rgba(255,255,255,0.2)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                fontSize: '24px',
                                marginBottom: '16px',
                                border: '2px solid rgba(255,255,255,0.3)'
                              }}>
                                🌍
                              </div> */}

                              {/* Country name */}
                              <h4 style={{
                                fontSize: '1.3rem',
                                fontWeight: '600',
                                margin: '0 0 8px 0',
                                color: 'white',
                                textShadow: '0 2px 4px rgba(0,0,0,0.3)'
                              }}>
                                {getCountryName(stat.country_name)}
                              </h4>

                              {/* Review count and percentage */}
                              <div style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px',
                                marginBottom: '12px'
                              }}>
                                <div style={{
                                  fontSize: '2.5rem',
                                  fontWeight: 'bold',
                                  color: 'white',
                                  textShadow: '0 2px 4px rgba(0,0,0,0.3)'
                                }}>
                                  {stat.review_count}
                                </div>
                                <div style={{
                                  fontSize: '1rem',
                                  color: 'rgba(255,255,255,0.9)',
                                  fontWeight: '500'
                                }}>
                                  reviews<br/>
                                  <span style={{ fontSize: '0.9rem' }}>({stat.percentage}%)</span>
                                </div>
                              </div>

                              {/* Market presence indicator */}
                              <div style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px',
                                fontSize: '0.9rem',
                                color: 'rgba(255,255,255,0.8)'
                              }}>
                                <span>📊</span>
                                <span>
                                  {stat.percentage >= 20 ? 'Major Market' :
                                   stat.percentage >= 10 ? 'Significant Market' :
                                   stat.percentage >= 5 ? 'Growing Market' : 'Emerging Market'}
                                </span>
                              </div>

                              {/* Progress bar */}
                              <div style={{
                                marginTop: '16px',
                                height: '4px',
                                background: 'rgba(255,255,255,0.2)',
                                borderRadius: '2px',
                                overflow: 'hidden'
                              }}>
                                <div style={{
                                  height: '100%',
                                  background: 'rgba(255,255,255,0.8)',
                                  borderRadius: '2px',
                                  width: `${Math.min((stat.review_count / Math.max(...countryStats.map(s => s.review_count))) * 100, 100)}%`,
                                  transition: 'width 0.3s ease'
                                }} />
                              </div>
                            </div>
                          );
                        })}
                    </div>
                  </>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReviewCount;
