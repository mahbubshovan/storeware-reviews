# First Page Review Monitoring System

## Overview

The First Page Review Monitoring System is an efficient replacement for the previous comprehensive scraping approach. Instead of scraping entire review datasets, this system monitors only the first page of each app's reviews to detect new reviews for the Access Review Tab functionality.

## Core Features

### ✅ **Efficient Monitoring**
- **First Page Only**: Scrapes only page 1 of each app's reviews (10 reviews per app)
- **New Review Detection**: Compares against existing `access_reviews` data to identify genuinely new reviews
- **Real-time Updates**: Adds only new reviews to the `access_reviews` table
- **Rate Limiting Friendly**: Minimal requests to avoid HTTP 429 errors

### ✅ **Accurate Metrics**
- **This Month Count**: Reviews from current month start to today
- **30 Days Count**: Reviews from last 30 days
- **Total Reviews Count**: All historical reviews from main `reviews` table
- **Combined Data**: Merges monitoring data with existing review data

### ✅ **All Apps Coverage**
- StoreSEO: `https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1`
- StoreFAQ: `https://apps.shopify.com/storefaq/reviews?sort_by=newest&page=1`
- EasyFlow: `https://apps.shopify.com/product-options-4/reviews?sort_by=newest&page=1`
- TrustSync: `https://apps.shopify.com/customer-review-app/reviews?sort_by=newest&page=1`
- Vitals: `https://apps.shopify.com/vitals/reviews?sort_by=newest&page=1`
- BetterDocs FAQ: `https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest&page=1`
- Vidify: `https://apps.shopify.com/vidify/reviews?sort_by=newest&page=1`

## Technical Implementation

### Backend Components

#### 1. **FirstPageMonitor Class** (`utils/FirstPageMonitor.php`)
- **Main monitoring logic**
- **Review extraction and comparison**
- **Database integration**
- **Statistics generation**

#### 2. **Monitoring Script** (`first_page_monitor.php`)
- **Command-line interface**
- **Runs monitoring for all apps**
- **Displays results and statistics**

#### 3. **Enhanced APIs**
- **`api/homepage-stats.php`**: Comprehensive statistics API
- **`api/monitoring-stats.php`**: Monitoring-specific statistics
- **`api/run-monitoring.php`**: On-demand monitoring trigger
- **`api/this-month-reviews.php`**: Enhanced with monitoring data
- **`api/last-30-days-reviews.php`**: Enhanced with monitoring data

### Frontend Components

#### 1. **MonitoringControls Component** (`components/MonitoringControls.jsx`)
- **Manual monitoring triggers**
- **Real-time results display**
- **App-specific and all-apps monitoring**
- **Integration with main dashboard**

#### 2. **Enhanced SummaryStats** (`components/SummaryStats.jsx`)
- **Uses new homepage-stats API**
- **Real-time monitoring data**
- **Improved performance**

### Database Schema

#### Enhanced Tables
```sql
-- Reviews table with new columns
ALTER TABLE reviews ADD COLUMN source_type VARCHAR(50) DEFAULT 'live_scrape';
ALTER TABLE reviews ADD COLUMN is_active BOOLEAN DEFAULT TRUE;

-- Access reviews table with store_name
ALTER TABLE access_reviews ADD COLUMN store_name VARCHAR(255);
```

## Usage

### Command Line
```bash
# Run monitoring for all apps
php first_page_monitor.php

# Check current statistics
php -r "require_once 'utils/FirstPageMonitor.php'; $m = new FirstPageMonitor(); print_r($m->getMonitoringStats());"
```

### API Endpoints

#### Get Statistics
```bash
# All apps statistics
curl http://localhost:8000/api/homepage-stats.php

# Specific app statistics
curl http://localhost:8000/api/homepage-stats.php?app_name=StoreSEO

# This month count
curl http://localhost:8000/api/this-month-reviews.php?app_name=StoreSEO

# Last 30 days count
curl http://localhost:8000/api/last-30-days-reviews.php?app_name=StoreSEO
```

#### Run Monitoring
```bash
# Monitor all apps
curl -X POST http://localhost:8000/api/run-monitoring.php

# Monitor specific app
curl -X POST http://localhost:8000/api/run-monitoring.php \
  -H "Content-Type: application/json" \
  -d '{"app_name": "StoreSEO"}'
```

### Frontend Integration

The MonitoringControls component is integrated into the main dashboard and provides:
- **Manual monitoring triggers**
- **Real-time feedback**
- **Statistics updates**
- **Error handling**

## Benefits

### 🚀 **Performance**
- **99% reduction** in scraping volume (10 reviews vs 1000+ reviews)
- **Faster execution** (seconds vs minutes)
- **Lower server load**
- **Reduced rate limiting issues**

### 🎯 **Accuracy**
- **Real-time detection** of new reviews
- **No duplicate entries**
- **Preserves existing assignments**
- **Accurate date-based filtering**

### 🔧 **Maintainability**
- **Simple, focused code**
- **Easy to debug and monitor**
- **Clear separation of concerns**
- **Comprehensive error handling**

## Current Statistics

Based on latest monitoring run:
- **Total Apps**: 7
- **This Month**: 18 reviews
- **Last 30 Days**: 74 reviews  
- **Total Reviews**: 1,160 reviews
- **Monitoring Efficiency**: 10 reviews checked per app vs 100+ previously

## Future Enhancements

1. **Automated Scheduling**: Cron job integration for regular monitoring
2. **Email Notifications**: Alert when new reviews are detected
3. **Advanced Filtering**: Category-based review filtering
4. **Performance Metrics**: Detailed monitoring performance tracking
5. **API Rate Limiting**: Built-in rate limiting and retry logic
