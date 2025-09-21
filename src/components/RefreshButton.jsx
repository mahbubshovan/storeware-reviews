import { useState } from 'react';
import { useScrapeStatus } from '../hooks/useScrapeStatus';
import './RefreshButton.css';

/**
 * Smart refresh button with rate limiting and countdown timer
 */
function RefreshButton({ appSlug, onRefreshComplete, className = '' }) {
  const {
    scrapeStatus,
    isLoading,
    error,
    countdown,
    triggerScrape,
    canScrapeNow,
    hasUpstreamChanges,
    formatRemainingTime
  } = useScrapeStatus(appSlug);

  const [isRefreshing, setIsRefreshing] = useState(false);
  const [refreshError, setRefreshError] = useState(null);

  const handleRefresh = async () => {
    if (!canScrapeNow || isRefreshing) return;

    setIsRefreshing(true);
    setRefreshError(null);

    try {
      const result = await triggerScrape();
      
      // Notify parent component
      if (onRefreshComplete) {
        onRefreshComplete(result);
      }
      
      // Show success message briefly
      setTimeout(() => {
        setIsRefreshing(false);
      }, 2000);
      
    } catch (err) {
      setRefreshError(err.message);
      setIsRefreshing(false);
    }
  };

  if (isLoading) {
    return (
      <div className={`refresh-button-container ${className}`}>
        <button className="refresh-button loading" disabled>
          <span className="spinner"></span>
          Loading...
        </button>
      </div>
    );
  }

  return (
    <div className={`refresh-button-container ${className}`}>
      {/* Main refresh button */}
      <button
        className={`refresh-button ${canScrapeNow ? 'enabled' : 'disabled'} ${isRefreshing ? 'refreshing' : ''}`}
        onClick={handleRefresh}
        disabled={!canScrapeNow || isRefreshing}
        title={canScrapeNow ? 'Refresh data now' : (formatRemainingTime(countdown) ? `Next refresh in ${formatRemainingTime(countdown)}` : 'Refresh available')}
      >
        {isRefreshing ? (
          <>
            <span className="spinner"></span>
            Refreshing...
          </>
        ) : canScrapeNow ? (
          <>
            <span className="refresh-icon">🔄</span>
            Refresh Now
          </>
        ) : (
          <>
            <span className="clock-icon">⏱️</span>
            {formatRemainingTime(countdown) ? `Next refresh in ${formatRemainingTime(countdown)}` : 'Refresh Available'}
          </>
        )}
      </button>

      {/* Status indicators */}
      <div className="refresh-status">
        {hasUpstreamChanges && (
          <div className="upstream-changes-indicator" title="New reviews detected upstream">
            <span className="indicator-icon">🔔</span>
            <span className="indicator-text">New reviews available</span>
          </div>
        )}

        {scrapeStatus.last_run_at && (
          <div className="last-refresh-time">
            Last refreshed: {new Date(scrapeStatus.last_run_at).toLocaleString()}
          </div>
        )}

        {!canScrapeNow && countdown > 0 && formatRemainingTime(countdown) && (
          <div className="countdown-display">
            <div className="countdown-bar">
              <div
                className="countdown-progress"
                style={{
                  width: `${Math.max(0, 100 - (countdown / 21600) * 100)}%`
                }}
              ></div>
            </div>
            <div className="countdown-text">
              Rate limit: {formatRemainingTime(countdown)} remaining
            </div>
          </div>
        )}
      </div>

      {/* Error display */}
      {(error || refreshError) && (
        <div className="refresh-error">
          <span className="error-icon">⚠️</span>
          {refreshError || error}
        </div>
      )}

      {/* Success message */}
      {isRefreshing && (
        <div className="refresh-success">
          <span className="success-icon">✅</span>
          Data refreshed successfully!
        </div>
      )}
    </div>
  );
}

export default RefreshButton;
