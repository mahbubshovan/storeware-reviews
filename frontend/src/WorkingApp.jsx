import { useState, useEffect } from 'react'

function WorkingApp() {
  const [selectedApp, setSelectedApp] = useState('');
  const [apps, setApps] = useState([]);
  const [stats, setStats] = useState(null);
  const [distribution, setDistribution] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fetch available apps
  useEffect(() => {
    const fetchApps = async () => {
      try {
        const response = await fetch('http://localhost:8000/api/available-apps.php');
        const data = await response.json();
        if (data.success) {
          setApps(data.apps);
        }
      } catch (err) {
        console.error('Error fetching apps:', err);
        setError('Failed to load apps');
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
        console.log('Fetching data for app:', selectedApp);

        // Fetch stats
        console.log('Fetching stats...');
        const statsRes = await fetch(`http://localhost:8000/api/this-month-reviews.php?app_name=${selectedApp}`);
        console.log('Stats response status:', statsRes.status);

        if (!statsRes.ok) {
          throw new Error(`Stats API failed: ${statsRes.status}`);
        }

        const statsData = await statsRes.json();
        console.log('Stats data received:', statsData);

        // Fetch distribution
        console.log('Fetching distribution...');
        const distRes = await fetch(`http://localhost:8000/api/review-distribution.php?app_name=${selectedApp}`);
        console.log('Distribution response status:', distRes.status);

        if (!distRes.ok) {
          throw new Error(`Distribution API failed: ${distRes.status}`);
        }

        const distData = await distRes.json();
        console.log('Distribution data received:', distData);

        setStats(statsData);
        setDistribution(distData);
        console.log('Data set successfully');

      } catch (err) {
        console.error('Detailed error fetching app data:', err);
        setError(`Failed to load app data: ${err.message}`);
      } finally {
        setLoading(false);
      }
    };

    fetchAppData();
  }, [selectedApp]);

  const styles = {
    app: {
      padding: '20px',
      fontFamily: 'Arial, sans-serif',
      maxWidth: '1200px',
      margin: '0 auto',
      backgroundColor: '#f8f9fa',
      minHeight: '100vh'
    },
    header: {
      textAlign: 'center',
      marginBottom: '30px',
      padding: '20px',
      backgroundColor: 'white',
      borderRadius: '12px',
      boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
    },
    selector: {
      marginBottom: '30px',
      padding: '20px',
      backgroundColor: 'white',
      borderRadius: '12px',
      boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
    },
    select: {
      width: '100%',
      padding: '12px',
      fontSize: '16px',
      border: '2px solid #dee2e6',
      borderRadius: '8px',
      backgroundColor: 'white'
    },
    card: {
      padding: '20px',
      backgroundColor: 'white',
      borderRadius: '12px',
      boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
      marginBottom: '20px'
    },
    noApp: {
      textAlign: 'center',
      padding: '60px 20px',
      backgroundColor: 'white',
      borderRadius: '12px',
      border: '2px dashed #dee2e6'
    },
    distributionGrid: {
      display: 'grid',
      gridTemplateColumns: 'repeat(5, 1fr)',
      gap: '15px',
      marginTop: '20px'
    },
    distributionItem: {
      textAlign: 'center',
      padding: '15px',
      backgroundColor: '#f0f9ff',
      borderRadius: '8px',
      border: '1px solid #e0f2fe'
    }
  };

  return (
    <div style={styles.app}>
      <header style={styles.header}>
        <h1>🛍️ Shopify App Review Analytics</h1>
        <p>Complete rating distribution data from live Shopify App Store pages</p>
      </header>

      {/* App Selector */}
      <div style={styles.selector}>
        <label htmlFor="app-select" style={{ display: 'block', marginBottom: '10px', fontWeight: 'bold' }}>
          Select App to Analyze:
        </label>
        <select
          id="app-select"
          value={selectedApp}
          onChange={(e) => setSelectedApp(e.target.value)}
          style={styles.select}
        >
          <option value="">Choose an app to analyze</option>
          {apps.map((app) => (
            <option key={app} value={app}>{app}</option>
          ))}
        </select>
        
        {error && (
          <div style={{ color: 'red', marginTop: '10px', padding: '10px', backgroundColor: '#ffe6e6', borderRadius: '6px' }}>
            ❌ {error}
            <div style={{ marginTop: '10px' }}>
              <button
                onClick={() => {
                  setError(null);
                  if (selectedApp) {
                    // Retry fetching data
                    setSelectedApp('');
                    setTimeout(() => setSelectedApp(selectedApp), 100);
                  }
                }}
                style={{ padding: '8px 16px', backgroundColor: '#dc3545', color: 'white', border: 'none', borderRadius: '4px' }}
              >
                Retry
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Content Area */}
      {selectedApp ? (
        <div>
          {loading ? (
            <div style={{ ...styles.card, textAlign: 'center' }}>
              <h2>🔄 Loading {selectedApp} data...</h2>
            </div>
          ) : (
            <>
              {/* Stats */}
              {stats && stats.success && (
                <div style={styles.card}>
                  <h2>📊 Recent Statistics for {selectedApp}</h2>
                  <p><strong>This Month Reviews:</strong> {stats.count || 0}</p>
                </div>
              )}

              {/* Complete Rating Distribution */}
              {distribution && distribution.success && (
                <div style={styles.card}>
                  <h2>⭐ Complete Rating Distribution</h2>
                  <p><strong>Total Reviews:</strong> {distribution.total_reviews}</p>
                  <p style={{ color: '#666', fontSize: '14px' }}>
                    📡 Live data from Shopify App Store
                  </p>
                  
                  <div style={styles.distributionGrid}>
                    <div style={styles.distributionItem}>
                      <div style={{ fontSize: '18px', marginBottom: '8px' }}>⭐⭐⭐⭐⭐</div>
                      <div style={{ fontSize: '28px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.five_star}
                      </div>
                      <div style={{ fontSize: '12px', color: '#666' }}>5 Stars</div>
                    </div>
                    <div style={styles.distributionItem}>
                      <div style={{ fontSize: '18px', marginBottom: '8px' }}>⭐⭐⭐⭐</div>
                      <div style={{ fontSize: '28px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.four_star}
                      </div>
                      <div style={{ fontSize: '12px', color: '#666' }}>4 Stars</div>
                    </div>
                    <div style={styles.distributionItem}>
                      <div style={{ fontSize: '18px', marginBottom: '8px' }}>⭐⭐⭐</div>
                      <div style={{ fontSize: '28px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.three_star}
                      </div>
                      <div style={{ fontSize: '12px', color: '#666' }}>3 Stars</div>
                    </div>
                    <div style={styles.distributionItem}>
                      <div style={{ fontSize: '18px', marginBottom: '8px' }}>⭐⭐</div>
                      <div style={{ fontSize: '28px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.two_star}
                      </div>
                      <div style={{ fontSize: '12px', color: '#666' }}>2 Stars</div>
                    </div>
                    <div style={styles.distributionItem}>
                      <div style={{ fontSize: '18px', marginBottom: '8px' }}>⭐</div>
                      <div style={{ fontSize: '28px', fontWeight: 'bold', color: '#0369a1' }}>
                        {distribution.distribution.one_star}
                      </div>
                      <div style={{ fontSize: '12px', color: '#666' }}>1 Star</div>
                    </div>
                  </div>
                </div>
              )}
            </>
          )}
        </div>
      ) : (
        <div style={styles.noApp}>
          <h2 style={{ color: '#6c757d', marginBottom: '15px' }}>Choose an app to analyze</h2>
          <p style={{ color: '#6c757d', fontSize: '18px' }}>
            Select an app from the dropdown above to view its complete rating distribution and analytics
          </p>
          <div style={{ marginTop: '20px', fontSize: '14px', color: '#999' }}>
            📡 All data is scraped live from Shopify App Store pages
          </div>
        </div>
      )}
    </div>
  )
}

export default WorkingApp
