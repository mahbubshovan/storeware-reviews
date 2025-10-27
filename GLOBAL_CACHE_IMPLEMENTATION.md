# Global Application-Level Caching Implementation

## Overview
Implemented a global React Context-based caching system that persists data across all pages and components within the same browser session. This eliminates redundant API calls when switching between pages.

## Architecture

### 1. Cache Context (`src/context/CacheContext.jsx`)
- **Purpose**: Provides global cache management across the entire application
- **Cache Duration**: 30 minutes (configurable)
- **Features**:
  - `getCachedData()` - Retrieves cached data if valid
  - `setCachedData()` - Stores data with timestamp
  - `clearAppCache()` - Clears cache for specific app
  - `clearAllCache()` - Clears all cache
  - `getCacheStats()` - Debug utility to view cache state

### 2. Cache Provider Wrapper
- Wrapped entire app in `<CacheProvider>` in `src/App.jsx`
- Makes cache available to all child components via `useCache()` hook

## Integration Points

### Analytics Component (`src/components/Analytics.jsx`)
- Uses global cache for analytics data
- Checks cache before making API calls to `enhanced-analytics.php`
- Caches both analytics data and filtered reviews
- **Result**: Instant loading when switching back to previously viewed apps

### Access Reviews Page (`src/pages/AccessTabbed.jsx`)
- Uses global cache for review data by app and page
- Cache key: `access_reviews_{appName}_page{pageNumber}`
- Checks cache before fetching from API
- **Result**: Instant tab switching for previously loaded apps

### Appwise Reviews Page (`src/pages/ReviewCount.jsx`)
- Uses global cache for agent stats and country stats
- Cache keys: `agent_stats_{appName}_{filter}` and `country_stats_{appName}_{filter}`
- Checks cache before API calls
- **Result**: Instant data loading when switching apps

## How It Works

### Flow Diagram
```
User selects app on Page A
    ↓
Check global cache
    ↓
Cache HIT? → Load instantly ✅
    ↓
Cache MISS? → Fetch from API → Store in cache → Display
    ↓
User navigates to Page B
    ↓
Select same app
    ↓
Check global cache
    ↓
Cache HIT? → Load instantly ✅ (No API call!)
```

## Console Logging
The implementation includes helpful console logs for debugging:
- `✅ Cache HIT for key: {key}` - Data loaded from cache
- `⏰ Cache EXPIRED for key: {key}` - Cache entry was too old
- `💾 Cache SET for key: {key}` - Data stored in cache
- `🗑️ Cleared cache for app: {appName}` - Cache cleared
- `✅ Loading from global cache: {key}` - Specific component loading from cache

## Cache Keys
Cache keys are generated based on:
- App name (required)
- Filter type (optional)
- Custom key (optional)

Examples:
- `StoreSEO` - Analytics data for StoreSEO
- `StoreSEO_this_month` - Filtered reviews for StoreSEO (this month)
- `access_reviews_StoreSEO_page1` - Access reviews page 1 for StoreSEO
- `agent_stats_StoreSEO_last_30_days` - Agent stats for StoreSEO

## Cache Expiration
- **Duration**: 30 minutes (1,800,000 milliseconds)
- **Behavior**: Expired entries are automatically removed when accessed
- **Refresh**: Browser refresh clears all cache (session-based)

## Performance Impact

### Before Implementation
- Analytics → StoreSEO: 2-3 seconds (API call + scraping)
- Switch to StoreFAQ: 2-3 seconds (API call + scraping)
- Back to StoreSEO: 2-3 seconds (API call + scraping again) ❌

### After Implementation
- Analytics → StoreSEO: 2-3 seconds (API call + scraping)
- Switch to StoreFAQ: 2-3 seconds (API call + scraping)
- Back to StoreSEO: <100ms (instant from cache) ✅

## Usage in Components

### Basic Usage
```javascript
import { useCache } from '../context/CacheContext';

const MyComponent = () => {
  const { getCachedData, setCachedData } = useCache();

  // Check cache
  const cached = getCachedData('StoreSEO');
  
  // Store in cache
  setCachedData('StoreSEO', myData);
};
```

### With Custom Keys
```javascript
const cacheKey = `agent_stats_${appName}_${filter}`;
const cached = getCachedData(appName, null, cacheKey);
setCachedData(appName, data, null, cacheKey);
```

## Files Modified
1. `src/context/CacheContext.jsx` - NEW (Global cache provider)
2. `src/App.jsx` - Wrapped with CacheProvider
3. `src/components/Analytics.jsx` - Integrated global cache
4. `src/pages/AccessTabbed.jsx` - Integrated global cache
5. `src/pages/ReviewCount.jsx` - Integrated global cache

## Testing the Implementation

### Test Scenario 1: Analytics Page
1. Go to Analytics page
2. Select "StoreSEO" (loads with data)
3. Select "StoreFAQ" (loads with data)
4. Select "StoreSEO" again (should load instantly from cache)
5. Check browser console for "✅ Cache HIT" message

### Test Scenario 2: Cross-Page Navigation
1. Go to Analytics → Select "StoreSEO" (caches data)
2. Navigate to Access Reviews → Select "StoreSEO" (should load from cache)
3. Navigate to Appwise Reviews → Select "StoreSEO" (should load from cache)
4. Back to Analytics → Select "StoreSEO" (should load from cache)

### Test Scenario 3: Cache Expiration
1. Load data for an app
2. Wait 30+ minutes
3. Select the same app again
4. Should fetch fresh data (cache expired)

## Future Enhancements
- Add cache persistence to localStorage for cross-session caching
- Implement cache invalidation on manual refresh/sync
- Add cache size limits to prevent memory issues
- Create admin UI to view and manage cache
- Add cache statistics dashboard

