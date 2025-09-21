import { useState } from 'react';
import './MonitoringControls.css';

const MonitoringControls = ({ selectedApp, onMonitoringComplete }) => {
  const [isMonitoring, setIsMonitoring] = useState(false);
  const [lastRun, setLastRun] = useState(null);
  const [results, setResults] = useState(null);

  const runMonitoring = async (appName = null) => {
    setIsMonitoring(true);
    setResults(null);

    try {
      const response = await fetch('http://localhost:8000/api/run-monitoring.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          app_name: appName
        })
      });

      const data = await response.json();

      if (data.success) {
        setResults(data);
        setLastRun(new Date().toLocaleString());
        
        // Notify parent component that monitoring is complete
        if (onMonitoringComplete) {
          onMonitoringComplete(data);
        }
      } else {
        throw new Error(data.error || 'Monitoring failed');
      }
    } catch (error) {
      console.error('Monitoring error:', error);
      setResults({
        success: false,
        error: error.message
      });
    } finally {
      setIsMonitoring(false);
    }
  };

  return (
    <div className="monitoring-controls">
      <div className="monitoring-header">
        <h3>🔍 Review Monitoring</h3>
        <p>Check for new reviews on first pages of Shopify app stores</p>
      </div>

      <div className="monitoring-buttons">
        {selectedApp ? (
          <button
            onClick={() => runMonitoring(selectedApp)}
            disabled={isMonitoring}
            className="monitor-btn monitor-single"
          >
            {isMonitoring ? '🔄 Monitoring...' : `🔍 Monitor ${selectedApp}`}
          </button>
        ) : (
          <p className="no-app-message">Select an app to monitor specific reviews</p>
        )}

        <button
          onClick={() => runMonitoring()}
          disabled={isMonitoring}
          className="monitor-btn monitor-all"
        >
          {isMonitoring ? '🔄 Monitoring All Apps...' : '🔍 Monitor All Apps'}
        </button>
      </div>

      {lastRun && (
        <div className="last-run">
          <small>Last run: {lastRun}</small>
        </div>
      )}

      {results && (
        <div className="monitoring-results">
          {results.success ? (
            <div className="results-success">
              <h4>✅ Monitoring Complete</h4>
              {results.app_name ? (
                <div className="single-app-results">
                  <p><strong>{results.app_name}:</strong> {results.new_reviews_found} new reviews found</p>
                  {results.updated_stats && (
                    <div className="app-stats">
                      <span>This month: {results.updated_stats.this_month}</span>
                      <span>Last 30 days: {results.updated_stats.last_30_days}</span>
                      <span>Total: {results.updated_stats.total_reviews}</span>
                    </div>
                  )}
                </div>
              ) : (
                <div className="all-apps-results">
                  <p><strong>Total new reviews found:</strong> {results.total_new_reviews}</p>
                  <div className="execution-time">
                    <small>Completed in {results.execution_time_ms}ms</small>
                  </div>
                  {results.updated_stats && (
                    <div className="all-stats">
                      <h5>Updated Statistics:</h5>
                      {Object.entries(results.updated_stats).map(([appName, stats]) => (
                        <div key={appName} className="app-stat-row">
                          <span className="app-name">{appName}:</span>
                          <span className="stat-values">
                            {stats.this_month} this month | {stats.last_30_days} last 30 days
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>
          ) : (
            <div className="results-error">
              <h4>❌ Monitoring Failed</h4>
              <p>{results.error}</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default MonitoringControls;
