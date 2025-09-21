import { useState, useEffect, useCallback } from 'react';
import { useClientId } from './useClientId';

/**
 * Custom hook for managing scraping status and rate limiting
 */
export function useScrapeStatus(appSlug) {
  const { clientId, isLoading: clientIdLoading } = useClientId();
  const [scrapeStatus, setScrapeStatus] = useState({
    allowed_now: false,
    next_run_at: null,
    last_run_at: null,
    remaining_seconds: 0,
    has_upstream_changes: false
  });
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [countdown, setCountdown] = useState(0);

  // Update countdown every second
  useEffect(() => {
    if (scrapeStatus.remaining_seconds > 0) {
      const interval = setInterval(() => {
        const now = new Date();
        const nextRun = new Date(scrapeStatus.next_run_at);
        const remaining = Math.max(0, Math.floor((nextRun - now) / 1000));
        
        setCountdown(remaining);
        
        if (remaining === 0) {
          // Rate limit expired, refresh status
          fetchScrapeStatus();
        }
      }, 1000);

      return () => clearInterval(interval);
    }
  }, [scrapeStatus.next_run_at, scrapeStatus.remaining_seconds]);

  /**
   * Fetch current scrape status for the app
   */
  const fetchScrapeStatus = useCallback(async () => {
    if (!clientId || !appSlug) return;

    try {
      setIsLoading(true);
      setError(null);

      // For now, just use localStorage-based rate limiting until new API is fully deployed
      // This prevents JSON parsing errors and provides immediate functionality

      // Fallback: simulate rate limiting behavior with localStorage
      const lastScrapeKey = `last_scrape_${appSlug}_${clientId}`;
      const lastScrapeTime = localStorage.getItem(lastScrapeKey);

      if (lastScrapeTime) {
        const timeSinceLastScrape = Date.now() - parseInt(lastScrapeTime);
        const sixHoursInMs = 6 * 60 * 60 * 1000;
        const remainingMs = sixHoursInMs - timeSinceLastScrape;

        if (remainingMs > 0) {
          setScrapeStatus({
            allowed_now: false,
            next_run_at: new Date(parseInt(lastScrapeTime) + sixHoursInMs).toISOString(),
            last_run_at: new Date(parseInt(lastScrapeTime)).toISOString(),
            remaining_seconds: Math.ceil(remainingMs / 1000),
            has_upstream_changes: false
          });
          setCountdown(Math.ceil(remainingMs / 1000));
        } else {
          setScrapeStatus({
            allowed_now: true,
            next_run_at: null,
            last_run_at: new Date(parseInt(lastScrapeTime)).toISOString(),
            remaining_seconds: 0,
            has_upstream_changes: false
          });
          setCountdown(0);
        }
      } else {
        // First time - allow immediate scrape
        setScrapeStatus({
          allowed_now: true,
          next_run_at: null,
          last_run_at: null,
          remaining_seconds: 0,
          has_upstream_changes: false
        });
        setCountdown(0);
      }
    } catch (err) {
      console.error('Error fetching scrape status:', err);
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  }, [clientId, appSlug]);

  /**
   * Trigger a scrape for the current app
   */
  const triggerScrape = useCallback(async () => {
    if (!clientId || !appSlug) {
      throw new Error('Client ID or app slug not available');
    }

    try {
      // Convert app slug back to app name for existing API - STANDARDIZED MAPPING
      const appNames = {
        'storeseo': 'StoreSEO',
        'storefaq': 'StoreFAQ',
        'vidify': 'Vidify',
        'customer-review-app': 'TrustSync',
        'product-options-4': 'EasyFlow',
        'betterdocs-knowledgebase': 'BetterDocs FAQ Knowledge Base'
      };

      const appName = appNames[appSlug] || appSlug;

      // For now, use existing API directly until new endpoints are deployed

      // Fallback to existing API
      const response = await fetch('/api/scrape-app.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ app_name: appName })
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.error || 'Scraping failed');
      }

      // Store scrape time for rate limiting
      const lastScrapeKey = `last_scrape_${appSlug}_${clientId}`;
      localStorage.setItem(lastScrapeKey, Date.now().toString());

      // Refresh status after successful trigger
      await fetchScrapeStatus();

      return data;
    } catch (err) {
      console.error('Error triggering scrape:', err);
      throw err;
    }
  }, [clientId, appSlug, fetchScrapeStatus]);

  /**
   * Check scrape job status (placeholder for future implementation)
   */
  const checkJobStatus = useCallback(async () => {
    // Placeholder - will be implemented when new API endpoints are deployed
    return { status: 'completed' };
  }, []);

  /**
   * Format remaining time as human-readable string
   */
  const formatRemainingTime = useCallback((seconds) => {
    if (seconds <= 0) return null; // Return null instead of '0s'

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
      return `${hours}h ${minutes}m`;
    } else if (minutes > 0) {
      return `${minutes}m ${secs}s`;
    } else {
      return `${secs}s`;
    }
  }, []);

  // Fetch status when clientId or appSlug changes
  useEffect(() => {
    if (!clientIdLoading && clientId && appSlug) {
      fetchScrapeStatus();
    }
  }, [clientId, appSlug, clientIdLoading, fetchScrapeStatus]);

  return {
    scrapeStatus,
    isLoading: isLoading || clientIdLoading,
    error,
    countdown,
    triggerScrape,
    checkJobStatus,
    refreshStatus: fetchScrapeStatus,
    formatRemainingTime,
    // Computed properties for easier use
    canScrapeNow: scrapeStatus.allowed_now && countdown === 0,
    hasUpstreamChanges: scrapeStatus.has_upstream_changes,
    nextScrapeTime: scrapeStatus.next_run_at ? new Date(scrapeStatus.next_run_at) : null,
    lastScrapeTime: scrapeStatus.last_run_at ? new Date(scrapeStatus.last_run_at) : null
  };
}

export default useScrapeStatus;
