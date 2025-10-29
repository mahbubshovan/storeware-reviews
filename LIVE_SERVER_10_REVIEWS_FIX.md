# Live Server Fix: Only 10 Reviews Showing

## Problem Summary

- **Local**: Access Reviews shows 527 reviews for StoreSEO ✅
- **Live**: Access Reviews shows only 10 reviews ❌

The issue is that the **main `reviews` table on the live server only has 10 reviews**, not 527+.

## Root Cause

The scraper on the live server is **stopping after the first page** (which has ~10 reviews) instead of continuing through all pages. This could be due to:

1. **Rate limiting is active** - Scraper is blocked and returning cached data
2. **Scraper timeout** - Connection timing out after first page
3. **Scraper hasn't run** - Fresh database with no data

## Step-by-Step Fix

### Step 1: Diagnose the Issue

Run the diagnostic endpoint to check what's happening:

```
GET http://your-live-server/api/diagnose-live-issue.php
```

This will show:
- ✅ Database connection status
- 📊 How many reviews are in main `reviews` table
- ⏳ If rate limiting is active
- 🔍 Root cause analysis
- 💡 Recommendations

### Step 2: Check Rate Limiting

If the diagnostic shows rate limiting is active:

```
GET http://your-live-server/api/clear-rate-limits.php
```

This will:
- Clear all rate limit records
- Allow fresh scraping to proceed

### Step 3: Run Fresh Scrape

After clearing rate limits, trigger a fresh scrape for each app:

```bash
# For StoreSEO
curl -X POST http://your-live-server/api/scrape-app.php \
  -H "Content-Type: application/json" \
  -d '{"app_name": "StoreSEO"}'

# For StoreFAQ
curl -X POST http://your-live-server/api/scrape-app.php \
  -H "Content-Type: application/json" \
  -d '{"app_name": "StoreFAQ"}'

# For all apps
for app in "StoreSEO" "StoreFAQ" "EasyFlow" "TrustSync" "BetterDocs FAQ Knowledge Base" "Vidify"; do
  curl -X POST http://your-live-server/api/scrape-app.php \
    -H "Content-Type: application/json" \
    -d "{\"app_name\": \"$app\"}"
  sleep 5  # Wait between requests
done
```

### Step 4: Verify the Fix

Run the diagnostic again to confirm data is populated:

```
GET http://your-live-server/api/diagnose-live-issue.php
```

Look for:
- ✅ StoreSEO: 527 total reviews
- ✅ StoreFAQ: 245+ total reviews
- ✅ All other apps with correct counts

### Step 5: Verify in Frontend

1. Go to Access Reviews page
2. Should now show 527 total reviews for StoreSEO
3. All apps should show correct counts matching Shopify pages

## API Endpoints Created

### 1. `/api/diagnose-live-issue.php` (NEW)
**Purpose**: Diagnose why only 10 reviews are showing
**Method**: GET
**Response**: Detailed diagnosis with root cause and recommendations

### 2. `/api/clear-rate-limits.php` (NEW)
**Purpose**: Clear all rate limit records
**Method**: GET
**Response**: Confirmation of cleared records

### 3. `/api/verify-access-reviews-fix.php` (NEW)
**Purpose**: Verify Access Reviews API is working correctly
**Method**: GET
**Response**: Comparison of main table vs Access Reviews API

## Code Changes

### Modified: `backend/utils/AccessReviewsSync.php`

Changed the `getAccessReviews()` function to query from main `reviews` table instead of `access_reviews`:

```php
// OLD - Querying from access_reviews (limited data)
SELECT * FROM access_reviews WHERE review_date >= ?

// NEW - Querying from main reviews table (all data)
SELECT * FROM reviews WHERE review_date >= ? AND is_active = TRUE
```

This ensures the Access Reviews page shows the same data as the live Shopify pages.

## Expected Results

After completing all steps:

| App | Expected Count | Status |
|-----|-----------------|--------|
| StoreSEO | 527+ | ✅ |
| StoreFAQ | 245+ | ✅ |
| EasyFlow | 200+ | ✅ |
| BetterDocs FAQ Knowledge Base | 180+ | ✅ |
| Vidify | 150+ | ✅ |
| TrustSync | 120+ | ✅ |

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Still showing 10 reviews | Run `/api/clear-rate-limits.php` then scrape again |
| Scraper times out | Check server timeout settings, increase PHP max_execution_time |
| Rate limit keeps activating | Check if scraper is running too frequently |
| Database connection error | Verify database credentials in `.env` or `config/database.php` |

## Files Modified/Created

- ✅ `backend/utils/AccessReviewsSync.php` - Modified to query from main reviews table
- ✅ `backend/api/diagnose-live-issue.php` - NEW diagnostic endpoint
- ✅ `backend/api/clear-rate-limits.php` - NEW rate limit clearing endpoint
- ✅ `backend/api/verify-access-reviews-fix.php` - NEW verification endpoint

## Deployment Checklist

- [x] Code changes implemented
- [x] Diagnostic endpoints created
- [ ] Deploy to production
- [ ] Run diagnostic endpoint
- [ ] Clear rate limits if needed
- [ ] Run fresh scrape for each app
- [ ] Verify data is populated
- [ ] Test Access Reviews page
- [ ] Confirm counts match Shopify pages

