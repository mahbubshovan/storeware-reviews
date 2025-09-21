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
        // Use the actual message from the API response instead of hardcoding
        const message = response.data.message;
        const isHistorical = message.includes('historical');

        if (isHistorical) {
          // For historical reviews (apps with no recent reviews)
          setSuccess(`${appName} data loaded! (${message})`);
        } else {
          // For recent reviews - only show success for actual scraping, not rate limiting
          if (!message.includes('Rate limited')) {
            let successMessage = `Successfully ${message} for ${appName}!`;

            // Add smart sync information if available
            if (response.data.smart_sync) {
              const sync = response.data.smart_sync;
              if (sync.new_added > 0) {
                successMessage += ` | 🧠 Smart Sync: ${sync.new_added} new reviews added to Access Reviews (${sync.duplicates_skipped} duplicates skipped)`;
              } else if (sync.total_found > 0) {
                successMessage += ` | 🧠 Smart Sync: All ${sync.total_found} reviews already exist in Access Reviews`;
              } else {
                successMessage += ` | 🧠 Smart Sync: No new reviews for today`;
              }
            }

            setSuccess(successMessage);
          }
        }

        // Notify parent component that scraping is complete
        onScrapeComplete(appName, response.data.scraped_count);
        // Clear success message after 8 seconds (longer for smart sync info)
        const timeout = response.data.smart_sync ? 8000 : 5000;
        setTimeout(() => setSuccess(null), timeout);
      } else {
        // For apps with no recent reviews, show a more user-friendly message
        const errorMsg = response.data.message || response.data.error || 'Unknown error';
        if (errorMsg.includes('No live reviews found')) {
          setSuccess(`${appName} data loaded! (No new reviews in last 30 days)`);
          onScrapeComplete(appName, 0);
          setTimeout(() => setSuccess(null), 5000);
        } else {
          setError(`Scraping failed: ${errorMsg}`);
        }
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
    <div className="app-selector-new">
      <div className="selector-container-new">
        <select
          id="app-select"
          value={selectedApp || ''}
          onChange={handleAppChange}
          disabled={scraping}
          className="app-dropdown-new"
        >
          <option value="">Choose an app to analyze...</option>
          {apps.length > 0 ? apps.map((app) => (
            <option key={app} value={app}>
              {app}
            </option>
          )) : (
            <option disabled>Loading apps...</option>
          )}
        </select>

        {scraping && (
          <div className="scraping-status-new">
            <div className="spinner-new"></div>
            <span>Live scraping {selectedApp} reviews...</span>
            <div className="scraping-details-new">
              <small>Extracting fresh data from Shopify</small>
            </div>
          </div>
        )}
      </div>

      {success && (
        <div className="success-message-new">
          {success}
        </div>
      )}

      {error && (
        <div className="error-message-new">
          {error}
        </div>
      )}
    </div>
  );
};

export default AppSelector;
