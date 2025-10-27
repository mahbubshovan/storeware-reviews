# Live Scrape Feature Documentation

## Overview
The "Live Scrape" button on the Analytics page allows users to fetch real-time data directly from the Shopify app store review pages, bypassing any cached data and ensuring the most current information is displayed.

## Features

### 1. Real-Time Data Fetching
- Scrapes live data directly from `apps.shopify.com/{app-slug}/reviews`
- Fetches exact data currently visible on the Shopify app store
- No cached data - always fresh information
- Works for all 6 supported apps

### 2. Data Extracted
The Live Scrape feature extracts and displays:
- **Total Review Count**: Exact number of reviews on the Shopify page
- **Average Rating**: Overall rating displayed on the Shopify page
- **Rating Distribution**: Breakdown of 5-star, 4-star, 3-star, 2-star, 1-star reviews
- **Latest Reviews**: Recent reviews with:
  - Reviewer name
  - Star rating
  - Review date
  - Review title
  - Review text

### 3. User Experience
- **Loading Indicator**: Shows "⟳ Scraping..." while fetching data
- **Success Message**: Displays confirmation with review count and rating
- **Error Handling**: Shows clear error messages if scraping fails
- **Auto-Clear Messages**: Success/error messages auto-dismiss after 5 seconds
- **Disabled State**: Button is disabled when no app is selected or while scraping

## How to Use

### Step 1: Select an App
1. Go to the Analytics page
2. Select an app from the dropdown (StoreSEO, StoreFAQ, EasyFlow, etc.)

### Step 2: Click Live Scrape
1. Click the green "🌐 Live Scrape" button next to the app dropdown
2. The button will show "⟳ Scraping..." while fetching data

### Step 3: View Results
1. Wait for the scraping to complete (typically 2-5 seconds)
2. The Analytics page will update with the live data
3. A success message will appear showing the total reviews and rating
4. The message will auto-dismiss after 5 seconds

## Technical Implementation

### Backend Endpoint
**File**: `backend/api/live-scrape.php`

**Request**:
```
GET /backend/api/live-scrape.php?app=StoreSEO
```

**Response**:
```json
{
  "success": true,
  "data": {
    "app_name": "StoreSEO",
    "total_reviews": 526,
    "average_rating": 4.8,
    "rating_distribution": {
      "5": 450,
      "4": 50,
      "3": 15,
      "2": 8,
      "1": 3
    },
    "latest_reviews": [
      {
        "reviewer_name": "John Doe",
        "rating": 5,
        "date": "2024-10-26",
        "title": "Great app!",
        "text": "This app is amazing..."
      }
    ],
    "data_source": "live_scrape",
    "scraped_at": "2024-10-26 16:22:30"
  }
}
```

### Frontend Component
**File**: `src/components/Analytics.jsx`

**Key Functions**:
- `performLiveScrape()` - Initiates the scraping process
- Updates `analyticsData` with scraped results
- Updates `latestReviews` with latest reviews
- Manages loading and message states

### Scraping Logic
The scraper uses:
- **cURL** for HTTP requests with proper headers
- **DOMDocument** for HTML parsing
- **XPath** for element selection
- **Regex** for data extraction

### Supported Apps
1. StoreSEO
2. StoreFAQ
3. EasyFlow
4. BetterDocs FAQ Knowledge Base
5. Vidify
6. TrustSync

## Error Handling

### Common Errors

**"Failed to fetch page from Shopify"**
- Shopify server is unreachable
- Network connectivity issue
- Possible rate limiting (HTTP 429)

**"App not found"**
- App name is not in the supported list
- Check app name spelling

**"Network error"**
- Frontend network issue
- Backend server is down

### Recovery
- Click the "Live Scrape" button again to retry
- Check browser console for detailed error logs
- Verify internet connection

## Performance Considerations

### Scraping Time
- Typical scraping time: 2-5 seconds
- Depends on Shopify server response time
- Network latency affects duration

### Rate Limiting
- Shopify may rate limit excessive requests
- Recommended: Wait 5-10 seconds between scrapes
- If rate limited, use cached data instead

### Data Freshness
- Live scrape always fetches current data
- No caching of live scrape results
- Each click performs a fresh scrape

## Browser Console Logs

When scraping, check the browser console for:

```
🌐 Live scraping: StoreSEO from https://apps.shopify.com/storeseo/reviews?page=1
✅ Live scrape successful: 526 reviews, rating: 4.8
```

Or on error:
```
❌ Live scrape error: Failed to fetch page from Shopify
```

## Comparison: Live Scrape vs Cached Data

| Feature | Live Scrape | Cached Data |
|---------|------------|------------|
| Data Freshness | Real-time | Up to 30 minutes old |
| Speed | 2-5 seconds | <100ms |
| Accuracy | 100% current | May be outdated |
| Rate Limiting | Possible | None |
| Use Case | Verification | Quick browsing |

## Best Practices

1. **Use Live Scrape for Verification**
   - When you need to verify exact current data
   - Before making important decisions
   - To compare with cached data

2. **Use Cached Data for Browsing**
   - For quick navigation between apps
   - When exact real-time data isn't critical
   - To avoid rate limiting

3. **Respect Rate Limits**
   - Don't click Live Scrape repeatedly
   - Wait a few seconds between scrapes
   - Use cached data when possible

4. **Monitor Console Logs**
   - Check browser console for scraping status
   - Look for error messages
   - Verify data extraction success

## Troubleshooting

### Button is Disabled
- **Cause**: No app selected
- **Solution**: Select an app from the dropdown

### Scraping Takes Too Long
- **Cause**: Slow network or Shopify server
- **Solution**: Wait or try again later

### Error Message Appears
- **Cause**: Network issue or rate limiting
- **Solution**: Wait a few seconds and try again

### Data Doesn't Update
- **Cause**: Scraping failed silently
- **Solution**: Check browser console for errors

## Future Enhancements

1. **Batch Scraping**: Scrape all apps at once
2. **Scheduled Scraping**: Auto-scrape at intervals
3. **Data Comparison**: Show diff between live and cached
4. **Export Results**: Download scraped data as CSV/JSON
5. **Scraping History**: Track scraping activity and results

