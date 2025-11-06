import { useState, useEffect } from 'react';

const ReviewCredit = () => {
  const [timeFilter, setTimeFilter] = useState('last_30_days');
  const [agents, setAgents] = useState([]);
  const [selectedAgent, setSelectedAgent] = useState(null);
  const [selectedAgentDetails, setSelectedAgentDetails] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch all agents
  const fetchAgents = async () => {
    console.log('Fetching all agents with filter:', timeFilter);
    setLoading(true);
    setError(null);
    try {
      const cacheBust = `_t=${Date.now()}&_cache_bust=${Math.random()}`;
      const response = await fetch(`/backend/api/agent-review-stats.php?filter=${timeFilter}&${cacheBust}`);
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
      const response = await fetch(`/backend/api/agent-review-stats.php?agent_name=${encodeURIComponent(agentName)}&filter=${timeFilter}&${cacheBust}`);
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

  // Load agents on component mount
  useEffect(() => {
    console.log('Component mounted, fetching agents...');
    fetchAgents();
  }, []);

  // Fetch agents when time filter changes
  useEffect(() => {
    console.log('Time filter changed:', timeFilter);
    fetchAgents();
    setSelectedAgent(null);
    setSelectedAgentDetails(null);
  }, [timeFilter]);

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
        <h1 style={{ margin: '0 0 10px 0', fontSize: '2.5rem' }}>👥 Agent Reviews Dashboard</h1>
        <p style={{ margin: '0', fontSize: '1.1rem', opacity: 0.95 }}>Track individual agent performance across all apps</p>
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
        <div style={{ marginBottom: '30px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '20px', flexWrap: 'wrap' }}>
          {/* Agent Dropdown */}
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
              👥 Select Agent:
            </label>
            <select
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
              <option value="">Choose an agent to analyze</option>
              {agents.map((agent) => (
                <option key={agent.agent_name} value={agent.agent_name}>
                  {agent.agent_name} ({agent.review_count} reviews)
                </option>
              ))}
            </select>
          </div>

          {/* Time Filter Tabs */}
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
                  {timeFilter === 'last_30_days' ? 'Last 30 Days' : 'All Time'} Statistics
                </span>
              </h2>

              <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                gap: '20px',
                marginBottom: '30px'
              }}>
                <div style={{
                  background: '#f0fdf4',
                  borderRadius: '12px',
                  padding: '20px',
                  border: '2px solid #10B981'
                }}>
                  <div style={{ fontSize: '0.9rem', color: '#666', marginBottom: '8px' }}>Total Reviews</div>
                  <div style={{ fontSize: '2.5rem', fontWeight: 'bold', color: '#10B981' }}>
                    {selectedAgentDetails.total_reviews}
                  </div>
                </div>

                <div style={{
                  background: '#fef3c7',
                  borderRadius: '12px',
                  padding: '20px',
                  border: '2px solid #f59e0b'
                }}>
                  <div style={{ fontSize: '0.9rem', color: '#666', marginBottom: '8px' }}>Average Rating</div>
                  <div style={{ fontSize: '2.5rem', fontWeight: 'bold', color: '#f59e0b' }}>
                    {selectedAgentDetails.average_rating}⭐
                  </div>
                </div>

                <div style={{
                  background: '#ede9fe',
                  borderRadius: '12px',
                  padding: '20px',
                  border: '2px solid #a78bfa'
                }}>
                  <div style={{ fontSize: '0.9rem', color: '#666', marginBottom: '8px' }}>Apps Covered</div>
                  <div style={{ fontSize: '2.5rem', fontWeight: 'bold', color: '#a78bfa' }}>
                    {selectedAgentDetails.by_app.length}
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
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))',
                  gap: '15px'
                }}>
                  {selectedAgentDetails.by_app.map((app, index) => (
                    <div key={index} style={{
                      background: 'white',
                      borderRadius: '8px',
                      padding: '15px',
                      border: '1px solid #e5e7eb'
                    }}>
                      <div style={{ fontWeight: 'bold', marginBottom: '8px', color: '#333' }}>
                        {app.app_name}
                      </div>
                      <div style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#10B981', marginBottom: '8px' }}>
                        {app.review_count} reviews
                      </div>
                      <div style={{ fontSize: '0.9rem', color: '#666' }}>
                        Avg Rating: {app.average_rating}⭐
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

export default ReviewCredit;