import { useState, useEffect } from 'react';

const ReviewCreditSimple = () => {
  const [timeFilter, setTimeFilter] = useState('last_30_days');
  const [agents, setAgents] = useState([]);
  const [selectedAgent, setSelectedAgent] = useState(null);
  const [selectedAgentDetails, setSelectedAgentDetails] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [customDateRange, setCustomDateRange] = useState({ start: '', end: '' });
  const [showCustomDatePicker, setShowCustomDatePicker] = useState(false);

  // Fetch all agents
  const fetchAgents = async () => {
    console.log('Fetching all agents with filter:', timeFilter);
    setLoading(true);
    setError(null);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/agent-review-stats.php?filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch agents');
      const data = await response.json();
      console.log('Agents loaded:', data);

      if (data.message === 'no_agents') {
        setAgents([]);
        setError('No agents have been assigned reviews yet. You can assign reviews in the Access Review page.');
      } else {
        setAgents(data);
      }
    } catch (err) {
      console.error('Error fetching agents:', err);
      setError('Failed to load agent statistics');
      setAgents([]);
    } finally {
      setLoading(false);
    }
  };

  // Fetch details for a specific agent
  const fetchAgentDetails = async (agentName) => {
    console.log('Fetching details for agent:', agentName);
    setLoading(true);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      let url = `/backend/api/agent-review-stats.php?agent_name=${encodeURIComponent(agentName)}&filter=${timeFilter}&${cacheBust}`;

      // Add custom date range if applicable
      if (timeFilter === 'custom' && customDateRange.start && customDateRange.end) {
        url += `&start_date=${customDateRange.start}&end_date=${customDateRange.end}`;
      }

      const response = await fetch(url);
      if (!response.ok) throw new Error('Failed to fetch agent details');
      const data = await response.json();
      console.log('Agent details loaded:', data);
      setSelectedAgentDetails(data);
    } catch (err) {
      console.error('Error fetching agent details:', err);
      setError('Failed to load agent details');
    } finally {
      setLoading(false);
    }
  };

  // Load agents on component mount and when filters change
  useEffect(() => {
    console.log('Fetching agents - Filter:', timeFilter, 'Custom Range:', customDateRange);
    fetchAgents();
    // If an agent is already selected, re-fetch their details with the new filter
    if (selectedAgent) {
      fetchAgentDetails(selectedAgent);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [timeFilter, customDateRange.start, customDateRange.end]);

  // Handle agent selection
  const handleAgentSelect = (agentName) => {
    console.log('Agent selected:', agentName);
    setSelectedAgent(agentName);
    fetchAgentDetails(agentName);
  };

  return (
    <div style={{
      maxWidth: '1400px',
      width: '100%',
      background: '#f8f9fa',
      padding: '20px',
      margin: '0 auto',
      borderRadius: '16px',
      boxSizing: 'border-box',
    }}>
      <style>
        {`
          @media (max-width: 768px) {
            .agent-reviews-container {
              padding: 12px !important;
            }

            .agent-reviews-header {
              flex-direction: column !important;
              gap: 15px !important;
              text-align: center !important;
            }

            .agent-reviews-title h1 {
              font-size: 24px !important;
            }

            .agent-reviews-title p {
              font-size: 13px !important;
            }

            .agent-selector-container {
              flex-direction: column !important;
              width: 100% !important;
              gap: 15px !important;
              justify-content: center !important;
            }

            .agent-selector-label {
              width: 100% !important;
              justify-content: center !important;
            }

            .agent-selector-dropdown {
              width: 100% !important;
              min-width: auto !important;
            }

            .time-filter-tabs {
              width: 100% !important;
              flex-wrap: wrap !important;
            }

            .time-filter-button {
              flex: 1 !important;
              min-width: 120px !important;
              padding: 10px 12px !important;
              font-size: 13px !important;
            }

            .stats-grid {
              grid-template-columns: 1fr !important;
            }

            .stat-card {
              padding: 16px !important;
            }

            .stat-value {
              font-size: 24px !important;
            }

            .stat-content h3 {
              font-size: 12px !important;
            }

            .reviews-by-app-grid {
              grid-template-columns: 1fr !important;
            }

            .app-review-card {
              padding: 12px !important;
            }

            .app-review-card .app-name {
              font-size: 14px !important;
            }

            .app-review-count {
              font-size: 1.3rem !important;
            }
          }

          @media (max-width: 480px) {
            .agent-reviews-container {
              padding: 8px !important;
            }

            .agent-reviews-header {
              padding: 20px 12px !important;
            }

            .agent-reviews-title h1 {
              font-size: 20px !important;
            }

            .agent-reviews-title p {
              font-size: 12px !important;
            }

            .agent-selector-label {
              font-size: 14px !important;
            }

            .agent-selector-dropdown {
              padding: 10px 12px !important;
              font-size: 13px !important;
            }

            .time-filter-button {
              padding: 8px 10px !important;
              font-size: 12px !important;
            }

            .stat-card {
              padding: 12px !important;
              gap: 10px !important;
            }

            .stat-icon {
              width: 45px !important;
              height: 45px !important;
              font-size: 20px !important;
            }

            .stat-value {
              font-size: 20px !important;
            }

            .stat-content h3 {
              font-size: 11px !important;
            }

            .stat-label {
              font-size: 10px !important;
            }

            .app-review-card {
              padding: 10px !important;
            }

            .app-name {
              font-size: 13px !important;
            }

            .app-review-count {
              font-size: 1.2rem !important;
            }
          }
        `}
      </style>
      {/* Green Header */}
      <div className="agent-reviews-header" style={{
        background: 'linear-gradient(135deg, #10B981 0%, #0d9488 100%)',
        color: 'white',
        padding: '40px 20px',
        textAlign: 'center',
        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
        borderRadius: '12px',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        gap: '20px',
        flexWrap: 'wrap'
      }}>
        <div className="agent-reviews-title" style={{ flex: 1, minWidth: '200px' }}>
          <h1 style={{ margin: '0 0 10px 0', fontSize: '2.5rem' }}>👥 Agent Reviews Dashboard</h1>
          <p style={{ margin: '0', fontSize: '1.1rem', opacity: 0.95 }}>Track individual agent performance across all apps</p>
        </div>
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

        {/* Agent Selector and Time Filter */}
        <div className="agent-selector-container" style={{ marginBottom: '30px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '30px', flexWrap: 'wrap' }}>
          {/* Agent Dropdown */}
          <div className="agent-selector-label" style={{
            display: 'flex',
            alignItems: 'center',
            gap: '12px',
            padding: '5px 0'
          }}>
            <label style={{
              color: '#333',
              fontWeight: 'bold',
              fontSize: '1rem',
              whiteSpace: 'nowrap'
            }}>
              👥 Select Agent:
            </label>
            <select
              className="agent-selector-dropdown"
              value={selectedAgent || ''}
              onChange={(e) => {
                const agentName = e.target.value;
                console.log('Agent selected:', agentName);
                if (agentName) {
                  handleAgentSelect(agentName);
                } else {
                  setSelectedAgent(null);
                  setSelectedAgentDetails(null);
                }
              }}
              style={{
                padding: '10px 14px',
                borderRadius: '8px',
                border: '2px solid #e5e7eb',
                fontSize: '0.95rem',
                minWidth: '200px',
                background: 'white',
                color: '#333',
                cursor: 'pointer'
              }}
            >
              <option value="">Choose an agent to analyze</option>
              {agents.map((agent) => (
                <option key={agent.agent_name} value={agent.agent_name}>
                  {agent.agent_name}
                </option>
              ))}
            </select>
          </div>

          {/* Time Filter Tabs */}
          <div className="time-filter-tabs" style={{ display: 'inline-flex', background: '#e5e7eb', borderRadius: '10px', padding: '5px', gap: '5px' }}>
            <button
              className="time-filter-button"
              onClick={() => {
                console.log('Filter changed to: last_30_days');
                setTimeFilter('last_30_days');
                setShowCustomDatePicker(false);
              }}
              style={{
                padding: '10px 20px',
                background: timeFilter === 'last_30_days' ? '#10B981' : 'transparent',
                color: timeFilter === 'last_30_days' ? 'white' : '#666',
                border: 'none',
                borderRadius: '8px',
                cursor: 'pointer',
                fontWeight: 'bold',
                transition: 'all 0.3s ease',
                fontSize: '0.95rem',
                whiteSpace: 'nowrap'
              }}
            >
              📊 Last 30 Days
            </button>
            <button
              className="time-filter-button"
              onClick={() => {
                console.log('Filter changed to: all_time');
                setTimeFilter('all_time');
                setShowCustomDatePicker(false);
              }}
              style={{
                padding: '10px 20px',
                background: timeFilter === 'all_time' ? '#10B981' : 'transparent',
                color: timeFilter === 'all_time' ? 'white' : '#666',
                border: 'none',
                borderRadius: '8px',
                cursor: 'pointer',
                fontWeight: 'bold',
                transition: 'all 0.3s ease',
                fontSize: '0.95rem',
                whiteSpace: 'nowrap'
              }}
            >
              🏆 All Time
            </button>
            <button
              className="time-filter-button"
              onClick={() => {
                console.log('Filter changed to: custom');
                setShowCustomDatePicker(!showCustomDatePicker);
                if (!showCustomDatePicker) {
                  setTimeFilter('custom');
                }
              }}
              style={{
                padding: '10px 20px',
                background: timeFilter === 'custom' ? '#10B981' : 'transparent',
                color: timeFilter === 'custom' ? 'white' : '#666',
                border: 'none',
                borderRadius: '8px',
                cursor: 'pointer',
                fontWeight: 'bold',
                transition: 'all 0.3s ease',
                fontSize: '0.95rem',
                whiteSpace: 'nowrap'
              }}
            >
              📅 Custom Range
            </button>
          </div>
        </div>

        {/* Custom Date Range Picker */}
        {showCustomDatePicker && (
          <div style={{
            marginTop: '20px',
            marginBottom: '20px',
            padding: '25px',
            background: 'white',
            borderRadius: '12px',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
            maxWidth: '650px',
            margin: '20px auto'
          }}>
            <div style={{
              display: 'flex',
              flexDirection: 'column',
              gap: '20px'
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
                      fetchAgents();
                      if (selectedAgent) {
                        fetchAgentDetails(selectedAgent);
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
            </div>

            {customDateRange.start && customDateRange.end && (
              <div style={{
                fontSize: '0.85rem',
                color: '#666',
                textAlign: 'center',
                padding: '12px',
                marginTop: '15px',
                background: '#f0fdf4',
                borderRadius: '6px'
              }}>
                📊 Showing data from <strong>{customDateRange.start}</strong> to <strong>{customDateRange.end}</strong>
              </div>
            )}
          </div>
        )}

        {/* Content */}
        <div style={{
          background: 'white',
          borderRadius: '15px',
          padding: '40px',
          boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
          minHeight: '400px'
        }}>
          {loading ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>⏳</div>
              <h2 style={{ color: '#333' }}>Loading Agent Statistics...</h2>
            </div>
          ) : error && agents.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>⚠️</div>
              <h2 style={{ color: '#333', marginBottom: '15px' }}>No Data Available</h2>
              <p style={{ fontSize: '16px', color: '#666' }}>{error}</p>
            </div>
          ) : error && selectedAgent && !selectedAgentDetails ? (
            // Show error when agent is selected but details failed to load
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '3rem', marginBottom: '20px' }}>❌</div>
              <h2 style={{ color: '#333', marginBottom: '15px' }}>Failed to Load Agent Details</h2>
              <p style={{ fontSize: '16px', color: '#666' }}>{error}</p>
              <button
                onClick={() => {
                  setError(null);
                  if (selectedAgent) fetchAgentDetails(selectedAgent);
                }}
                style={{
                  marginTop: '20px',
                  padding: '12px 24px',
                  background: '#10B981',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  fontSize: '1rem',
                  fontWeight: '600',
                  cursor: 'pointer'
                }}
              >
                🔄 Retry
              </button>
            </div>
          ) : !selectedAgent ? (
            // Show default message when no agent is selected
            <div style={{ textAlign: 'center', padding: '60px 20px' }}>
              <div style={{ fontSize: '4rem', marginBottom: '20px' }}>👥</div>
              <h2 style={{ marginBottom: '15px', color: '#333' }}>Choose an Agent to Analyze</h2>
              <p style={{ fontSize: '18px', color: '#666' }}>
                Select an agent from the dropdown above to view their review statistics across all apps
              </p>
            </div>
          ) : selectedAgent && selectedAgentDetails ? (
            // Show selected agent details
            <>
              <h2 style={{ margin: '0 0 30px 0', color: '#333', textAlign: 'center' }}>
                📋 {selectedAgentDetails.agent_name}
                <br />
                <span style={{ fontSize: '1.1rem', fontWeight: 'normal', color: '#666' }}>
                  {timeFilter === 'last_30_days'
                    ? 'Last 30 Days'
                    : timeFilter === 'custom' && customDateRange.start && customDateRange.end
                    ? `${customDateRange.start} to ${customDateRange.end}`
                    : 'All Time'} Statistics
                </span>
              </h2>

              <div className="stats-grid" style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))',
                gap: '12px',
                marginBottom: '30px'
              }}>
                <div className="stat-card" style={{
                  background: 'white',
                  borderRadius: '8px',
                  paddingTop: '12px',
                  paddingRight: '12px',
                  paddingBottom: '12px',
                  paddingLeft: '6px',
                  border: '1px solid #e5e7eb',
                  borderLeft: '4px solid #10B981',
                  display: 'flex',
                  flexDirection: 'column',
                  justifyContent: 'flex-start',
                  alignItems: 'flex-start',
                  textAlign: 'left'
                }}>
                  <div style={{paddingLeft: '10px'}}>
                    <div style={{ fontSize: '0.7rem', color: '#999', marginBottom: '8px', fontWeight: '600', textTransform: 'uppercase', letterSpacing: '0.5px' }}>Total Reviews</div>
                    <div className="stat-value" style={{ fontSize: '2rem', fontWeight: 'bold', color: '#333', lineHeight: '1' }}>
                      {selectedAgentDetails.total_reviews}
                    </div>
                  </div>
                </div>

                <div className="stat-card" style={{
                  background: 'white',
                  borderRadius: '8px',
                  paddingTop: '12px',
                  paddingRight: '12px',
                  paddingBottom: '12px',
                  paddingLeft: '6px',
                  border: '1px solid #e5e7eb',
                  borderLeft: '4px solid #10B981',
                  display: 'flex',
                  flexDirection: 'column',
                  justifyContent: 'flex-start',
                  alignItems: 'flex-start',
                  textAlign: 'left'
                }}>
                  <div style={{paddingLeft: '10px'}}>
                    <div style={{ fontSize: '0.7rem', color: '#999', marginBottom: '8px', fontWeight: '600', textTransform: 'uppercase', letterSpacing: '0.5px' }}>Apps Covered</div>
                    <div className="stat-value" style={{ fontSize: '2rem', fontWeight: 'bold', color: '#333', lineHeight: '1' }}>
                      {selectedAgentDetails.by_app.length}
                    </div>
                  </div>
                </div>
              </div>

              {/* Reviews by App */}
              <div style={{
                background: '#f8f9fa',
                borderRadius: '12px',
                padding: '20px'
              }}>
                <h3 style={{ margin: '0 0 15px 0', color: '#333' }}>Reviews by App</h3>
                <div className="reviews-by-app-grid" style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))',
                  gap: '15px'
                }}>
                  {selectedAgentDetails.by_app.map((app, index) => (
                    <div key={index} className="app-review-card" style={{
                      background: 'white',
                      borderRadius: '8px',
                      overflow: 'hidden',
                      border: '1px solid #e5e7eb',
                      padding: '10px'
                    }}>
                      <div className="app-name" style={{
                        background: '#d1fae5',
                        padding: '10px 15px',
                        fontWeight: 'bold',
                        color: '#065f46',
                        fontSize: '0.95rem',
                        display: 'inline-block',
                        width: 'auto'
                      }}>
                        {app.app_name}
                      </div>
                      <div style={{ padding: '15px' }}>
                        <div className="app-review-count" style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#10B981' }}>
                          {app.review_count} {app.review_count === 1 ? 'review' : 'reviews'}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </>
          ) : null}
        </div>
      </div>
    </div>
  );
};

export default ReviewCreditSimple;
