import { useState, useEffect } from 'react'

function SimpleApp() {
  const [selectedApp, setSelectedApp] = useState('');
  const [apps, setApps] = useState([]);
  const [stats, setStats] = useState(null);
  const [distribution, setDistribution] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  console.log('SimpleApp rendering, selectedApp:', selectedApp);

  // Fetch available apps on mount
  useEffect(() => {
    const fetchApps = async () => {
      try {
        console.log('Fetching available apps...');
        const response = await fetch('http://localhost:8000/api/available-apps.php');
        const data = await response.json();
        console.log('Apps data:', data);
        
        if (data.success) {
          setApps(data.apps);
        } else {
          setError('Failed to load apps');
        }
      } catch (err) {
        console.error('Error fetching apps:', err);
        setError('Failed to connect to server: ' + err.message);
      }
    };
    
    fetchApps();
  }, []);

  // Fetch app data when app is selected
  useEffect(() => {
    if (!selectedApp) {
      setStats(null);
      setDistribution(null);
      return;
    }

    const fetchAppData = async () => {
      try {
        setLoading(true);
        setError(null);
        console.log('Fetching data for:', selectedApp);

        // Fetch stats
        const statsResponse = await fetch(`http://localhost:8000/api/this-month-reviews.php?app_name=${selectedApp}`);
        const statsData = await statsResponse.json();
        console.log('Stats data:', statsData);

        // Fetch rating distribution
        const distResponse = await fetch(`http://localhost:8000/api/review-distribution.php?app_name=${selectedApp}`);
        const distData = await distResponse.json();
        console.log('Distribution data:', distData);

        setStats(statsData);
        setDistribution(distData);
        
      } catch (err) {
        console.error('Error fetching app data:', err);
        setError('Failed to load app data: ' + err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchAppData();
  }, [selectedApp]);

  const appStyle = {
    padding: '20px',
    fontFamily: 'Arial, sans-serif',
    maxWidth: '1200px',
    margin: '0 auto'
  };

  const headerStyle = {
    textAlign: 'center',
    marginBottom: '30px',
    borderBottom: '2px solid #eee',
    paddingBottom: '20px'
  };

  const selectorStyle = {
    marginBottom: '30px',
    padding: '20px',
    backgroundColor: '#f8f9fa',
    borderRadius: '8px'
  };

  const contentStyle = {
    display: 'grid',
    gap: '20px'
  };

  const cardStyle = {
    padding: '20px',
    backgroundColor: 'white',
    border: '1px solid #ddd',
    borderRadius: '8px',
    boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
  };

  return (
    <div style={appStyle}>
      <header style={headerStyle}>
        <h1>🛍️ Shopify App Review Analytics</h1>
        <p>Complete rating distribution data from live Shopify App Store pages</p>
      </header>

      {/* App Selector */}
      <div style={selectorStyle}>
        <label htmlFor="app-select" style={{ display: 'block', marginBottom: '10px', fontWeight: 'bold' }}>
          Select App to Analyze:
        </label>
        <select
          id="app-select"
          value={selectedApp}
          onChange={(e) => setSelectedApp(e.target.value)}
          style={{
            width: '100%',
            padding: '12px',
            fontSize: '16px',
            border: '2px solid #ddd',
            borderRadius: '6px',
            backgroundColor: 'white'
          }}
        >
          <option value="">Choose an app to analyze</option>
          {apps.map((app) => (
            <option key={app} value={app}>{app}</option>
          ))}
        </select>
        
        {error && (
          <div style={{ color: 'red', marginTop: '10px', padding: '10px', backgroundColor: '#ffe6e6', borderRadius: '4px' }}>
            ❌ {error}
          </div>
        )}
      </div>

      {/* Content Area */}
      {selectedApp ? (
        <div style={contentStyle}>
          {loading ? (
            <div style={{ textAlign: 'center', padding: '40px' }}>
              <h2>Loading {selectedApp} data...</h2>
            </div>
          ) : (
            <>
              {/* Stats Cards */}
              {stats && (
                <div style={cardStyle}>
                  <h2>📊 Statistics for {selectedApp}</h2>
                  <p><strong>This Month Reviews:</strong> {stats.count || 0}</p>
                </div>
              )}

              {/* Rating Distribution */}
              {distribution && distribution.success && (
                <div style={cardStyle}>
                  <h2>⭐ Complete Rating Distribution</h2>
                  <p><strong>Total Reviews:</strong> {distribution.total_reviews}</p>
                  <div style={{ display: 'grid', gridTemplateColumns: 'repeat(5, 1fr)', gap: '10px', marginTop: '15px' }}>
                    <div style={{ textAlign: 'center', padding: '10px', backgroundColor: '#f0f9ff', borderRadius: '6px' }}>
                      <div>⭐⭐⭐⭐⭐</div>
                      <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.five_star}
                      </div>
                    </div>
                    <div style={{ textAlign: 'center', padding: '10px', backgroundColor: '#f0f9ff', borderRadius: '6px' }}>
                      <div>⭐⭐⭐⭐</div>
                      <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.four_star}
                      </div>
                    </div>
                    <div style={{ textAlign: 'center', padding: '10px', backgroundColor: '#f0f9ff', borderRadius: '6px' }}>
                      <div>⭐⭐⭐</div>
                      <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.three_star}
                      </div>
                    </div>
                    <div style={{ textAlign: 'center', padding: '10px', backgroundColor: '#f0f9ff', borderRadius: '6px' }}>
                      <div>⭐⭐</div>
                      <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.two_star}
                      </div>
                    </div>
                    <div style={{ textAlign: 'center', padding: '10px', backgroundColor: '#f0f9ff', borderRadius: '6px' }}>
                      <div>⭐</div>
                      <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.one_star}
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </>
          )}
        </div>
      ) : (
        <div style={{ 
          textAlign: 'center', 
          padding: '60px 20px',
          backgroundColor: '#f8f9fa',
          borderRadius: '12px',
          border: '2px dashed #dee2e6'
        }}>
          <h2 style={{ color: '#6c757d', marginBottom: '10px' }}>Choose an app to analyze</h2>
          <p style={{ color: '#6c757d', fontSize: '18px' }}>
            Select an app from the dropdown above to view its complete rating distribution and analytics
          </p>
        </div>
      )}
    </div>
  )
}

export default SimpleApp
