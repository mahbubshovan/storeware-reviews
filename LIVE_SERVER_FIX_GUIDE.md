# 🚀 Live Server Data Storage Fix Guide

## 🔍 Problem Identified
Your Access Reviews tab shows "Cached" data instead of live data because:
1. Database configuration wasn't properly handling Railway's environment variables
2. Hardcoded localhost URLs in API calls
3. Missing environment variable mapping in Railway configuration

## ✅ Fixes Applied

### 1. **Enhanced Database Configuration** (`backend/config/database.php`)
- Added support for Railway's `MYSQL_*` environment variables
- Enhanced error logging for debugging live server issues
- Added connection timeout and better error handling
- Priority order: Railway MYSQL_* > System ENV > .env file > defaults

### 2. **Fixed API Internal Calls** (`backend/api/access-reviews-tabbed.php`)
- Removed hardcoded `localhost:8000` URLs
- Added dynamic URL detection for Railway vs local environment
- Enhanced cURL error handling for HTTPS connections

### 3. **Updated Railway Configuration** (`railway.toml`)
- Added proper `MYSQL_*` environment variable mapping
- Included backward compatibility variables
- Ensured all database credentials are properly set

### 4. **Added Diagnostic Tools**
- `backend/api/debug-health.php` - Comprehensive health check
- `backend/api/test-data-insert.php` - Test data insertion capability
- `backend/setup/init-database.php` - Database initialization script

## 🧪 Testing Your Live Server

After pushing these changes to GitHub and deploying to Railway:

### Step 1: Check Database Connection
Visit: `https://your-railway-app.railway.app/backend/api/debug-health.php`

This will show:
- Environment variables status
- Database connection status
- Table existence and counts
- Write permission test

### Step 2: Test Data Insertion
Visit: `https://your-railway-app.railway.app/backend/api/test-data-insert.php`

- GET request: Shows current data
- POST request: Inserts test data

### Step 3: Verify Access Reviews
Visit your Access Reviews tab and check:
- Data should show "Live" instead of "Cached"
- Numbers should match between different views
- New data should be stored properly

## 🔧 Manual Database Setup (If Needed)

If tables are missing on Railway, run:

```bash
# Connect to Railway MySQL
railway connect MySQL

# Then run:
mysql> SOURCE /path/to/shopify_reviews_database_complete.sql;
```

Or use the initialization script:
```bash
php backend/setup/init-database.php
```

## 🚨 Troubleshooting

### If Still Showing "Cached" Data:
1. Check `debug-health.php` for database connection issues
2. Verify environment variables are set in Railway dashboard
3. Check Railway logs for database connection errors

### If Data Not Inserting:
1. Test with `test-data-insert.php` endpoint
2. Check Railway MySQL service is running
3. Verify database credentials in Railway dashboard

### If Environment Variables Not Working:
1. Go to Railway project dashboard
2. Check MySQL service variables are auto-configured
3. Manually set variables if needed:
   - `MYSQL_HOST`
   - `MYSQL_DATABASE` 
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_PORT`

## 📋 Deployment Checklist

- [x] Updated `backend/config/database.php` with Railway support
- [x] Fixed hardcoded URLs in `backend/api/access-reviews-tabbed.php`
- [x] Updated `railway.toml` with proper environment variables
- [x] Added diagnostic endpoints for debugging
- [x] Created database initialization script

## 🎯 Expected Results

After deployment:
1. **Access Reviews tab** will show "Live" data instead of "Cached"
2. **Data consistency** between localhost and live server
3. **Proper data storage** - new reviews will be saved to database
4. **Real-time updates** - changes will persist across sessions

## 🔗 Quick Test URLs

Replace `your-app` with your actual Railway app name:

- Health Check: `https://your-app.railway.app/backend/api/debug-health.php`
- Data Test: `https://your-app.railway.app/backend/api/test-data-insert.php`
- Regular Health: `https://your-app.railway.app/backend/api/health.php`

Push these changes to GitHub and redeploy to Railway. The Access Reviews tab should now work properly with live data storage! 🎉
