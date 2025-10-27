# Live Scrape - Quick Reference Guide

## 🚀 Quick Start (30 seconds)

1. **Go to Analytics page** → http://localhost:5173
2. **Select an app** from dropdown (e.g., "StoreSEO")
3. **Click "🌐 Live Scrape"** button (green button next to dropdown)
4. **Wait 2-5 seconds** for scraping to complete
5. **See results** - Data updates with live information from Shopify

## 📍 Button Location

**Analytics Page Header**:
```
┌─────────────────────────────────────────────────────────┐
│ 📊 Analytics Dashboard                                  │
│ Real-time insights from Shopify app reviews             │
│                                                         │
│ App Selector: [StoreSEO ▼] [🌐 Live Scrape]           │
└─────────────────────────────────────────────────────────┘
```

## 🎯 What It Does

| Action | Result |
|--------|--------|
| Click Live Scrape | Fetches real-time data from Shopify |
| Button shows "⟳ Scraping..." | Scraping is in progress |
| Success message appears | Data updated successfully |
| Message auto-dismisses | After 5 seconds |

## 📊 Data Displayed

After Live Scrape completes, you'll see:
- ✅ **Total Reviews**: Exact count from Shopify page
- ✅ **Average Rating**: Current rating (e.g., 4.8/5)
- ✅ **Rating Distribution**: Breakdown of star ratings
- ✅ **Latest Reviews**: Recent reviews with details

## ⚡ Performance

| Metric | Time |
|--------|------|
| First Click | 2-5 seconds |
| Subsequent Clicks | 2-5 seconds each |
| Message Display | Instant |
| Auto-Dismiss | 5 seconds |

## 🔧 Supported Apps

1. StoreSEO
2. StoreFAQ
3. EasyFlow
4. BetterDocs FAQ Knowledge Base
5. Vidify
6. TrustSync

## ❌ Troubleshooting

| Problem | Solution |
|---------|----------|
| Button is grayed out | Select an app first |
| Scraping takes too long | Wait or try again later |
| Error message appears | Check internet connection |
| Data doesn't update | Check browser console for errors |

## 🔍 Verify Results

**Compare with Shopify**:
1. Click "Live Scrape" for StoreSEO
2. Note the review count and rating
3. Open: https://apps.shopify.com/storeseo/reviews
4. Compare numbers - should match exactly

## 📱 Mobile Support

- ✅ Button works on mobile
- ✅ Responsive design
- ✅ Touch-friendly

## 🐛 Debug Mode

**Check Browser Console** (F12):
- Look for: `🌐 Live scraping: StoreSEO from ...`
- Success: `✅ Live scrape successful: X reviews, rating: Y`
- Error: `❌ Live scrape error: ...`

## 💡 Tips

1. **Use Live Scrape for verification** - When you need exact current data
2. **Use cached data for browsing** - For quick navigation
3. **Wait between scrapes** - Avoid rate limiting (5-10 seconds)
4. **Check console logs** - For debugging issues

## 🔄 Live Scrape vs Cached Data

| Feature | Live Scrape | Cached |
|---------|------------|--------|
| Freshness | Real-time | Up to 30 min old |
| Speed | 2-5 sec | <100ms |
| Accuracy | 100% current | May be outdated |
| Best for | Verification | Quick browsing |

## 📋 Button States

```
Disabled (No app selected):
[🌐 Live Scrape] (grayed out)

Enabled (App selected):
[🌐 Live Scrape] (green, clickable)

Loading (Scraping in progress):
[⟳ Scraping...] (green, disabled)

After Scraping:
[🌐 Live Scrape] (green, clickable)
```

## 🎨 Visual Feedback

**Success Message** (Green):
```
✅ Live scrape completed! Found 526 reviews with 4.8 rating.
```

**Error Message** (Red):
```
❌ Error: Failed to fetch page from Shopify
```

## 🔗 Related Documentation

- **Full Feature Guide**: `LIVE_SCRAPE_FEATURE.md`
- **Testing Guide**: `TESTING_LIVE_SCRAPE.md`
- **Implementation Details**: `LIVE_SCRAPE_IMPLEMENTATION_SUMMARY.md`

## 📞 Support

**If something doesn't work**:
1. Check browser console (F12)
2. Verify internet connection
3. Try again after 5-10 seconds
4. Clear browser cache if needed
5. Check documentation files

## ✨ Key Features

- 🌐 Real-time data from Shopify
- ⚡ Fast scraping (2-5 seconds)
- 📊 Accurate data extraction
- 🎯 All 6 apps supported
- 🔄 No caching - always fresh
- 📱 Mobile responsive
- 🛡️ Error handling
- 📝 Console logging

## 🎯 Common Use Cases

1. **Verify current ratings** - Before making decisions
2. **Check latest reviews** - See what customers are saying
3. **Monitor review count** - Track growth
4. **Compare with competitors** - Get accurate data
5. **Troubleshoot data** - Verify accuracy

## 🚀 Getting Started

```
1. Open Analytics page
2. Select app
3. Click Live Scrape
4. Wait for completion
5. View results
```

That's it! 🎉

