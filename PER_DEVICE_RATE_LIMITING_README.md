# Per-Device Rate Limiting System for Shopify Reviews

This system implements sophisticated per-device rate limiting for Shopify app review scraping, ensuring each browser/device can only scrape once every 6 hours while serving cached snapshots between scrapes.

## 🎯 Key Features

- **Per-Device Rate Limiting**: 6-hour cooldown per browser/device (not global)
- **Client Identification**: Stable UUID stored in localStorage with cookie fallback
- **Snapshot System**: Immutable snapshots with content hash change detection
- **Upstream Change Detection**: Detects when new reviews are available upstream
- **Background Jobs**: Optional automated scraping and cleanup
- **Real-time UI**: Countdown timers, refresh buttons, and status indicators

## 🏗️ Architecture Overview

### Database Schema
- `clients`: Track unique client devices
- `scrape_schedule`: Per-device 6-hour rate limiting
- `snapshots`: Immutable scraped data snapshots
- `snapshot_pointer`: Per-device pointers to current snapshot
- `upstream_state`: Track upstream changes for change detection

### API Endpoints
- `GET /api/reviews/:app?client_id={uuid}` - Get cached data with rate limit status
- `POST /api/scrape/:app/trigger` - Trigger scraping (rate limited)
- `GET /api/scrape/:app/status?client_id={uuid}` - Get scraping job status

### Frontend Components
- `useClientId` hook - Manages device identification
- `useScrapeStatus` hook - Handles rate limiting state
- `RefreshButton` component - Smart refresh with countdown timer

## 🚀 Installation & Setup

### 1. Database Migration

Run the migration to add new tables:

```bash
# Apply migration to existing database
mysql -u username -p shopify_reviews < backend/database/migration_per_device_rate_limiting.sql

# Or for new installations, use the complete schema
mysql -u username -p shopify_reviews < shopify_reviews_database_complete.sql
```

### 2. Backend Setup

The new API endpoints are ready to use:
- `backend/api/reviews-v2.php` - Enhanced reviews endpoint
- `backend/api/scrape-trigger.php` - Rate-limited scraping
- `backend/api/scrape-status.php` - Status checking

### 3. Frontend Integration

The React components are already integrated into the main App.jsx:

```jsx
import { useClientId } from './hooks/useClientId';
import { useScrapeStatus } from './hooks/useScrapeStatus';
import RefreshButton from './components/RefreshButton';

// Usage in component
const { clientId } = useClientId();
const { canScrapeNow, countdown } = useScrapeStatus(appSlug);
```

### 4. Background Jobs (Optional)

Set up automated background scraping:

```bash
# Make setup script executable
chmod +x backend/cron/setup_cron.sh

# Run setup (will add cron jobs)
./backend/cron/setup_cron.sh

# Manual cron setup alternative:
# Add to crontab: */5 * * * * /usr/bin/php /path/to/backend/cron/background_scraper.php
```

## 🧪 Testing

### Run Unit Tests

```bash
# Test content hashing
php backend/tests/ContentHashTest.php

# Expected output: All content hash tests passed!
```

### Run E2E Tests

```bash
# Test complete rate limiting flow
php backend/tests/RateLimitingE2ETest.php

# Expected output: All E2E tests passed!
```

### Manual Testing Flow

1. **First Visit**: Open app page → should allow immediate refresh
2. **After Scrape**: Click refresh → should show 6-hour countdown
3. **Rate Limited**: Try refresh again → should show "Rate limited" message
4. **Different Device**: Open in incognito/different browser → should allow immediate refresh
5. **Upstream Changes**: When new reviews appear, should show "New reviews available" indicator

## 🔧 Configuration

### Environment Variables

Add to your `.env` or server configuration:

```bash
# Rate limiting settings
SCRAPE_RATE_LIMIT_HOURS=6
BACKGROUND_SCRAPING_ENABLED=true
CLEANUP_RETENTION_DAYS=30

# Performance settings
MAX_SNAPSHOTS_PER_APP=20
SCRAPE_TIMEOUT_SECONDS=300
```

### App Slug Mapping

The system supports these apps by default:

```php
$appSlugs = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq', 
    'Vidify' => 'vidify',
    'TrustSync' => 'customer-review-app',
    'EasyFlow' => 'product-options-4',
    'BetterDocs FAQ' => 'betterdocs-knowledgebase'
];
```

## 📊 Monitoring & Maintenance

### Health Checks

```bash
# Run health check
php backend/cron/health_check.php

# View logs
tail -f logs/health.log
tail -f logs/background_scraper.log
```

### Database Maintenance

The system automatically:
- Keeps latest 20 snapshots per app
- Cleans up orphaned records
- Removes inactive clients (30+ days)

### Performance Monitoring

Key metrics to monitor:
- Average scrape time per app (target: <5 seconds)
- Database size growth
- Client activity patterns
- Rate limiting effectiveness

## 🔍 Troubleshooting

### Common Issues

**"Client ID not generating"**
- Check browser localStorage support
- Verify cookie fallback is working
- Check console for JavaScript errors

**"Rate limiting not working"**
- Verify database migration completed
- Check client_id is being sent with requests
- Confirm scrape_schedule table has data

**"Snapshots not updating"**
- Check enhanced scraper is being used
- Verify content hash generation
- Check upstream_state table updates

**"Background jobs not running"**
- Verify cron jobs are installed: `crontab -l`
- Check log files for errors
- Ensure PHP CLI path is correct

### Debug Mode

Enable debug logging:

```php
// Add to config/database.php
define('DEBUG_RATE_LIMITING', true);

// Check logs
tail -f logs/debug.log
```

## 🚀 Deployment

### Railway Deployment

The system is compatible with Railway. Ensure:

1. Database migration runs on deployment
2. Cron jobs are configured (if using background scraping)
3. Log directory is writable
4. Environment variables are set

### Production Checklist

- [ ] Database migration completed
- [ ] API endpoints responding correctly
- [ ] Frontend client ID generation working
- [ ] Rate limiting enforced (test with multiple requests)
- [ ] Background jobs configured (optional)
- [ ] Health checks passing
- [ ] Logs rotating properly
- [ ] Performance monitoring in place

## 📈 Performance Expectations

- **First scrape**: Immediate (0 seconds wait)
- **Subsequent scrapes**: 6-hour cooldown
- **Scrape duration**: 2-5 seconds per app
- **Database growth**: ~50MB per month (estimated)
- **Memory usage**: <512MB during scraping
- **Concurrent clients**: Supports 1000+ simultaneous users

## 🔒 Security Considerations

- Client IDs are UUIDs (not personally identifiable)
- Rate limiting prevents abuse
- No sensitive data stored in client-side storage
- SQL injection protection via prepared statements
- Input validation on all endpoints

## 📝 API Documentation

### GET /api/reviews/:app

Returns cached snapshot data with rate limiting metadata.

**Parameters:**
- `client_id` (required): UUID v4 client identifier

**Response:**
```json
{
  "app": "storeseo",
  "status": "ok", 
  "data": { /* snapshot data */ },
  "scrape": {
    "allowed_now": false,
    "next_run_at": "2024-01-15T14:30:00Z",
    "remaining_seconds": 18000,
    "has_upstream_changes": true
  }
}
```

### POST /api/scrape/:app/trigger

Triggers rate-limited scraping for the specified app.

**Body:**
```json
{
  "client_id": "uuid-v4-string"
}
```

**Response (Success):**
```json
{
  "success": true,
  "scraped_count": 150,
  "snapshot_id": 12345,
  "next_scrape_allowed_at": "2024-01-15T20:30:00Z"
}
```

**Response (Rate Limited):**
```json
{
  "success": false,
  "error": "Rate limited",
  "remaining_seconds": 18000
}
```

This completes the comprehensive per-device rate limiting system implementation!
