import React, { useState, useEffect } from 'react';

const ReviewCount = () => {
  const [apps, setApps] = useState([]);
  const [selectedApp, setSelectedApp] = useState('');
  const [agentStats, setAgentStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch available apps on component mount
  useEffect(() => {
    fetchApps();
  }, []);

  // Fetch agent stats when selected app changes
  useEffect(() => {
    if (selectedApp) {
      fetchAgentStats(selectedApp);
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

  return (
    <div className="review-count-page">
      <div className="container" style={{ padding: '20px', maxWidth: '1400px', margin: '0 auto' }}>
        <div className="page-header" style={{ marginBottom: '30px' }}>
          <h1 style={{
            fontSize: '2.5rem',
            fontWeight: 'bold',
            color: '#333',
            marginBottom: '10px'
          }}>
            Review Count
          </h1>
          <p style={{
            fontSize: '1.1rem',
            color: '#666',
            marginBottom: '0'
          }}>
            Support agent review statistics for the last 30 days
          </p>
        </div>

        <div className="two-section-layout" style={{
          display: 'grid',
          gridTemplateColumns: '300px 1fr',
          gap: '30px',
          height: 'calc(100vh - 200px)'
        }}>
          {/* Left Section - App Selection */}
          <div className="app-selection-section" style={{
            backgroundColor: 'white',
            borderRadius: '8px',
            padding: '20px',
            boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
            height: 'fit-content'
          }}>
            <h3 style={{
              fontSize: '1.3rem',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '20px',
              borderBottom: '2px solid #28a745',
              paddingBottom: '10px'
            }}>
              Select Application
            </h3>

            {selectedApp && (
              <div style={{
                backgroundColor: '#e8f5e8',
                padding: '15px',
                borderRadius: '6px',
                marginBottom: '20px',
                border: '1px solid #28a745'
              }}>
                <div style={{ fontSize: '0.9rem', color: '#666', marginBottom: '5px' }}>
                  Currently Selected:
                </div>
                <div style={{ fontSize: '1.1rem', fontWeight: 'bold', color: '#28a745' }}>
                  {selectedApp}
                </div>
              </div>
            )}

            <div className="app-list">
              {apps.map((app) => (
                <button
                  key={app}
                  onClick={() => setSelectedApp(app)}
                  style={{
                    width: '100%',
                    padding: '12px 16px',
                    marginBottom: '8px',
                    border: selectedApp === app ? '2px solid #28a745' : '1px solid #ddd',
                    borderRadius: '6px',
                    backgroundColor: selectedApp === app ? '#f8fff8' : 'white',
                    color: selectedApp === app ? '#28a745' : '#333',
                    cursor: 'pointer',
                    textAlign: 'left',
                    fontSize: '1rem',
                    fontWeight: selectedApp === app ? 'bold' : 'normal',
                    transition: 'all 0.2s ease'
                  }}
                  onMouseEnter={(e) => {
                    if (selectedApp !== app) {
                      e.target.style.backgroundColor = '#f8f9fa';
                      e.target.style.borderColor = '#28a745';
                    }
                  }}
                  onMouseLeave={(e) => {
                    if (selectedApp !== app) {
                      e.target.style.backgroundColor = 'white';
                      e.target.style.borderColor = '#ddd';
                    }
                  }}
                >
                  {app}
                </button>
              ))}
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
                  {' '}for {selectedApp} (Last 30 Days)
                </span>
              )}
            </h3>

            {loading && (
              <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
                <div style={{ fontSize: '2rem', marginBottom: '10px' }}>⏳</div>
                Loading agent statistics...
              </div>
            )}

            {error && (
              <div style={{
                backgroundColor: '#f8d7da',
                color: '#721c24',
                padding: '15px',
                borderRadius: '6px',
                border: '1px solid #f5c6cb'
              }}>
                {error}
              </div>
            )}

            {!loading && !error && agentStats.length === 0 && selectedApp && (
              <div style={{ textAlign: 'center', padding: '40px', color: '#666' }}>
                <div style={{ fontSize: '2rem', marginBottom: '10px' }}>📊</div>
                No review data found for {selectedApp} in the last 30 days
              </div>
            )}

            {!loading && !error && agentStats.length > 0 && (
              <div className="stats-list">
                {agentStats.map((stat, index) => (
                  <div
                    key={index}
                    style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      padding: '15px',
                      marginBottom: '10px',
                      backgroundColor: '#f8f9fa',
                      borderRadius: '6px',
                      border: '1px solid #e9ecef'
                    }}
                  >
                    <div style={{
                      fontSize: '1.1rem',
                      fontWeight: '500',
                      color: '#333'
                    }}>
                      {stat.agent_name}
                    </div>
                    <div style={{
                      fontSize: '1.2rem',
                      fontWeight: 'bold',
                      color: '#28a745',
                      backgroundColor: 'white',
                      padding: '5px 12px',
                      borderRadius: '20px',
                      border: '1px solid #28a745'
                    }}>
                      {stat.review_count} reviews
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReviewCount;
