# 🗄️ Shopify Reviews - Database Setup Guide

## 📋 Quick Setup Instructions

### 1. Download Database File
- **Database File**: `shopify_reviews_database_complete.sql` (in project root)
- **Database Name**: `shopify_reviews`

### 2. Import Database

#### Option A: Using Command Line
```bash
# Create database
mysql -u your_username -p -e "CREATE DATABASE shopify_reviews;"

# Import the complete database
mysql -u your_username -p shopify_reviews < shopify_reviews_database_complete.sql
```

#### Option B: Using phpMyAdmin
1. Login to phpMyAdmin
2. Create new database named `shopify_reviews`
3. Select the database
4. Go to "Import" tab
5. Choose `shopify_reviews_database_complete.sql` file
6. Click "Go" to import

#### Option C: Using cPanel/Hosting Control Panel
1. Go to MySQL Databases
2. Create database named `shopify_reviews`
3. Go to phpMyAdmin
4. Select the database
5. Import the SQL file

### 3. Update Configuration

Edit `backend/config/database.php` with your live server credentials:

```php
// Update these values for your live server
$this->host = 'your_live_host';        // e.g., 'localhost' or 'mysql.yourhost.com'
$this->db_name = 'shopify_reviews';    // Keep this as is
$this->username = 'your_db_username';  // Your MySQL username
$this->password = 'your_db_password';  // Your MySQL password
```

### 4. Test Connection

Create a test file `test_connection.php` in your backend folder:

```php
<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "✅ Database connection successful!";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM reviews");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<br>📊 Total reviews in database: " . $result['count'];
} else {
    echo "❌ Database connection failed!";
}
?>
```

## 📊 Database Structure

### Tables Created:

1. **`reviews`** - Main reviews data
   - id, app_name, store_name, country_name, rating, review_content, review_date, created_at

2. **`app_metadata`** - App statistics and metadata
   - id, app_name, total_reviews, star ratings breakdown, overall_rating, last_updated

3. **`access_reviews`** - Last 30 days reviews with editable fields
   - id, app_name, review_date, review_content, country_name, earned_by, original_review_id

### Sample Data Included:

- **6 Apps**: StoreSEO, StoreFAQ, Vidify, TrustSync, EasyFlow, BetterDocs FAQ
- **Sample Reviews**: Historical and recent reviews for testing
- **App Metadata**: Realistic statistics for each app

## 🔧 Environment Variables (Optional)

For better security, you can use environment variables:

```bash
# Add to your .env file or server environment
DB_HOST=your_live_host
DB_NAME=shopify_reviews
DB_USER=your_db_username
DB_PASS=your_db_password
```

## 🚀 Deployment Checklist

- [ ] Database created with name `shopify_reviews`
- [ ] SQL file imported successfully
- [ ] Database credentials updated in `backend/config/database.php`
- [ ] Test connection works
- [ ] Backend API endpoints accessible
- [ ] Frontend can fetch data from backend

## 🔍 Troubleshooting

### Common Issues:

1. **Connection Failed**
   - Check database credentials
   - Verify database name is exactly `shopify_reviews`
   - Ensure MySQL service is running

2. **Import Errors**
   - Check file encoding (should be UTF-8)
   - Verify MySQL version compatibility
   - Check for sufficient privileges

3. **Permission Errors**
   - Ensure database user has full privileges on `shopify_reviews` database
   - Grant SELECT, INSERT, UPDATE, DELETE, CREATE, DROP permissions

### SQL Commands for Permissions:
```sql
GRANT ALL PRIVILEGES ON shopify_reviews.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

## 📞 Support

If you encounter any issues:
1. Check the error logs in your hosting control panel
2. Verify all file paths are correct
3. Ensure PHP has PDO MySQL extension enabled
4. Test with the connection test file above

---

**Your Shopify Reviews database is now ready for production! 🎉**
