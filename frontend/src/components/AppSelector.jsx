import { useState, useEffect } from 'react';
import { reviewsAPI } from '../services/api';

const AppSelector = ({ selectedApp, onAppSelect, onScrapeComplete }) => {
  const [apps, setApps] = useState([]);
  const [loading, setLoading] = useState(false);
  const [scraping, setScraping] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  useEffect(() => {
    fetchAvailableApps();
  }, []);

  const fetchAvailableApps = async () => {
    try {
      setLoading(true);
      console.log('Fetching available apps...');
      const response = await reviewsAPI.getAvailableApps();
      console.log('API Response:', response);
      console.log('Apps data:', response.data);
      setApps(response.data.apps);
      setError(null);
    } catch (err) {
      console.error('Detailed error fetching apps:', {
        message: err.message,
        response: err.response,
        request: err.request,
        config: err.config
      });

      let errorMessage = 'Failed to fetch available apps';
      if (err.response) {
        errorMessage += ` (Server error: ${err.response.status})`;
      } else if (err.request) {
        errorMessage += ' (No response from server)';
      } else {
        errorMessage += ` (${err.message})`;
      }

      setError(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const handleAppChange = async (event) => {
    const appName = event.target.value;

    if (!appName) {
      onAppSelect(null);
      return;
    }

    // Set the selected app immediately
    onAppSelect(appName);

    // Start scraping
    try {
      setScraping(true);
      setError(null);
      setSuccess(null);

      const response = await reviewsAPI.scrapeApp(appName);

      if (response.data.success) {
        // Show success message
        setSuccess(`Successfully scraped ${response.data.scraped_count} new reviews for ${appName}!`);
        // Notify parent component that scraping is complete
        onScrapeComplete(appName, response.data.scraped_count);
        // Clear success message after 5 seconds
        setTimeout(() => setSuccess(null), 5000);
      } else {
        setError(`Scraping failed: ${response.data.error}`);
      }
    } catch (err) {
      console.error('Scraping error details:', err);

      let errorMessage = 'Unknown error occurred';

      if (err.response) {
        // Server responded with error status
        console.error('Server response:', err.response.data);
        if (err.response.data && typeof err.response.data === 'object') {
          errorMessage = err.response.data.error || err.response.data.message || `Server error: ${err.response.status}`;
        } else {
          errorMessage = `Server error: ${err.response.status}`;
        }
      } else if (err.request) {
        // Request was made but no response received
        console.error('No response received:', err.request);
        errorMessage = 'No response from server. Please check if the backend is running.';
      } else {
        // Something else happened
        console.error('Request setup error:', err.message);
        errorMessage = err.message || 'Request setup error';
      }

      setError(`Scraping error: ${errorMessage}`);
    } finally {
      setScraping(false);
    }
  };

  if (loading) {
    return <div className="app-selector loading">Loading apps...</div>;
  }

  return (
    <div className="app-selector">
      <div className="selector-container">
        <label htmlFor="app-select" className="selector-label">
          Select Shopify App:
        </label>
        <select
          id="app-select"
          value={selectedApp || ''}
          onChange={handleAppChange}
          disabled={scraping}
          className="app-dropdown"
        >
          <option value="">Choose an app to analyze...</option>
          {apps.map((app) => (
            <option key={app} value={app}>
              {app}
            </option>
          ))}
        </select>
        
        {scraping && (
          <div className="scraping-status">
            <div className="spinner"></div>
            <span>Live scraping {selectedApp} reviews from multiple pages...</span>
            <div className="scraping-details">
              <small>Checking review dates and extracting fresh data</small>
            </div>
          </div>
        )}

        {selectedApp && !scraping && (
          <div className="selected-app-display">
            <h3>📊 Analyzing: {selectedApp}</h3>
            <p>Showing analytics data for {selectedApp} app reviews</p>
          </div>
        )}
      </div>
      
      {success && (
        <div className="success-message">
          {success}
        </div>
      )}

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}
    </div>
  );
};

export default AppSelector;
