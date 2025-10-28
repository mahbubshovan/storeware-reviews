import { useState, useEffect } from 'react';

const ReviewCredit = () => {
  const [timeFilter, setTimeFilter] = useState('last_30_days');
  const [agentStats, setAgentStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [apps, setApps] = useState([]);
  const [selectedApp, setSelectedApp] = useState('all');

  // Fetch available apps
  const fetchApps = async () => {
    try {
      const response = await fetch('/backend/api/apps.php');
      if (!response.ok) throw new Error('Failed to fetch apps');
      const data = await response.json();
      setApps(data);
    } catch (err) {
      console.error('Error fetching apps:', err);
      setError('Failed to load apps');
    }
  };

  // Fetch agent statistics
  const fetchAgentStats = async () => {
    if (selectedApp === 'all') {
      setAgentStats([]);
      return;
    }

    setLoading(true);
    setError(null);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      const response = await fetch(`/backend/api/agent-stats.php?app_name=${encodeURIComponent(selectedApp)}&filter=${timeFilter}&${cacheBust}`);
      if (!response.ok) throw new Error('Failed to fetch agent stats');
      const data = await response.json();

      if (data.message === 'no_assignments') {
        setAgentStats([]);
        setError(`No agent assignments found for ${selectedApp}. You can assign reviews in the Access Review page.`);
      } else {
        setAgentStats(data);
      }
    } catch (err) {
      console.error('Error fetching agent stats:', err);
      setError('Failed to load agent statistics');
      setAgentStats([]);
    } finally {
      setLoading(false);
    }
  };

  // Load apps on component mount
  useEffect(() => {
    fetchApps();
  }, []);

  // Fetch agent stats when app or time filter changes
  useEffect(() => {
    if (selectedApp !== 'all') {
      fetchAgentStats();
    } else {
      setAgentStats([]);
      setError(null);
    }
  }, [selectedApp, timeFilter]);

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
      padding: '20px'
    }}>
      <div style={{ textAlign: 'center', color: 'white', padding: '20px' }}>
        <h1>🎯 Agent Reviews Dashboard</h1>
        <p>Track agent performance with Last 30 Days and All Time views</p>

        {/* Time Filter Tabs */}
        {/* App Selector */}
        {/* <div style={{ margin: '20px 0' }}> */}
        <div style={{  }}>
          <div style={{
            background: 'rgba(255,255,255,0.1)',
            borderRadius: '10px',
            padding: '20px',
            marginBottom: '20px'
          }}>
            <label style={{
              display: 'block',
              marginBottom: '10px',
              color: 'white',
              fontWeight: 'bold',
              fontSize: '1.1rem'
            }}>
              📱 Select App:
            </label>
            <select
              value={selectedApp}
              onChange={(e) => setSelectedApp(e.target.value)}
              style={{
                padding: '12px 16px',
                borderRadius: '8px',
                border: 'none',
                fontSize: '1rem',
                minWidth: '200px',
                background: 'white',
                color: '#333'
              }}
            >
              <option value="all">Choose an app to analyze</option>
              {apps.map(app => (
                <option key={app} value={app}>
                  {app}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Time Filter Tabs */}
        {selectedApp !== 'all' && (
          <div style={{ margin: '30px 0' }}>
            <div style={{ display: 'inline-flex', background: 'rgba(255,255,255,0.2)', borderRadius: '10px', padding: '5px' }}>
              <button
                onClick={() => setTimeFilter('last_30_days')}
                style={{
                  padding: '12px 24px',
                  background: timeFilter === 'last_30_days' ? 'white' : 'transparent',
                  color: timeFilter === 'last_30_days' ? '#10B981' : 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontWeight: 'bold'
                }}
              >
                📊 Last 30 Days
              </button>
              <button
                onClick={() => setTimeFilter('all_time')}
                style={{
                  padding: '12px 24px',
                  background: timeFilter === 'all_time' ? 'white' : 'transparent',
                  color: timeFilter === 'all_time' ? '#10B981' : 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontWeight: 'bold'
                }}
              >
                🏆 All Time
              </button>
            </div>
          </div>
        )}

        {/* Content based on selected app and filter */}
        <div style={{
          background: 'rgba(255,255,255,0.1)',
          borderRadius: '15px',
          padding: '30px',
          margin: '20px auto',
          maxWidth: '1000px',
          minHeight: '400px'
        }}>
          {selectedApp === 'all' ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '4rem', marginBottom: '20px' }}>📱</div>
              <h2 style={{ marginBottom: '15px', color: 'white' }}>Choose an App to Analyze</h2>
              <p style={{ fontSize: '18px', opacity: 0.8, color: 'white' }}>
                Select an app from the dropdown above to view agent review statistics
              </p>
            </div>
          ) : loading ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>⏳</div>
              <h2 style={{ color: 'white' }}>Loading Agent Statistics...</h2>
            </div>
          ) : error ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>⚠️</div>
              <h2 style={{ color: 'white', marginBottom: '15px' }}>No Data Available</h2>
              <p style={{ fontSize: '16px', opacity: 0.9, color: 'white' }}>{error}</p>
            </div>
          ) : (
            <>
              <h2 style={{ marginBottom: '30px', color: 'white', textAlign: 'center' }}>
                {timeFilter === 'last_30_days' ? '📊 Last 30 Days Statistics' : '🏆 All Time Statistics'}
                <br />
                <span style={{ fontSize: '1.2rem', fontWeight: 'normal', opacity: 0.8 }}>
                  for {selectedApp}
                </span>
              </h2>

              {agentStats.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '40px 20px' }}>
                  <div style={{ fontSize: '3rem', marginBottom: '20px' }}>📋</div>
                  <h3 style={{ color: 'white', marginBottom: '15px' }}>No Agent Assignments Found</h3>
                  <p style={{ fontSize: '16px', opacity: 0.9, color: 'white' }}>
                    No reviews have been assigned to agents for {selectedApp} in the selected time period.
                    <br />
                    Visit the Access Reviews page to assign reviews to agents.
                  </p>
                </div>
              ) : (
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
                  gap: '20px',
                  marginTop: '30px'
                }}>
                  {agentStats
                    .sort((a, b) => b.review_count - a.review_count)
                    .map((agent, index) => (
                      <div
                        key={index}
                        style={{
                          background: 'rgba(255,255,255,0.95)',
                          borderRadius: '12px',
                          padding: '25px',
                          color: '#333',
                          boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                          border: index === 0 && agent.review_count > 0 ? '3px solid #10B981' : 'none',
                          position: 'relative'
                        }}
                      >
                        {index === 0 && agent.review_count > 0 && (
                          <div style={{
                            position: 'absolute',
                            top: '-10px',
                            right: '15px',
                            background: '#10B981',
                            color: 'white',
                            padding: '5px 12px',
                            borderRadius: '15px',
                            fontSize: '0.8rem',
                            fontWeight: 'bold'
                          }}>
                            🏆 Top Performer
                          </div>
                        )}

                        <div style={{ textAlign: 'center' }}>
                          <div style={{
                            fontSize: '2.5rem',
                            fontWeight: 'bold',
                            color: '#10B981',
                            marginBottom: '10px'
                          }}>
                            {agent.review_count}
                          </div>
                          <div style={{
                            fontSize: '1.2rem',
                            fontWeight: 'bold',
                            marginBottom: '8px',
                            color: '#333'
                          }}>
                            {agent.agent_name || agent.earned_by || 'Unassigned'}
                          </div>
                          <div style={{
                            fontSize: '0.9rem',
                            color: '#666',
                            marginBottom: '15px'
                          }}>
                            Reviews Handled
                          </div>

                          {agent.review_count > 0 && (
                            <div style={{
                              background: '#f8f9fa',
                              borderRadius: '8px',
                              padding: '12px',
                              fontSize: '0.85rem',
                              color: '#666'
                            }}>
                              <div>📅 Period: {timeFilter === 'last_30_days' ? 'Last 30 Days' : 'All Time'}</div>
                              <div>📱 App: {selectedApp}</div>
                            </div>
                          )}
                        </div>
                      </div>
                    ))}
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default ReviewCredit;