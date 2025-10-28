import { useState, useEffect } from 'react';

const ReviewCreditSimple = () => {
  const [timeFilter, setTimeFilter] = useState('last_30_days');
  const [agentStats, setAgentStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [apps, setApps] = useState([]);
  const [selectedApp, setSelectedApp] = useState('all');

  // Fetch available apps
  const fetchApps = async () => {
    try {
      console.log('Fetching apps...');
      const response = await fetch('/backend/api/apps.php');
      if (!response.ok) throw new Error('Failed to fetch apps');
      const data = await response.json();
      console.log('Apps loaded:', data);
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

    console.log('Fetching agent stats for:', selectedApp, 'filter:', timeFilter);
    setLoading(true);
    setError(null);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      const response = await fetch(`/backend/api/agent-stats.php?app_name=${encodeURIComponent(selectedApp)}&filter=${timeFilter}&${cacheBust}`);
      if (!response.ok) throw new Error('Failed to fetch agent stats');
      const data = await response.json();
      console.log('Agent stats loaded:', data);

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
    console.log('Component mounted, fetching apps...');
    fetchApps();
  }, []);

  // Fetch agent stats when app or time filter changes
  useEffect(() => {
    console.log('App or filter changed:', selectedApp, timeFilter);
    if (selectedApp !== 'all') {
      fetchAgentStats();
    } else {
      setAgentStats([]);
      setError(null);
    }
  }, [selectedApp, timeFilter]);

  console.log('Rendering component with:', { selectedApp, timeFilter, agentStats, loading, error, apps });

  return (
    <div style={{
      maxWidth: '1400px',
      width: '100%',
      background: '#f8f9fa',
      padding: '20px',
      margin: '0 auto',
      borderRadius: '16px',
    }}>
      {/* Green Header */}
      <div style={{
        background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
        color: 'white',
        padding: '40px 20px',
        textAlign: 'center',
        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
        borderRadius: '12px'
      }}>
        <h1 style={{ margin: '0 0 10px 0', fontSize: '2.5rem' }}>🎯 Agent Reviews Dashboard</h1>
        <p style={{ margin: '0', fontSize: '1.1rem', opacity: 0.95 }}>Track agent performance with Last 30 Days and All Time views</p>
      </div>

      {/* Main Content */}
      <div style={{ padding: '40px 0px', maxWidth: '1200px', margin: '0 auto' }}>
        {/* Error Message */}
        {error && (
          <div style={{
            background: '#fee',
            padding: '16px',
            margin: '0 0 30px 0',
            borderRadius: '8px',
            fontSize: '14px',
            color: '#c33',
            border: '1px solid #fcc'
          }}>
            <p style={{ margin: '0' }}>{error}</p>
          </div>
        )}

        {/* App Selector */}
        <div style={{ marginBottom: '30px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: '10px',
            padding: '5px 0'
          }}>
            <label style={{
              color: '#333',
              fontWeight: 'bold',
              fontSize: '1.1rem'
            }}>
              📱 Select App:
            </label>
            <select
              value={selectedApp}
              onChange={(e) => {
                console.log('App selected:', e.target.value);
                setSelectedApp(e.target.value);
              }}
              style={{
                padding: '12px 16px',
                borderRadius: '8px',
                border: '2px solid #e5e7eb',
                fontSize: '1rem',
                minWidth: '250px',
                background: 'white',
                color: '#333',
                cursor: 'pointer'
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

          {/* Time Filter Tabs */}
          {selectedApp !== 'all' && (
            <div style={{}}>
              <div style={{ display: 'inline-flex', background: '#e5e7eb', borderRadius: '10px', padding: '5px' }}>
                <button
                  onClick={() => {
                    console.log('Filter changed to: last_30_days');
                    setTimeFilter('last_30_days');
                  }}
                  style={{
                    padding: '12px 24px',
                    background: timeFilter === 'last_30_days' ? '#10B981' : 'transparent',
                    color: timeFilter === 'last_30_days' ? 'white' : '#666',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontWeight: 'bold',
                    transition: 'all 0.3s ease'
                  }}
                >
                  📊 Last 30 Days
                </button>
                <button
                  onClick={() => {
                    console.log('Filter changed to: all_time');
                    setTimeFilter('all_time');
                  }}
                  style={{
                    padding: '12px 24px',
                    background: timeFilter === 'all_time' ? '#10B981' : 'transparent',
                    color: timeFilter === 'all_time' ? 'white' : '#666',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontWeight: 'bold',
                    transition: 'all 0.3s ease'
                  }}
                >
                  🏆 All Time
                </button>
              </div>
            </div>
          )}
        </div>

        

        {/* Content */}
        <div style={{
          background: 'white',
          borderRadius: '15px',
          padding: '40px',
          boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
          minHeight: '400px'
        }}>
          {selectedApp === 'all' ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '4rem', marginBottom: '20px' }}>📱</div>
              <h2 style={{ marginBottom: '15px', color: '#333' }}>Choose an App to Analyze</h2>
              <p style={{ fontSize: '18px', color: '#666' }}>
                Select an app from the dropdown above to view agent review statistics
              </p>
            </div>
          ) : loading ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>⏳</div>
              <h2 style={{ color: '#333' }}>Loading Agent Statistics...</h2>
            </div>
          ) : error ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>⚠️</div>
              <h2 style={{ color: '#333', marginBottom: '15px' }}>No Data Available</h2>
              <p style={{ fontSize: '16px', color: '#666' }}>{error}</p>
            </div>
          ) : (
            <>
              <h2 style={{ marginBottom: '30px', color: '#333', textAlign: 'center' }}>
                {timeFilter === 'last_30_days' ? '📊 Last 30 Days Statistics' : '🏆 All Time Statistics'}
                <br />
                <span style={{ fontSize: '1.2rem', fontWeight: 'normal', color: '#666' }}>
                  for {selectedApp}
                </span>
              </h2>

              {agentStats.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '40px 20px' }}>
                  <div style={{ fontSize: '3rem', marginBottom: '20px' }}>📋</div>
                  <h3 style={{ color: '#333', marginBottom: '15px' }}>No Agent Assignments Found</h3>
                  <p style={{ fontSize: '16px', color: '#666' }}>
                    No reviews have been assigned to agents for {selectedApp} in the selected time period.
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
                          background: '#f8f9fa',
                          borderRadius: '12px',
                          padding: '25px',
                          color: '#333',
                          boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
                          border: index === 0 && agent.review_count > 0 ? '3px solid #10B981' : '1px solid #e5e7eb',
                          position: 'relative'
                        }}
                      >
                        {index === 0 && agent.review_count > 0 && (
                          <div style={{
                            position: 'absolute',
                            top: '-15px',
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
                              background: 'white',
                              borderRadius: '8px',
                              padding: '12px',
                              fontSize: '0.85rem',
                              color: '#666',
                              border: '1px solid #e5e7eb',
                              display: 'flex',
                              flexDirection: 'column',
                              gap: '5px'

                            }}>
                              <div>📱 App: <strong>{selectedApp}</strong></div>
                              <div>📅 Period: {timeFilter === 'last_30_days' ? 'Last 30 Days' : 'All Time'}</div>
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

export default ReviewCreditSimple;
