import React, { useState, useEffect } from 'react';

const ReviewCount = () => {
  // Clean up country names from messy database format
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
      'Ireland': '🇮🇪 Ireland'
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

  // Fetch available apps on component mount
  useEffect(() => {
    fetchApps();
  }, []);

  // Fetch agent stats and country stats when selected app changes
  useEffect(() => {
    if (selectedApp) {
      fetchAgentStats(selectedApp);
      fetchCountryStats(selectedApp);
    }
  }, [selectedApp]);

  const fetchApps = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/apps.php');
      if (!response.ok) throw new Error('Failed to fetch apps');
      const data = await response.json();
      setApps(data);
      // Set first app as default selection
      if (data.length > 0) {
        setSelectedApp(data[0]);
      }
    } catch (err) {
      setError('Failed to load apps');
      console.error('Error fetching apps:', err);
    }
  };

  const fetchAgentStats = async (appName) => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`http://localhost:8000/api/agent-stats.php?app_name=${encodeURIComponent(appName)}`);
      if (!response.ok) throw new Error('Failed to fetch agent stats');
      const data = await response.json();
      setAgentStats(data);
    } catch (err) {
      setError('Failed to load agent statistics');
      console.error('Error fetching agent stats:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchCountryStats = async (appName) => {
    setCountryLoading(true);
    setCountryError(null);
    try {
      const response = await fetch(`http://localhost:8000/api/country-stats.php?app_name=${encodeURIComponent(appName)}`);
      if (!response.ok) throw new Error('Failed to fetch country stats');
      const data = await response.json();
      if (data.success) {
        setCountryStats(data.country_stats || []);
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
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      padding: '20px 0'
    }}>
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
        `}
      </style>
      <div className="container" style={{ padding: '20px', maxWidth: '1400px', margin: '0 auto' }}>
        <div className="page-header" style={{
          marginBottom: '40px',
          textAlign: 'center',
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          padding: '40px 20px',
          borderRadius: '20px',
          color: 'white',
          position: 'relative',
          overflow: 'hidden'
        }}>
          {/* Background decoration */}
          <div style={{
            position: 'absolute',
            top: '-50%',
            left: '-50%',
            width: '200%',
            height: '200%',
            background: 'radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)',
            pointerEvents: 'none'
          }} />

          <div style={{ position: 'relative', zIndex: 1 }}>
            <div style={{
              fontSize: '3rem',
              marginBottom: '10px'
            }}>
              📊
            </div>
            <h1 style={{
              fontSize: '3rem',
              fontWeight: 'bold',
              color: 'white',
              marginBottom: '15px',
              textShadow: '0 4px 8px rgba(0,0,0,0.3)'
            }}>
              Review Count Dashboard
            </h1>
            <p style={{
              fontSize: '1.2rem',
              color: 'rgba(255,255,255,0.9)',
              marginBottom: '0',
              maxWidth: '600px',
              margin: '0 auto'
            }}>
              Track and analyze support agent performance with comprehensive review statistics for the last 30 days
            </p>
          </div>
        </div>

        <div className="two-section-layout" style={{
          display: 'grid',
          gridTemplateColumns: '300px 1fr',
          gap: '30px',
          height: 'calc(100vh - 200px)'
        }}>
          {/* Left Section - App Selection */}
          <div className="app-selection-section" style={{
            background: 'rgba(255, 255, 255, 0.95)',
            backdropFilter: 'blur(10px)',
            borderRadius: '20px',
            padding: '30px',
            boxShadow: '0 8px 32px rgba(0,0,0,0.1)',
            height: 'fit-content',
            border: '1px solid rgba(255,255,255,0.2)'
          }}>
            {/* Header */}
            <div style={{
              textAlign: 'center',
              marginBottom: '30px'
            }}>
              <div style={{
                fontSize: '2.5rem',
                marginBottom: '10px'
              }}>
                🎯
              </div>
              <h3 style={{
                fontSize: '1.5rem',
                fontWeight: 'bold',
                color: '#333',
                marginBottom: '8px'
              }}>
                Select Application
              </h3>
              <p style={{
                fontSize: '0.9rem',
                color: '#666',
                margin: '0'
              }}>
                Choose an app to view agent statistics
              </p>
            </div>

            {/* Currently Selected App Display */}
            {selectedApp && (
              <div style={{
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                padding: '20px',
                borderRadius: '16px',
                marginBottom: '25px',
                color: 'white',
                textAlign: 'center',
                position: 'relative',
                overflow: 'hidden'
              }}>
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
            )}

            {/* App List - Updated for consistent styling */}
            <div className="app-list" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {apps.map((app, index) => {
                const isSelected = selectedApp === app;
                const gradients = [
                  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
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
                    onClick={() => setSelectedApp(app)}
                    style={{
                      width: '100%',
                      padding: '16px 20px',
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
            boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
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
                  {' '}for {formatAppName(selectedApp)} (Last 30 Days)
                </span>
              )}
            </h3>

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
                    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    padding: '20px',
                    borderRadius: '12px',
                    color: 'white',
                    textAlign: 'center'
                  }}>
                    <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                      {agentStats.length}
                    </div>
                    <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                      Active Agents
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
                          border: isTopPerformer ? '2px solid #667eea' : '1px solid #e0e0e0',
                          boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
                          transition: 'all 0.2s ease',
                          position: 'relative'
                        }}

                      >
                        {/* Top performer badge */}
                        {isTopPerformer && (
                          <div style={{
                            position: 'absolute',
                            top: '8px',
                            right: '8px',
                            background: '#667eea',
                            color: 'white',
                            padding: '2px 6px',
                            borderRadius: '4px',
                            fontSize: '0.7rem',
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
                          color: '#333'
                        }}>
                          {stat.agent_name}
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
                            background: isTopPerformer ? '#667eea' : '#4facfe',
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
                  textAlign: 'center',
                  marginBottom: '30px'
                }}>
                  <div style={{
                    fontSize: '2.5rem',
                    marginBottom: '10px'
                  }}>
                    🌍
                  </div>
                  <h3 style={{
                    fontSize: '1.8rem',
                    fontWeight: 'bold',
                    color: '#333',
                    marginBottom: '8px'
                  }}>
                    Country-wise Review Count
                  </h3>
                  <p style={{
                    fontSize: '1rem',
                    color: '#666',
                    margin: '0'
                  }}>
                    Review distribution by country for {formatAppName(selectedApp)} (Last 30 Days)
                  </p>
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
                      No country-specific review data found for <strong>{formatAppName(selectedApp)}</strong> in the last 30 days.
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
                              <div style={{
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
                              </div>

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
