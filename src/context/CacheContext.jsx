import { createContext, useContext, useRef, useCallback } from 'react';

/**
 * Global Cache Context for sharing data across all pages/components
 * Provides application-level caching for app-specific data
 */
const CacheContext = createContext();

const CACHE_DURATION = 30 * 60 * 1000; // 30 minutes

export const CacheProvider = ({ children }) => {
  const cacheRef = useRef({});

  /**
   * Generate a cache key based on app name and optional filter
   */
  const getCacheKey = useCallback((appName, filter = null, customKey = null) => {
    if (customKey) return customKey;
    return filter ? `${appName}_${filter}` : appName;
  }, []);

  /**
   * Check if a cache entry is still valid
   */
  const isCacheValid = useCallback((cacheEntry) => {
    if (!cacheEntry) return false;
    return Date.now() - cacheEntry.timestamp < CACHE_DURATION;
  }, []);

  /**
   * Get cached data if it exists and is valid
   */
  const getCachedData = useCallback((appName, filter = null, customKey = null) => {
    const key = getCacheKey(appName, filter, customKey);
    const cached = cacheRef.current[key];
    
    if (isCacheValid(cached)) {
      console.log(`✅ Cache HIT for key: ${key}`);
      return cached.data;
    }
    
    if (cached) {
      console.log(`⏰ Cache EXPIRED for key: ${key}`);
      delete cacheRef.current[key];
    }
    
    return null;
  }, [getCacheKey, isCacheValid]);

  /**
   * Set data in cache with timestamp
   */
  const setCachedData = useCallback((appName, data, filter = null, customKey = null) => {
    const key = getCacheKey(appName, filter, customKey);
    cacheRef.current[key] = {
      data,
      timestamp: Date.now()
    };
    console.log(`💾 Cache SET for key: ${key}`);
  }, [getCacheKey]);

  /**
   * Clear cache for a specific app
   */
  const clearAppCache = useCallback((appName) => {
    const keysToDelete = Object.keys(cacheRef.current).filter(key => 
      key.startsWith(appName)
    );
    keysToDelete.forEach(key => delete cacheRef.current[key]);
    console.log(`🗑️ Cleared cache for app: ${appName}`);
  }, []);

  /**
   * Clear all cache
   */
  const clearAllCache = useCallback(() => {
    cacheRef.current = {};
    console.log(`🗑️ Cleared all cache`);
  }, []);

  /**
   * Get cache statistics (for debugging)
   */
  const getCacheStats = useCallback(() => {
    const stats = {
      totalEntries: Object.keys(cacheRef.current).length,
      entries: Object.keys(cacheRef.current).map(key => ({
        key,
        age: Date.now() - cacheRef.current[key].timestamp,
        isValid: isCacheValid(cacheRef.current[key])
      }))
    };
    return stats;
  }, [isCacheValid]);

  const value = {
    getCachedData,
    setCachedData,
    clearAppCache,
    clearAllCache,
    getCacheStats,
    getCacheKey
  };

  return (
    <CacheContext.Provider value={value}>
      {children}
    </CacheContext.Provider>
  );
};

/**
 * Hook to use the cache context
 */
export const useCache = () => {
  const context = useContext(CacheContext);
  if (!context) {
    throw new Error('useCache must be used within a CacheProvider');
  }
  return context;
};

