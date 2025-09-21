import { useState, useEffect, useCallback } from 'react';
import { reviewsAPI } from '../services/api';

/**
 * Hook to manage fresh data synchronization
 * Automatically ensures data is current when apps are selected
 */
export const useFreshData = (selectedApp) => {
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [lastRefresh, setLastRefresh] = useState(null);
  const [error, setError] = useState(null);

  // Check if data needs refresh (older than 5 minutes)
  const needsRefresh = useCallback(() => {
    if (!lastRefresh) return true;
    const fiveMinutesAgo = Date.now() - (5 * 60 * 1000);
    return lastRefresh < fiveMinutesAgo;
  }, [lastRefresh]);

  // Force refresh data for selected app
  const refreshData = useCallback(async (appName = selectedApp, force = false) => {
    if (!appName) return null;
    
    // Skip if already refreshing or recently refreshed (unless forced)
    if (isRefreshing || (!force && !needsRefresh())) {
      return null;
    }

    try {
      setIsRefreshing(true);
      setError(null);
      
      console.log(`🔄 Refreshing data for ${appName}...`);
      
      const response = await reviewsAPI.getFreshReviews(appName);
      
      if (response.data.success) {
        setLastRefresh(Date.now());
        console.log(`✅ Fresh data loaded for ${appName}:`, {
          reviews: response.data.reviews.length,
          stats: response.data.stats
        });
        return response.data;
      } else {
        throw new Error(response.data.error || 'Failed to refresh data');
      }
    } catch (err) {
      console.error('Error refreshing data:', err);
      setError(err.message);
      return null;
    } finally {
      setIsRefreshing(false);
    }
  }, [selectedApp, isRefreshing, needsRefresh]);

  // Auto-refresh when app changes
  useEffect(() => {
    if (selectedApp && needsRefresh()) {
      refreshData(selectedApp);
    }
  }, [selectedApp, refreshData, needsRefresh]);

  return {
    isRefreshing,
    lastRefresh,
    error,
    refreshData,
    needsRefresh: needsRefresh()
  };
};

/**
 * Hook for components that need fresh data
 * Provides both cached and fresh data options
 */
export const useAppData = (selectedApp, dataType = 'reviews') => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const { isRefreshing, refreshData } = useFreshData(selectedApp);

  const fetchData = useCallback(async (fresh = false) => {
    if (!selectedApp) {
      setData(null);
      return;
    }

    try {
      setLoading(true);
      setError(null);

      let response;
      
      if (fresh) {
        // Get fresh data
        response = await reviewsAPI.getFreshReviews(selectedApp);
        if (response.data.success) {
          setData(response.data);
        }
      } else {
        // Get cached data first for faster loading
        switch (dataType) {
          case 'reviews':
            response = await reviewsAPI.getLatestReviews(selectedApp);
            setData(response.data);
            break;
          case 'stats':
            const [thisMonth, last30Days, avgRating] = await Promise.all([
              reviewsAPI.getThisMonthReviews(selectedApp),
              reviewsAPI.getLast30DaysReviews(selectedApp),
              reviewsAPI.getAverageRating(selectedApp)
            ]);
            setData({
              thisMonth: thisMonth.data,
              last30Days: last30Days.data,
              avgRating: avgRating.data
            });
            break;
          case 'distribution':
            response = await reviewsAPI.getReviewDistribution(selectedApp);
            setData(response.data);
            break;
          default:
            throw new Error(`Unknown data type: ${dataType}`);
        }
      }
    } catch (err) {
      console.error(`Error fetching ${dataType}:`, err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [selectedApp, dataType]);

  // Load cached data immediately, then refresh if needed
  useEffect(() => {
    fetchData(false); // Load cached data first
  }, [fetchData]);

  return {
    data,
    loading,
    error,
    isRefreshing,
    refresh: () => fetchData(true),
    refreshData
  };
};
