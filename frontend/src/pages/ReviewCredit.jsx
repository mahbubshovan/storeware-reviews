import React, { useState, useEffect } from 'react';

const ReviewCredit = () => {
  const [reviewers, setReviewers] = useState([]);
  const [selectedReviewer, setSelectedReviewer] = useState('');
  const [reviewerStats, setReviewerStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch available reviewers on component mount
  useEffect(() => {
    fetchReviewers();
  }, []);

  // Fetch reviewer stats when selected reviewer changes
  useEffect(() => {
    if (selectedReviewer) {
      fetchReviewerStats(selectedReviewer);
    }
  }, [selectedReviewer]);

  const fetchReviewers = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/reviewers.php');
      if (!response.ok) throw new Error('Failed to fetch reviewers');
      const data = await response.json();
      setReviewers(data);
      // Set first reviewer as default selection
      if (data.length > 0) {
        setSelectedReviewer(data[0]);
      }
    } catch (err) {
      setError('Failed to load reviewers');
      console.error('Error fetching reviewers:', err);
    }
  };

  const fetchReviewerStats = async (reviewerName) => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`http://localhost:8000/api/reviewer-stats.php?reviewer_name=${encodeURIComponent(reviewerName)}`);
      if (!response.ok) throw new Error('Failed to fetch reviewer stats');
      const data = await response.json();
      setReviewerStats(data);
    } catch (err) {
      setError('Failed to load reviewer statistics');
      console.error('Error fetching reviewer stats:', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="review-credit-page" style={{
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
              🏆
            </div>
            <h1 style={{
              fontSize: '3rem',
              fontWeight: 'bold',
              color: 'white',
              marginBottom: '15px',
              textShadow: '0 4px 8px rgba(0,0,0,0.3)'
            }}>
              Review Credit Dashboard
            </h1>
            <p style={{
              fontSize: '1.2rem',
              color: 'rgba(255,255,255,0.9)',
              marginBottom: '0',
              maxWidth: '600px',
              margin: '0 auto'
            }}>
              Analyze reviewer performance and track app-specific review contributions across all platforms
            </p>
          </div>
        </div>

        <div className="two-section-layout" style={{
          display: 'grid',
          gridTemplateColumns: '300px 1fr',
          gap: '30px',
          height: 'calc(100vh - 200px)'
        }}>
          {/* Left Section - Reviewer Selection */}
          <div className="reviewer-selection-section" style={{
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
                👤
              </div>
              <h3 style={{
                fontSize: '1.5rem',
                fontWeight: 'bold',
                color: '#333',
                marginBottom: '8px'
              }}>
                Select Reviewer
              </h3>
              <p style={{
                fontSize: '0.9rem',
                color: '#666',
                margin: '0'
              }}>
                Choose a reviewer to view their app statistics
              </p>
            </div>

            {/* Currently Selected Reviewer Display */}
            {selectedReviewer && (
              <div style={{
                background: 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)',
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
                    {selectedReviewer}
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

            {/* Reviewer List */}
            <div className="reviewer-list" style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {reviewers.map((reviewer, index) => {
                const isSelected = selectedReviewer === reviewer;
                const gradients = [
                  'linear-gradient(135deg, #17a2b8 0%, #138496 100%)',
                  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                  'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                  'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
                ];

                return (
                  <button
                    key={reviewer}
                    onClick={() => setSelectedReviewer(reviewer)}
                    style={{
                      width: '100%',
                      padding: '16px 20px',
                      border: 'none',
                      borderRadius: '12px',
                      background: isSelected
                        ? gradients[index % gradients.length]
                        : 'rgba(255,255,255,0.98)',
                      color: isSelected ? 'white' : '#1a202c',
                      border: isSelected ? 'none' : '1px solid rgba(0,0,0,0.08)',
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
                      transform: isSelected ? 'translateY(-2px)' : 'translateY(0)'
                    }}
                    onMouseEnter={(e) => {
                      if (!isSelected) {
                        e.target.style.background = 'rgba(255,255,255,1)';
                        e.target.style.color = '#1a202c';
                        e.target.style.border = '1px solid rgba(0,0,0,0.15)';
                        e.target.style.transform = 'translateY(-2px)';
                        e.target.style.boxShadow = '0 6px 20px rgba(0,0,0,0.12)';
                      }
                    }}
                    onMouseLeave={(e) => {
                      if (!isSelected) {
                        e.target.style.background = 'rgba(255,255,255,0.98)';
                        e.target.style.color = '#1a202c';
                        e.target.style.border = '1px solid rgba(0,0,0,0.08)';
                        e.target.style.transform = 'translateY(0)';
                        e.target.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
                      }
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
                      <span>{reviewer}</span>
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
              background: 'rgba(23, 162, 184, 0.1)',
              borderRadius: '12px',
              textAlign: 'center'
            }}>
              <div style={{ fontSize: '0.85rem', color: '#666' }}>
                👥 {reviewers.length} reviewers available for analysis
              </div>
            </div>
          </div>

          {/* Right Section - Reviewer's App Statistics */}
          <div className="reviewer-stats-section" style={{
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
              borderBottom: '2px solid #17a2b8',
              paddingBottom: '10px'
            }}>
              App Statistics
              {selectedReviewer && (
                <span style={{ fontSize: '1rem', fontWeight: 'normal', color: '#666' }}>
                  {' '}for {selectedReviewer} (All Time)
                </span>
              )}
            </h3>

            {loading && (
              <div style={{
                textAlign: 'center',
                padding: '60px 40px',
                background: 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)',
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
                  Loading reviewer statistics...
                </div>
                <div style={{
                  fontSize: '1rem',
                  marginTop: '10px',
                  color: 'rgba(255,255,255,0.8)'
                }}>
                  Analyzing app contributions for {selectedReviewer}
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

            {!loading && !error && reviewerStats.length === 0 && selectedReviewer && (
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
                  No review data found for <strong>{selectedReviewer}</strong>.
                  Try selecting a different reviewer or check back later.
                </div>
              </div>
            )}

            {!loading && !error && reviewerStats.length > 0 && (
              <>
                {/* Summary Statistics */}
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                  gap: '15px',
                  marginBottom: '30px'
                }}>
                  <div style={{
                    background: 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)',
                    padding: '20px',
                    borderRadius: '12px',
                    color: 'white',
                    textAlign: 'center'
                  }}>
                    <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
                      {reviewerStats.length}
                    </div>
                    <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                      Apps Reviewed
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
                      {reviewerStats.reduce((sum, stat) => sum + stat.review_count, 0)}
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
                      {Math.round(reviewerStats.reduce((sum, stat) => sum + stat.review_count, 0) / reviewerStats.length)}
                    </div>
                    <div style={{ fontSize: '0.9rem', opacity: 0.9 }}>
                      Avg per App
                    </div>
                  </div>
                </div>

                <div className="stats-grid" style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))',
                  gap: '20px',
                  marginTop: '20px'
                }}>
                  {reviewerStats
                    .sort((a, b) => b.review_count - a.review_count) // Sort by review count descending
                    .map((stat, index) => {
                      const isTopApp = index === 0 && stat.review_count > 0;
                      const isHighContributor = index < 3 && stat.review_count >= 3;

                      return (
                        <div
                          key={index}
                          style={{
                            background: isTopApp
                              ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                              : isHighContributor
                              ? 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)'
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
                          onMouseEnter={(e) => {
                            e.currentTarget.style.transform = 'translateY(-5px)';
                            e.currentTarget.style.boxShadow = '0 12px 40px rgba(0,0,0,0.2)';
                          }}
                          onMouseLeave={(e) => {
                            e.currentTarget.style.transform = 'translateY(0)';
                            e.currentTarget.style.boxShadow = '0 8px 32px rgba(0,0,0,0.1)';
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

                          {/* Rank badge for top app */}
                          {isTopApp && (
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

                          {isHighContributor && !isTopApp && (
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

                          {/* App icon */}
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
                            📱
                          </div>

                          {/* App name */}
                          <h4 style={{
                            fontSize: '1.3rem',
                            fontWeight: '600',
                            margin: '0 0 8px 0',
                            color: 'white',
                            textShadow: '0 2px 4px rgba(0,0,0,0.3)'
                          }}>
                            {stat.app_name}
                          </h4>

                          {/* Review count */}
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
                              {stat.review_count === 1 ? 'review' : 'reviews'}
                            </div>
                          </div>

                          {/* Contribution level */}
                          <div style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            fontSize: '0.9rem',
                            color: 'rgba(255,255,255,0.8)'
                          }}>
                            <span>🏆</span>
                            <span>
                              {stat.review_count >= 10 ? 'Major Contributor' :
                               stat.review_count >= 5 ? 'Active Contributor' :
                               stat.review_count >= 2 ? 'Regular Contributor' : 'New Contributor'}
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
                              width: `${Math.min((stat.review_count / Math.max(...reviewerStats.map(s => s.review_count))) * 100, 100)}%`,
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
        </div>
      </div>
    </div>
  );
};

export default ReviewCredit;
