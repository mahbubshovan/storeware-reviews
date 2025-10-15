# Scraper Retry Mechanism - Automatic HTTP Request Retry

## 🎯 Feature Overview

The Shopify Review Scraper now includes **automatic retry logic** for failed HTTP requests. When a request fails (HTTP status ≠ 200), the scraper will automatically retry up to 3 times with exponential backoff.

## 🛡️ How It Works

### **Retry Logic Flow**

```
Attempt 1: Make HTTP request
  ↓
  HTTP 200? → ✅ Success, return data
  ↓
  HTTP ≠ 200? → ❌ Failed
  ↓
  Wait 2 seconds (2^1)
  ↓
Attempt 2: Retry HTTP request
  ↓
  HTTP 200? → ✅ Success, return data
  ↓
  HTTP ≠ 200? → ❌ Failed
  ↓
  Wait 4 seconds (2^2)
  ↓
Attempt 3: Retry HTTP request
  ↓
  HTTP 200? → ✅ Success, return data
  ↓
  HTTP ≠ 200? → ❌ Failed after 3 attempts
```

### **Exponential Backoff**

The scraper uses exponential backoff to avoid overwhelming the server:

| Attempt | Wait Time | Formula |
|---------|-----------|---------|
| 1 → 2 | 2 seconds | 2^1 = 2s |
| 2 → 3 | 4 seconds | 2^2 = 4s |
| 3 → 4 | 8 seconds | 2^3 = 8s |

## 💻 Implementation

### **Enhanced `fetchPage()` Method**

```php
private function fetchPage($url, $maxRetries = 3) {
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        $attempt++;
        
        // Make HTTP request with cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0...');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Success - return the HTML
        if ($httpCode === 200 && $html && !$error) {
            if ($attempt > 1) {
                echo "✅ Success on attempt $attempt\n";
            }
            return $html;
        }
        
        // Failed - log and retry if attempts remaining
        echo "❌ Attempt $attempt failed: HTTP $httpCode\n";
        
        if ($attempt < $maxRetries) {
            // Exponential backoff: 2s, 4s, 8s
            $waitTime = pow(2, $attempt);
            echo "⏳ Waiting {$waitTime}s before retry...\n";
            sleep($waitTime);
        }
    }
    
    // All retries exhausted
    echo "❌ Failed after $maxRetries attempts\n";
    return false;
}
```

## 📊 Success Conditions

A request is considered **successful** when:

1. ✅ HTTP status code = 200
2. ✅ HTML content is not empty
3. ✅ No cURL errors occurred

A request is considered **failed** when:

1. ❌ HTTP status code ≠ 200 (e.g., 429, 500, 503)
2. ❌ HTML content is empty
3. ❌ cURL error occurred (timeout, connection failed, etc.)

## 🎯 Common HTTP Status Codes Handled

| Code | Meaning | Retry Behavior |
|------|---------|----------------|
| 200 | Success | ✅ Return data immediately |
| 429 | Too Many Requests | 🔄 Retry with backoff |
| 500 | Internal Server Error | 🔄 Retry with backoff |
| 503 | Service Unavailable | 🔄 Retry with backoff |
| 404 | Not Found | 🔄 Retry (may be temporary) |
| 403 | Forbidden | 🔄 Retry (may be rate limit) |

## 📝 Console Output Examples

### **Scenario 1: Success on First Attempt**
```
📄 Scraping page 1...
✅ Found 15 reviews on page 1
```

### **Scenario 2: Success on Second Attempt**
```
📄 Scraping page 2...
❌ Attempt 1 failed: HTTP 429
⏳ Waiting 2s before retry...
✅ Success on attempt 2
✅ Found 15 reviews on page 2
```

### **Scenario 3: Success on Third Attempt**
```
📄 Scraping page 3...
❌ Attempt 1 failed: HTTP 503
⏳ Waiting 2s before retry...
❌ Attempt 2 failed: HTTP 503
⏳ Waiting 4s before retry...
✅ Success on attempt 3
✅ Found 15 reviews on page 3
```

### **Scenario 4: Failed After All Retries**
```
📄 Scraping page 4...
❌ Attempt 1 failed: HTTP 500
⏳ Waiting 2s before retry...
❌ Attempt 2 failed: HTTP 500
⏳ Waiting 4s before retry...
❌ Attempt 3 failed: HTTP 500
❌ Failed after 3 attempts
❌ Failed to fetch page 4 - stopping
```

## ⚙️ Configuration

### **Default Settings**

```php
// Maximum retry attempts (default: 3)
$maxRetries = 3;

// Exponential backoff base (default: 2)
$waitTime = pow(2, $attempt); // 2^1, 2^2, 2^3

// Request timeout (default: 30s)
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Connection timeout (default: 10s)
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

// Delay between pages (default: 2.0s)
usleep(2000000); // 2.0 second delay
```

### **Customizing Retry Attempts**

To change the maximum retry attempts, modify the `fetchPage()` call:

```php
// Default: 3 retries
$html = $this->fetchPage($url);

// Custom: 5 retries
$html = $this->fetchPage($url, 5);

// No retries (fail immediately)
$html = $this->fetchPage($url, 1);
```

## 🚀 Benefits

### **1. Improved Reliability**
- ✅ Handles temporary server issues automatically
- ✅ Recovers from rate limiting (HTTP 429)
- ✅ Deals with network hiccups

### **2. Better Data Collection**
- ✅ Fewer failed scraping sessions
- ✅ More complete review data
- ✅ Reduced manual intervention

### **3. Smart Backoff**
- ✅ Exponential delays prevent server overload
- ✅ Gives server time to recover
- ✅ Respects rate limits

### **4. Clear Logging**
- ✅ Shows which attempt succeeded
- ✅ Logs all failures with HTTP codes
- ✅ Easy to debug issues

## 📊 Performance Impact

### **Best Case (Success on First Attempt)**
- Time: Same as before (no delay)
- Requests: 1 request per page

### **Typical Case (Success on Second Attempt)**
- Time: +2 seconds per failed page
- Requests: 2 requests per page

### **Worst Case (Failed After 3 Attempts)**
- Time: +6 seconds per failed page (2s + 4s)
- Requests: 3 requests per page
- Result: Scraping stops for that app

## 🧪 Testing

### **Test Scenario 1: Normal Operation**
```bash
php backend/populate_all_apps_data.php
```
Expected: All pages scraped successfully on first attempt

### **Test Scenario 2: Rate Limiting**
If you encounter HTTP 429:
- Scraper will automatically retry
- Wait times: 2s, 4s, 8s
- Should succeed on retry

### **Test Scenario 3: Server Error**
If you encounter HTTP 500/503:
- Scraper will automatically retry
- Should succeed when server recovers

## 🔧 Troubleshooting

### **Issue: Still getting failures after retries**

**Solution 1:** Increase retry attempts
```php
$html = $this->fetchPage($url, 5); // Try 5 times instead of 3
```

**Solution 2:** Increase delay between pages
```php
usleep(3000000); // 3.0 second delay instead of 2.0
```

**Solution 3:** Increase request timeout
```php
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60s instead of 30s
```

### **Issue: Scraping is too slow**

**Solution 1:** Reduce retry attempts
```php
$html = $this->fetchPage($url, 2); // Only 2 attempts
```

**Solution 2:** Reduce delay between pages
```php
usleep(1000000); // 1.0 second delay
```

## 📝 Files Modified

- `backend/scraper/ImprovedShopifyReviewScraper.php`
  - Enhanced `fetchPage()` method with retry logic
  - Added exponential backoff mechanism
  - Improved error logging

## 🎯 Summary

The scraper now automatically retries failed HTTP requests up to 3 times with exponential backoff (2s, 4s, 8s). This significantly improves reliability when dealing with:

- ✅ Rate limiting (HTTP 429)
- ✅ Temporary server errors (HTTP 500, 503)
- ✅ Network timeouts
- ✅ Connection issues

The retry mechanism is **automatic** and requires **no manual intervention**. Failed requests are logged clearly, and successful retries are reported.

---

**Last Updated:** 2025-10-15  
**Version:** 1.0  
**Status:** ✅ Implemented and ready for use

