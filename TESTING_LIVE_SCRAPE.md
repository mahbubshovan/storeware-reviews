# Testing Live Scrape Feature

## Quick Start Testing

### Test 1: Basic Live Scrape
**Objective**: Verify that Live Scrape button works and fetches data

**Steps**:
1. Open http://localhost:5173 in browser
2. Go to **Analytics** page
3. Select **"StoreSEO"** from dropdown
4. Click the green **"🌐 Live Scrape"** button
5. Wait for scraping to complete (2-5 seconds)

**Expected Results**:
- ✅ Button shows "⟳ Scraping..." while loading
- ✅ Data updates with live information
- ✅ Success message appears: "✅ Live scrape completed! Found X reviews with Y rating"
- ✅ Message auto-dismisses after 5 seconds
- ✅ Browser console shows: "✅ Live scrape successful: X reviews, rating: Y"

---

### Test 2: Verify Data Accuracy
**Objective**: Confirm scraped data matches Shopify page

**Steps**:
1. Click "Live Scrape" for StoreSEO
2. Note the total review count and rating displayed
3. Open new tab and go to: https://apps.shopify.com/storeseo/reviews
4. Compare the numbers

**Expected Results**:
- ✅ Total review count matches Shopify page
- ✅ Average rating matches Shopify page
- ✅ Rating distribution is accurate
- ✅ Latest reviews match what's shown on Shopify

---

### Test 3: Multiple Apps
**Objective**: Test Live Scrape with different apps

**Steps**:
1. Select **"StoreSEO"** → Click Live Scrape → Wait for completion
2. Select **"StoreFAQ"** → Click Live Scrape → Wait for completion
3. Select **"EasyFlow"** → Click Live Scrape → Wait for completion
4. Select **"BetterDocs FAQ Knowledge Base"** → Click Live Scrape → Wait for completion
5. Select **"Vidify"** → Click Live Scrape → Wait for completion
6. Select **"TrustSync"** → Click Live Scrape → Wait for completion

**Expected Results**:
- ✅ All apps scrape successfully
- ✅ Each app shows correct data
- ✅ No errors for any app
- ✅ Data is different for each app

---

### Test 4: Button States
**Objective**: Verify button behavior in different states

**Steps**:
1. **No app selected**: Button should be disabled (grayed out)
2. **App selected**: Button should be enabled (green)
3. **During scraping**: Button should show "⟳ Scraping..." and be disabled
4. **After scraping**: Button should return to "🌐 Live Scrape" and be enabled

**Expected Results**:
- ✅ Button is disabled when no app selected
- ✅ Button is enabled when app is selected
- ✅ Button shows loading spinner during scrape
- ✅ Button is disabled during scraping
- ✅ Button returns to normal after scraping

---

### Test 5: Error Handling
**Objective**: Test error scenarios

**Steps**:
1. Disconnect internet (or use browser dev tools to throttle network)
2. Click "Live Scrape"
3. Wait for error message

**Expected Results**:
- ✅ Error message appears: "❌ Network error: ..."
- ✅ Error message is displayed in red
- ✅ Button returns to enabled state
- ✅ Browser console shows error details

---

### Test 6: Message Auto-Dismiss
**Objective**: Verify success/error messages auto-dismiss

**Steps**:
1. Click "Live Scrape"
2. Wait for success message to appear
3. Wait 5 seconds without clicking anything

**Expected Results**:
- ✅ Success message appears
- ✅ Message automatically disappears after ~5 seconds
- ✅ No manual action needed to dismiss

---

### Test 7: Rapid Clicking
**Objective**: Test behavior with rapid clicks

**Steps**:
1. Select an app
2. Click "Live Scrape" multiple times rapidly
3. Observe behavior

**Expected Results**:
- ✅ Only one scrape request is sent
- ✅ Button remains disabled during scraping
- ✅ No duplicate requests
- ✅ No errors or crashes

---

### Test 8: Data Persistence
**Objective**: Verify data persists after scraping

**Steps**:
1. Click "Live Scrape" for StoreSEO
2. Wait for completion
3. Navigate to another page (Access Reviews)
4. Navigate back to Analytics
5. Select StoreSEO again

**Expected Results**:
- ✅ Data is still displayed
- ✅ No automatic re-scraping
- ✅ Data loads from cache (fast)
- ✅ Can click Live Scrape again to refresh

---

### Test 9: Console Logging
**Objective**: Verify console logs are helpful

**Steps**:
1. Open browser DevTools (F12)
2. Go to Console tab
3. Click "Live Scrape"
4. Watch console output

**Expected Results**:
- ✅ Console shows: "🌐 Live scraping: StoreSEO from ..."
- ✅ Console shows: "✅ Live scrape successful: X reviews, rating: Y"
- ✅ Logs are clear and helpful for debugging

---

### Test 10: Responsive Design
**Objective**: Test button appearance on mobile

**Steps**:
1. Open DevTools (F12)
2. Toggle device toolbar (mobile view)
3. Resize to different screen sizes
4. Verify button appearance

**Expected Results**:
- ✅ Button is visible on mobile
- ✅ Button is properly sized
- ✅ Button is clickable
- ✅ Layout doesn't break
- ✅ Text is readable

---

## Performance Testing

### Scraping Time Measurement
1. Open DevTools → Network tab
2. Click "Live Scrape"
3. Note the time for `/backend/api/live-scrape.php` request
4. Typical time: 2-5 seconds

### Data Size
- Check Network tab for response size
- Typical response: 5-20 KB

### Memory Usage
- Open DevTools → Memory tab
- Take heap snapshot before scraping
- Take heap snapshot after scraping
- Compare memory usage

---

## Browser Compatibility Testing

Test on:
- ✅ Chrome/Chromium
- ✅ Firefox
- ✅ Safari
- ✅ Edge

**Expected Results**:
- Button works on all browsers
- Styling is consistent
- No console errors

---

## Comparison Testing

### Live Scrape vs Cached Data
1. Click "Live Scrape" for StoreSEO
2. Note the data displayed
3. Switch to another app
4. Switch back to StoreSEO
5. Compare data (should be same)

**Expected Results**:
- ✅ Live scrape data matches cached data
- ✅ No discrepancies
- ✅ Ratings are consistent

---

## Troubleshooting Checklist

If tests fail, check:
- [ ] Backend API endpoint exists: `/backend/api/live-scrape.php`
- [ ] Frontend component updated: `src/components/Analytics.jsx`
- [ ] CSS file updated: `src/components/Analytics.css`
- [ ] Dev server is running: `npm run dev`
- [ ] Browser cache is cleared
- [ ] Network requests are not blocked
- [ ] Shopify website is accessible
- [ ] No rate limiting from Shopify

---

## Success Criteria

All tests should pass:
- ✅ Button appears on Analytics page
- ✅ Button is clickable
- ✅ Scraping completes successfully
- ✅ Data is accurate
- ✅ Messages display correctly
- ✅ Error handling works
- ✅ No console errors
- ✅ Performance is acceptable

