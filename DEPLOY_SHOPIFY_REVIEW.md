# 🚀 Deploy "shopify-review" - Custom Named Deployment

## 🎯 Your Custom Deployment Names:
- **Railway Backend**: `shopify-review`
- **Vercel Frontend**: `shopify-review`

## 📋 Step-by-Step Deployment (Browser tabs already open):

### 🔧 Step 1: Railway Backend Deployment

**On Railway (tab already open):**
1. Click **"Deploy from GitHub repo"**
2. Select **"IftekharPial/shopify-reviews"** (configured repository)
3. **Project Name**: Enter `shopify-review`
4. Click **"Deploy"**

**Add MySQL Database:**
1. In your Railway project dashboard
2. Click **"New Service"**
3. Select **"Database" → "MySQL"**
4. Railway auto-configures environment variables

### 📱 Step 2: Vercel Frontend Deployment

**On Vercel (tab already open):**
1. Click **"Import"** next to `IftekharPial/shopify-reviews`
2. **Project Name**: Enter `shopify-review`
3. **Framework Preset**: Vite
4. **Root Directory**: `frontend`
5. **Build Command**: `npm run build`
6. **Output Directory**: `dist`

**Add Environment Variable:**
- **Name**: `VITE_API_BASE_URL`
- **Value**: `https://shopify-review.railway.app/api`

### 🗄️ Step 3: Import Database

**After Railway MySQL is ready:**
1. Go to Railway MySQL service
2. Click **"Connect"** → **"MySQL CLI"**
3. Run:
```bash
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < shopify_reviews_database_complete.sql
```

## 🌐 Your Live URLs:
- **Frontend**: `https://shopify-review.vercel.app`
- **Backend**: `https://shopify-review.railway.app`

## ✅ What's Configured:
- ✅ **Custom name**: "shopify-review" for both services
- ✅ **PHP 8.2** with all required extensions
- ✅ **MySQL database** with Railway environment variables
- ✅ **CORS configured** for your custom domains
- ✅ **Real-time scraping** with 2-minute timeout
- ✅ **Dynamic date filtering** (This Month, Last 30 Days)
- ✅ **Production error handling**

## 💰 Cost:
- **Railway**: $5/month (after free trial)
- **Vercel**: Free
- **Total**: $5/month

**Your "shopify-review" app will be live and fully functional with all features working!**

Just follow the steps in the Railway and Vercel tabs I opened for you.
