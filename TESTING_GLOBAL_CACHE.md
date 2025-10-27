# Testing Global Application-Level Caching

## Quick Start Testing

### Test 1: Analytics Page Caching
**Objective**: Verify that switching between apps on Analytics page loads from cache

**Steps**:
1. Open browser DevTools (F12) → Console tab
2. Go to **Analytics** page
3. Select **"StoreSEO"** from dropdown
   - Wait for data to load (2-3 seconds)
   - Look for console logs showing API calls
4. Select **"StoreFAQ"** from dropdown
   - Wait for data to load (2-3 seconds)
   - Look for console logs showing API calls
5. Select **"StoreSEO"** again
   - **Expected**: Data loads instantly (<100ms)
   - **Console**: Should show `✅ Cache HIT for key: StoreSEO`

**Success Criteria**: 
- ✅ First load of StoreSEO: 2-3 seconds (API call)
- ✅ Load of StoreFAQ: 2-3 seconds (API call)
- ✅ Second load of StoreSEO: <100ms (cache hit)

---

### Test 2: Cross-Page Navigation Caching
**Objective**: Verify that data cached on one page is available on another page

**Steps**:
1. Open browser DevTools (F12) → Console tab
2. Go to **Analytics** page
3. Select **"StoreSEO"** 
   - Wait for data to load
   - Console shows API call logs
4. Navigate to **Access Reviews** page
5. Select **"StoreSEO"** from the app tabs
   - **Expected**: Data loads instantly from global cache
   - **Console**: Should show `✅ Loading from global cache: access_reviews_StoreSEO_page1`
6. Navigate to **Appwise Reviews** page
7. Select **"StoreSEO"** from dropdown
   - **Expected**: Data loads instantly from global cache
   - **Console**: Should show cache hit messages
8. Go back to **Analytics** page
9. Select **"StoreSEO"** again
   - **Expected**: Data loads instantly from global cache

**Success Criteria**:
- ✅ First load on Analytics: API call (2-3 seconds)
- ✅ Load on Access Reviews: Instant from cache
- ✅ Load on Appwise Reviews: Instant from cache
- ✅ Back to Analytics: Instant from cache

---

### Test 3: Cache Expiration
**Objective**: Verify that cache expires after 30 minutes

**Steps**:
1. Go to Analytics page
2. Select "StoreSEO" (loads and caches)
3. Open browser DevTools → Console
4. Run this command in console:
   ```javascript
   // Manually expire the cache by setting timestamp to 31 minutes ago
   // This simulates cache expiration
   ```
5. Select a different app, then select "StoreSEO" again
6. **Expected**: Should fetch fresh data from API (not from cache)
7. **Console**: Should show `⏰ Cache EXPIRED for key: StoreSEO`

---

### Test 4: Filter-Based Caching
**Objective**: Verify that different filters are cached separately

**Steps**:
1. Go to **Analytics** page
2. Select **"StoreSEO"**
3. Change filter to **"This Month"**
   - Data loads and caches with key: `StoreSEO_this_month`
4. Change filter to **"Last 90 Days"**
   - Data loads and caches with key: `StoreSEO_last_90_days`
5. Change filter back to **"This Month"**
   - **Expected**: Loads instantly from cache
   - **Console**: Should show `✅ Cache HIT for key: StoreSEO_this_month`

**Success Criteria**:
- ✅ Different filters have separate cache entries
- ✅ Switching between filters loads from cache
- ✅ Each filter maintains its own cached data

---

### Test 5: Page Navigation Performance
**Objective**: Measure performance improvement with caching

**Steps**:
1. Open browser DevTools → Performance tab
2. Go to Analytics → Select StoreSEO (record time)
3. Go to Access Reviews → Select StoreSEO (record time)
4. Go to Appwise Reviews → Select StoreSEO (record time)
5. Go back to Analytics → Select StoreSEO (record time)

**Expected Results**:
- First load: 2-3 seconds
- Subsequent loads: <100ms (from cache)
- **Performance improvement**: 20-30x faster

---

## Console Log Reference

### Cache Hit
```
✅ Cache HIT for key: StoreSEO
```
Data was found in cache and is still valid.

### Cache Expired
```
⏰ Cache EXPIRED for key: StoreSEO
```
Cache entry was found but exceeded 30-minute duration.

### Cache Set
```
💾 Cache SET for key: StoreSEO
```
New data was stored in cache after API call.

### Loading from Cache
```
✅ Loading from global cache: access_reviews_StoreSEO_page1
```
Component is loading data from global cache.

### Cache Cleared
```
🗑️ Cleared cache for app: StoreSEO
```
Cache was manually cleared for specific app.

---

## Debugging Tips

### View Cache Statistics
Open browser console and run:
```javascript
// This would require exposing cache stats to window
// Currently available through component logs
```

### Monitor Network Requests
1. Open DevTools → Network tab
2. Filter by "XHR" (XMLHttpRequest)
3. When cache hits, no new requests should appear
4. When cache misses, API calls will appear

### Check Console Logs
1. Open DevTools → Console tab
2. Filter by "Cache" keyword
3. Watch for cache hit/miss patterns
4. Verify cache keys are correct

---

## Expected Behavior Summary

| Scenario | Before Cache | After Cache | Improvement |
|----------|-------------|------------|-------------|
| First app load | 2-3s | 2-3s | None (API call) |
| Switch to different app | 2-3s | 2-3s | None (API call) |
| Return to previous app | 2-3s | <100ms | **20-30x faster** |
| Cross-page navigation | 2-3s | <100ms | **20-30x faster** |
| Filter change | 2-3s | <100ms | **20-30x faster** |

---

## Troubleshooting

### Cache not working?
1. Check browser console for errors
2. Verify CacheProvider is wrapping the app in App.jsx
3. Ensure useCache() hook is imported correctly
4. Check that cache functions are being called

### Data not updating?
1. Cache expires after 30 minutes
2. Browser refresh clears all cache
3. Manual cache clear can be implemented
4. Check if API is returning new data

### Performance still slow?
1. Verify cache hits in console logs
2. Check Network tab for unexpected API calls
3. Ensure cache keys are consistent
4. Monitor browser memory usage

