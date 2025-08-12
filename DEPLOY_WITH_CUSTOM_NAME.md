# 🚀 Deploy with Custom Name - Shopify Reviews

## Option 1: Deploy from Your Original Repository

### Step 1: Copy Configuration Files
Copy these files to your `mahbubshovan/shopify-reviews` repository:
- `railway.toml`
- `Procfile` 
- `composer.json`
- `nixpacks.toml`

### Step 2: Update Your Database Config
Update `backend/config/database.php` with Railway environment variables:
```php
$this->host = $_ENV['MYSQL_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
$this->db_name = $_ENV['MYSQL_DATABASE'] ?? $_ENV['DB_NAME'] ?? 'shopify_reviews';
$this->username = $_ENV['MYSQL_USER'] ?? $_ENV['DB_USER'] ?? 'root';
$this->password = $_ENV['MYSQL_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';
$this->port = $_ENV['MYSQL_PORT'] ?? '3306';
```

### Step 3: Deploy with Custom Names

**Railway Deployment:**
1. Go to railway.app/new
2. Select "Deploy from GitHub repo"
3. Choose `mahbubshovan/shopify-reviews`
4. **Custom project name**: `review-analytics-dashboard` (or any name you want)

**Vercel Deployment:**
1. Go to vercel.com/new
2. Import `mahbubshovan/shopify-reviews`
3. **Custom project name**: `shopify-review-manager` (or any name you want)
4. Set root directory: `frontend`

## Option 2: Use Any Repository Name

You can:
- **Rename your GitHub repository** to anything you want
- **Deploy with that new name**
- **Use custom domains** for both services

## Option 3: Environment-Based Naming

**Railway Project Names:**
- `shopify-analytics-prod`
- `review-dashboard-live`
- `app-review-system`

**Vercel Project Names:**
- `review-analytics-ui`
- `shopify-dashboard-frontend`
- `app-review-interface`

## 🔗 Final URLs (Examples):
- **Backend**: `https://review-analytics-prod.railway.app`
- **Frontend**: `https://shopify-dashboard-frontend.vercel.app`

**The repository name doesn't matter - you can use any project names you prefer during deployment!**
