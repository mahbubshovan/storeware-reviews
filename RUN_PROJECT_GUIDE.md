# 🚀 Complete Terminal Guide to Run Shopify Reviews Project

## 📋 Prerequisites Check
Before starting, ensure you have:
- ✅ PHP 7.4+ installed (`php --version`)
- ✅ MySQL/MariaDB running (`mysql --version`)
- ✅ Node.js 16+ installed (`node --version`)
- ✅ npm installed (`npm --version`)

## 🗄️ Step 1: Setup Database

### Option A: Quick Database Setup (Recommended)
```bash
# Navigate to project root
cd /Users/wpdev/Github/shopify-reviews

# Create database and import complete schema
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS shopify_reviews;"
mysql -u root -p shopify_reviews < shopify_reviews_database_complete.sql

# Verify database was created
mysql -u root -p -e "USE shopify_reviews; SHOW TABLES;"
```

### Option B: Manual Database Setup
```bash
# Login to MySQL
mysql -u root -p

# In MySQL prompt:
CREATE DATABASE shopify_reviews;
USE shopify_reviews;
SOURCE /Users/wpdev/Github/shopify-reviews/shopify_reviews_database_complete.sql;
SHOW TABLES;
EXIT;
```

## 🔧 Step 2: Configure Database Connection

Check if your database credentials are correct in `backend/config/database.php`:
```bash
# View current database config
cat backend/config/database.php
```

If needed, update the credentials (default should work for local development):
- Host: `localhost`
- Database: `shopify_reviews`
- Username: `root`
- Password: `` (empty for default XAMPP/MAMP)

## 🖥️ Step 3: Start Backend Server

```bash
# Navigate to backend directory
cd backend

# Start PHP built-in server on port 8000
php -S localhost:8000

# You should see:
# PHP 8.x.x Development Server (http://localhost:8000) started
```

**Keep this terminal open!** The backend server needs to keep running.

## ⚛️ Step 4: Start Frontend Server (New Terminal)

Open a **NEW terminal window/tab** and run:

```bash
# Navigate to project root
cd /Users/wpdev/Github/shopify-reviews

# Navigate to frontend directory
cd frontend

# Install dependencies (first time only)
npm install

# Start Vite development server
npm run dev

# You should see:
# VITE v7.x.x ready in xxx ms
# ➜ Local: http://localhost:5173/
```

## 🌐 Step 5: Access the Application

Open your browser and go to:
```
http://localhost:5173/
```

You should see the Shopify App Review Analytics dashboard!

## 🔍 Step 6: Test the Setup

1. **Check if backend is working:**
   ```bash
   curl http://localhost:8000/api/available-apps.php
   ```
   Should return JSON with available apps.

2. **Check if frontend can connect to backend:**
   - Open browser console (F12)
   - Look for any CORS or connection errors
   - The app should load without errors

## 🛠️ Troubleshooting

### Backend Issues:

**Database Connection Error:**
```bash
# Test database connection
cd backend
php -r "
require_once 'config/database.php';
\$db = new Database();
\$conn = \$db->getConnection();
if (\$conn) echo 'Database connected successfully!';
else echo 'Database connection failed!';
"
```

**Port 8000 Already in Use:**
```bash
# Use different port
php -S localhost:8001

# Update frontend API URL in frontend/src/services/api.js:
# Change: http://localhost:8000/api
# To: http://localhost:8001/api
```

### Frontend Issues:

**Port 5173 Already in Use:**
```bash
# Vite will automatically use next available port (5174, 5175, etc.)
# Or specify a different port:
npm run dev -- --port 3000
```

**Dependencies Issues:**
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### CORS Issues:
The backend is already configured for CORS with localhost:5173. If you see CORS errors:

1. Check that backend is running on port 8000
2. Check that frontend is running on port 5173
3. Verify `backend/config/cors.php` includes your ports

## 📱 Quick Commands Summary

**Terminal 1 (Backend):**
```bash
cd /Users/wpdev/Github/shopify-reviews/backend
php -S localhost:8000
```

**Terminal 2 (Frontend):**
```bash
cd /Users/wpdev/Github/shopify-reviews/frontend
npm run dev
```

**Browser:**
```
http://localhost:5173/
```

## 🎯 Expected Result

You should see:
- ✅ Shopify App Review Analytics dashboard
- ✅ App selector dropdown with 6 apps
- ✅ "Choose an app to analysis" message by default
- ✅ Navigation tabs: Analytics, Access, Review Count, Review Credit
- ✅ No console errors in browser

## 🔄 Daily Development Workflow

```bash
# Start both servers (2 terminals)
# Terminal 1:
cd /Users/wpdev/Github/shopify-reviews/backend && php -S localhost:8000

# Terminal 2:
cd /Users/wpdev/Github/shopify-reviews/frontend && npm run dev

# Open browser: http://localhost:5173/
```

---

**🎉 Your Shopify Reviews project should now be running successfully!**
