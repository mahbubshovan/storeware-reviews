# 🚀 Railway Deployment Guide - Shopify Reviews App

## 📋 Prerequisites
- GitHub account with this repository
- Railway account (free signup at railway.app)

## 🏗️ Deployment Steps

### Step 1: Deploy Backend to Railway

1. **Go to [railway.app](https://railway.app)** and login
2. **Click "New Project"**
3. **Select "Deploy from GitHub repo"**
4. **Choose your `shopify-reviews` repository**
5. **Railway will automatically detect the PHP configuration**

### Step 2: Add MySQL Database

1. **In your Railway project dashboard**
2. **Click "New Service"**
3. **Select "Database" → "MySQL"**
4. **Railway will automatically create and link the database**

### Step 3: Import Database Schema

1. **Go to Railway MySQL service**
2. **Click "Connect" → "MySQL CLI"**
3. **Run the import command:**
```bash
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < shopify_reviews_database_complete.sql
```

### Step 4: Deploy Frontend to Vercel

1. **Go to [vercel.com](https://vercel.com)**
2. **Connect your GitHub repository**
3. **Set build configuration:**
   - **Framework**: Vite
   - **Root Directory**: `frontend`
   - **Build Command**: `npm run build`
   - **Output Directory**: `dist`
4. **Add environment variable:**
   - `VITE_API_BASE_URL`: `https://your-railway-backend.railway.app/api`

### Step 5: Update Configuration

1. **Get your Railway backend URL** (e.g., `https://shopify-reviews-backend.railway.app`)
2. **Update `frontend/.env.production`** with the actual Railway URL
3. **Redeploy frontend** on Vercel

## 🔗 Final URLs
- **Frontend**: `https://shopify-reviews.vercel.app`
- **Backend**: `https://your-app.railway.app`
- **Database**: Managed by Railway

## 💰 Cost
- **Railway**: $5/month (after free trial)
- **Vercel**: Free
- **Total**: $5/month

## 🔧 Environment Variables (Auto-configured by Railway)
- `MYSQL_HOST`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `MYSQL_PORT`
- `PORT` (for PHP server)

## ✅ What's Configured
- ✅ PHP 8.2 with required extensions
- ✅ MySQL database connection
- ✅ CORS for cross-origin requests
- ✅ Production-ready error handling
- ✅ Automatic health checks
- ✅ Environment variable support

Your app will be fully functional with real-time data scraping and dynamic date filtering!
