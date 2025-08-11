# Access Reviews Feature

## Overview
The Access Reviews feature provides a dedicated interface for managing and assigning reviews from the last 30 days across all Shopify apps. It allows users to track which team member "earned" each review through their work.

## Database Schema

### `access_reviews` Table
```sql
CREATE TABLE access_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) NOT NULL,
    review_date DATE NOT NULL,
    review_content TEXT NOT NULL,
    country_name VARCHAR(100),
    earned_by VARCHAR(255) NULL,
    original_review_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (original_review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (original_review_id)
);
```

## Synchronization Logic

### Automatic Sync
- Triggers automatically after each app scraping session
- Runs through `AccessReviewsSync::syncAccessReviews()`

### Sync Process
1. **Remove Old Reviews**: Deletes reviews older than 30 days from `access_reviews`
2. **Remove Orphaned Reviews**: Removes reviews that no longer exist on the original Shopify pages
3. **Add New Reviews**: Adds new reviews from last 30 days from main `reviews` table
4. **Preserve Assignments**: Existing `earned_by` values are preserved during sync

### Data Matching Rules
- Match by `original_review_id` (foreign key to main reviews table)
- If review exists: keep existing `earned_by` value
- If review is new: add with `earned_by` = NULL (unassigned state)
- If review is old (>30 days): remove from `access_reviews`
- If review no longer exists on source page: remove from `access_reviews` (including assignments)

### Assignment Behavior
- **New Reviews**: Always start in unassigned state (`earned_by` = NULL)
- **Assignment Persistence**: Manual assignments persist through sync operations
- **Assignment Removal**: Only occurs when review is removed from source or ages out (>30 days)

## API Endpoints

### GET `/api/access-reviews.php`
Returns all access reviews grouped by app name with statistics.

**Response:**
```json
{
  "success": true,
  "reviews": {
    "StoreSEO": [
      {
        "id": 1,
        "app_name": "StoreSEO",
        "review_date": "2025-08-01",
        "review_content": "Great app...",
        "country_name": "US",
        "earned_by": "John Smith",
        "original_review_id": 123
      }
    ]
  },
  "stats": {
    "total_reviews": 18,
    "assigned_reviews": 5,
    "unassigned_reviews": 13,
    "reviews_by_app": [...]
  }
}
```

### PUT `/api/access-reviews.php`
Updates the `earned_by` field for a specific review.

**Request:**
```json
{
  "review_id": 1,
  "earned_by": "John Smith"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Earned By updated successfully"
}
```

### POST `/api/access-reviews.php`
Triggers synchronization of access reviews. This endpoint is primarily used internally by the scraping system and is automatically called after each app scraping operation.

**Response:**
```json
{
  "success": true,
  "message": "Access reviews synchronized successfully"
}
```

## Frontend Features

### Access Page (`/access`)
- **Statistics Dashboard**: Shows total, assigned, unassigned reviews and app count
- **Reviews by App**: Grouped tables showing reviews for each app
- **Inline Editing**: Click-to-edit functionality for "Earned By" field
- **Real-time Updates**: Changes save immediately and update UI
- **Automatic Sync**: Data is automatically updated when individual apps are scraped

### Navigation
- Accessible via navigation menu: "Access Reviews"
- Integrated with main analytics dashboard

## Usage Workflow

1. **Automatic Population**: Reviews are automatically added when scrapers run
2. **Review Assignment**: Users click on "Earned By" field to assign team members
3. **Data Persistence**: Assignments are preserved during future syncs
4. **Fresh Data**: Only shows reviews from last 30 days (calculated dynamically)

## Technical Implementation

### Backend Classes
- `AccessReviewsSync`: Main synchronization logic
- `DatabaseManager`: Database connection management

### Frontend Components
- `Access.jsx`: Main access reviews page
- `Layout.jsx`: Navigation wrapper with routing

### Integration Points
- All scraper classes automatically trigger sync after completion
- React Router handles navigation between analytics and access pages
- Real-time API calls for immediate UI updates

## Benefits

1. **Team Accountability**: Track which team member earned each review
2. **Fresh Data**: Always shows current last 30 days (rolling window)
3. **Data Integrity**: Preserves assignments during data refreshes
4. **User-Friendly**: Simple click-to-edit interface
5. **Automatic Sync**: No manual intervention required for data updates
