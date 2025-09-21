# Single Domain Deployment Guide

## Overview

The project has been restructured to serve both frontend and backend from a single domain. This eliminates the need for separate hosting or port configurations.

## Project Structure

```
shopify-reviews/
├── index.html              # Frontend entry point
├── assets/                 # Built frontend assets (CSS, JS)
├── backend/                # Backend API and PHP files
│   ├── api/               # API endpoints
│   ├── config/            # Configuration files
│   └── ...                # Other backend files
├── src/                   # Frontend source code (for development)
├── router.php             # PHP router for single domain deployment
├── .htaccess              # Apache configuration
├── package.json           # Frontend dependencies
├── vite.config.js         # Frontend build configuration
└── railway.toml           # Railway deployment configuration
```

## How It Works

### Frontend Access
- **URL**: `https://yourdomain.com/`
- **Serves**: React application from `index.html`
- **Assets**: CSS, JS, and other static files from `/assets/`

### Backend API Access
- **URL**: `https://yourdomain.com/backend/api/`
- **Example**: `https://yourdomain.com/backend/api/available-apps.php`
- **Serves**: PHP API endpoints from `backend/api/` directory

### Routing
- **Development**: Vite dev server with proxy configuration
- **Production**: PHP router (`router.php`) handles all requests
- **Apache**: `.htaccess` file provides URL rewriting

## Deployment Options

### 1. Railway Deployment

The project is configured for Railway deployment:

```bash
# Deploy to Railway
railway up
```

Configuration in `railway.toml`:
- Serves from root directory using `router.php`
- Health check endpoint: `/backend/api/available-apps.php`
- Environment variables for database connection

### 2. Apache/cPanel Hosting

1. Upload all files to your web root directory
2. Ensure `.htaccess` is in the root directory
3. Configure database connection in `backend/config/database.php`
4. The `.htaccess` file will handle routing automatically

### 3. Nginx Hosting

Add this configuration to your Nginx server block:

```nginx
location /backend/ {
    try_files $uri $uri/ @php;
}

location @php {
    fastcgi_pass php-fpm;
    fastcgi_param SCRIPT_FILENAME $document_root/router.php;
    include fastcgi_params;
}

location / {
    try_files $uri $uri/ /index.html;
}
```

## Development

### Frontend Development
```bash
# Install dependencies
npm install

# Start development server (with backend proxy)
npm run dev

# Build for production
npm run build
```

### Backend Development
The backend continues to work as before. All API endpoints are accessible at `/backend/api/`.

### API Configuration
All frontend API calls now use relative paths:
- Old: `http://localhost:8000/api/endpoint.php`
- New: `/backend/api/endpoint.php`

## Key Changes Made

### 1. File Structure
- Moved all frontend files from `frontend/` to root directory
- Kept `backend/` directory in its current location
- Removed the `frontend/` wrapper folder

### 2. API Endpoints
- Updated all API calls to use `/backend/api/` instead of `http://localhost:8000/api/`
- Modified `src/services/api.js` to use relative paths
- Updated all component files with hardcoded localhost references

### 3. Build Configuration
- Updated `vite.config.js` with proxy configuration for development
- Added build settings for production deployment
- Updated `package.json` with new project name and scripts

### 4. CORS Configuration
- Updated `backend/config/cors.php` to handle same-origin requests
- Added support for the current domain in allowed origins

### 5. Routing
- Created `router.php` for handling both frontend and backend requests
- Added `.htaccess` for Apache URL rewriting
- Updated Railway configuration to use the new router

## Environment Variables

For production deployment, ensure these environment variables are set:

```
DB_HOST=your_database_host
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
```

## Testing the Deployment

### Local Testing
```bash
# Start PHP development server
php -S localhost:8000 router.php

# Visit http://localhost:8000
```

### Production Testing
1. **Frontend**: Visit `https://yourdomain.com/` - should load the React app
2. **Backend**: Visit `https://yourdomain.com/backend/api/available-apps.php` - should return JSON
3. **Health Check**: Visit `https://yourdomain.com/backend/api/health.php` - should return status

## Troubleshooting

### Common Issues

1. **404 on API calls**
   - Check that `router.php` is in the root directory
   - Verify backend files are in the `backend/` directory
   - Check server configuration for URL rewriting

2. **CORS errors**
   - Verify `backend/config/cors.php` includes your domain
   - Check that requests are coming from the same domain

3. **Static assets not loading**
   - Run `npm run build` to generate production assets
   - Copy `dist/index.html` and `dist/assets/` to root directory
   - Check file permissions

4. **Database connection issues**
   - Verify environment variables are set correctly
   - Check `backend/config/database.php` configuration
   - Test database connectivity

## Benefits of Single Domain Deployment

1. **Simplified Deployment**: One domain, one deployment
2. **No CORS Issues**: Same-origin requests eliminate CORS complications
3. **Easier SSL**: Single SSL certificate covers both frontend and backend
4. **Better Performance**: Reduced latency from same-domain requests
5. **Simplified Configuration**: No need for separate frontend/backend hosting

## Maintenance

### Updating Frontend
1. Make changes in `src/` directory
2. Run `npm run build`
3. Copy `dist/index.html` and `dist/assets/` to root
4. Deploy changes

### Updating Backend
1. Make changes in `backend/` directory
2. Deploy changes directly (no build step required)

The project is now ready for single domain deployment! 🚀
