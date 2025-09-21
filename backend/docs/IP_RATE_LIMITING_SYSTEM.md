# IP-based Rate Limiting System

## Overview

This system implements comprehensive IP-based rate limiting for web scraping operations to prevent HTTP 429 errors and over-scraping. When data is scraped from a specific IP address, that IP is prevented from real-time scraping for the next 6 hours. During the cooldown period, cached data is returned instead.

## Key Features

- ✅ **6-hour IP-based cooldown periods**
- ✅ **Automatic cached data fallback during cooldown**
- ✅ **Enhanced error handling with exponential backoff**
- ✅ **Comprehensive activity logging and monitoring**
- ✅ **Transparent operation (no frontend changes needed)**
- ✅ **Automatic cleanup of old records**

## Database Schema

### `ip_scrape_limits` Table
Tracks scraping activity by IP address:
- `ip_address`: Client IP address (supports IPv4/IPv6)
- `last_scrape_timestamp`: When the last scrape occurred
- `cooldown_expiry`: When the cooldown period expires
- `scrape_count`: Number of scrapes from this IP
- `app_name`: Specific app being scraped (optional)

### `scrape_activity_log` Table
Comprehensive activity logging:
- `ip_address`: Client IP address
- `app_name`: App being scraped
- `action`: Type of action (scrape_allowed, scrape_blocked, rate_limit_applied, cooldown_expired)
- `message`: Detailed message about the action
- `timestamp`: When the action occurred

## Core Components

### 1. IPRateLimitManager (`backend/utils/IPRateLimitManager.php`)
Main rate limiting logic:
- `canScrape($appName, $ipAddress)`: Check if IP can scrape
- `recordScrape($appName, $ipAddress)`: Record scraping attempt and set cooldown
- `getRemainingCooldown($appName, $ipAddress)`: Get remaining cooldown time
- `getClientIP()`: Get real client IP (handles proxies/load balancers)

### 2. EnhancedUniversalScraper (`backend/scraper/EnhancedUniversalScraper.php`)
Enhanced scraper with rate limiting:
- Integrates with IPRateLimitManager
- Implements retry mechanisms with exponential backoff
- Returns cached data during cooldown periods
- Enhanced error handling for HTTP 429 and connection issues

### 3. API Endpoints

#### Rate Limiting APIs
- `GET /api/rate-limit-status.php?app=AppName`: Check rate limit status
- `GET /api/scrape-with-rate-limit.php?app=AppName`: Scrape with rate limiting
- `POST /api/scrape-app.php`: Main scraping endpoint (now uses rate limiting)

#### Monitoring APIs
- `GET /api/scrape-monitoring.php`: Comprehensive monitoring dashboard
- `GET /api/scrape-activity-log.php`: Activity log with filtering

## How It Works

### 1. First Scrape Request
```
Client IP: 192.168.1.100 → StoreSEO scrape request
✅ No previous record found
✅ Scraping allowed
✅ Fresh data scraped and returned
✅ 6-hour cooldown period set (expires at 21:30)
```

### 2. Subsequent Requests (During Cooldown)
```
Client IP: 192.168.1.100 → StoreSEO scrape request
❌ IP in cooldown (3h 45m remaining)
✅ Cached data returned instead
✅ No actual scraping performed
```

### 3. After Cooldown Expires
```
Client IP: 192.168.1.100 → StoreSEO scrape request
✅ Cooldown expired
✅ Fresh scraping allowed again
✅ New 6-hour cooldown period set
```

## Error Handling

### HTTP 429 Rate Limiting
- Exponential backoff (2s, 4s, 8s, 16s, max 60s)
- Maximum 3 retry attempts
- Automatic fallback to cached data

### Connection Failures
- Timeout handling (45s total, 15s connect)
- Random delays between requests (1-3s)
- Rotating user agents to avoid detection

### Fallback Strategy
1. Try recent reviews scraping
2. If fails, try historical scraping
3. If fails, return cached data from repository
4. If no cached data, return error with empty results

## Configuration

### Cooldown Period
Default: 6 hours (configurable in IPRateLimitManager)
```php
private $cooldownHours = 6; // Change this to adjust cooldown period
```

### Retry Settings
```php
private $maxRetries = 3;        // Maximum retry attempts
private $baseDelay = 2;         // Base delay in seconds
private $maxDelay = 30;         // Maximum delay in seconds
```

### Cleanup Settings
- Rate limit records: Removed after 7 days
- Activity logs: Removed after 30 days

## Monitoring

### Real-time Status
```bash
curl "http://localhost:8000/api/rate-limit-status.php?app=StoreSEO"
```

### Activity Monitoring
```bash
curl "http://localhost:8000/api/scrape-monitoring.php"
```

### Activity Logs
```bash
curl "http://localhost:8000/api/scrape-activity-log.php?limit=10"
```

## Maintenance

### Manual Cleanup
```bash
php backend/utils/cleanup-rate-limits.php
```

### Recommended Cron Job
```bash
# Run cleanup daily at 2 AM
0 2 * * * /usr/bin/php /path/to/backend/utils/cleanup-rate-limits.php
```

## Testing

### Test Rate Limiting
```bash
# First request - should scrape
curl -X POST -H "Content-Type: application/json" -d '{"app_name":"StoreSEO"}' "http://localhost:8000/api/scrape-app.php"

# Second request - should return cached data
curl -X POST -H "Content-Type: application/json" -d '{"app_name":"StoreSEO"}' "http://localhost:8000/api/scrape-app.php"
```

### Check Status
```bash
curl "http://localhost:8000/api/rate-limit-status.php?app=StoreSEO"
```

## Benefits

1. **Prevents HTTP 429 Errors**: Avoids rate limiting from Shopify
2. **Maintains Data Availability**: Always returns data (fresh or cached)
3. **Transparent Operation**: No frontend changes required
4. **Comprehensive Monitoring**: Full visibility into scraping activity
5. **Automatic Maintenance**: Self-cleaning with configurable retention
6. **Scalable Design**: Handles multiple IPs and apps independently

## Security Considerations

- IP detection handles proxies and load balancers
- Validates IP addresses to prevent injection
- Logs all activity for audit trails
- Automatic cleanup prevents database bloat
- Rate limiting per app prevents cross-app abuse

This system ensures reliable, sustainable web scraping while maintaining excellent user experience through intelligent caching and fallback mechanisms.
