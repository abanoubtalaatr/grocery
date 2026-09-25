# Meal Recommendations API - Feature Summary

## Overview
The Meal Recommendations API provides intelligent meal suggestions to users based on featured items, special offers, and variety across different categories. This enhances user experience by helping them discover meals they might be interested in.

## Endpoint

```
GET /api/meals/recommendations
```

**Authentication:** Required (Bearer Token)

## Features

### 1. Smart Algorithm
The recommendations engine uses a sophisticated algorithm that:
- **Prioritizes Featured Meals with Offers** (50% of results)
  - Meals that are both featured AND have discount prices
  - These are most likely to convert and satisfy users
  
- **Includes Variety** (remaining 50%)
  - Random meals from different categories
  - Ensures diverse options for different tastes
  
- **Shuffles Results**
  - Creates a unique experience each time
  - Prevents predictable recommendations
  
- **Filters Availability**
  - Only shows meals that are currently available
  - Respects the `is_available` flag

### 2. Recommendation Reasons
Each meal includes a `recommendation_reason` field explaining why it was recommended:

| Reason | Description |
|--------|-------------|
| `Featured with special offer` | Featured meal with an active discount |
| `Featured meal` | Featured meal without discount |
| `Special offer` | Regular meal with an active discount |
| `Popular choice` | Regular meal (variety suggestion) |

### 3. Customizable Results
- **Limit Parameter**: Control the number of recommendations (default: 10)
- **Flexible Response**: Returns up to the specified limit
- **Efficient Queries**: Uses eager loading to minimize database hits

## Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of recommendations to return |

## Response Structure

```json
{
    "success": true,
    "message": "Meal recommendations retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Fresh Organic Salad Mix",
            "slug": "fresh-organic-salad-mix",
            "description": "A delightful mix of fresh organic vegetables...",
            "image_url": "https://images.unsplash.com/photo-xxx",
            "offer_title": "20% OFF Today",
            "price": "12.99",
            "discount_price": "10.39",
            "final_price": 10.39,
            "has_offer": true,
            "is_featured": true,
            "category": {
                "id": 1,
                "name": "Vegetables",
                "slug": "vegetables"
            },
            "recommendation_reason": "Featured with special offer"
        }
    ]
}
```

## Usage Examples

### Example 1: Get 5 Recommendations
```bash
curl -X GET "http://localhost:8000/api/meals/recommendations?limit=5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Example 2: Get Default (10) Recommendations
```bash
curl -X GET "http://localhost:8000/api/meals/recommendations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Example 3: Testing Script
Run the included test script:
```bash
./test_recommendations.sh
```

## Implementation Details

### Controller Method
Location: `app/Http/Controllers/Api/MealController.php`

The `recommendations()` method:
1. Accepts optional `limit` parameter (default: 10)
2. Fetches featured meals with discounts (up to 50% of limit)
3. Fetches random meals from remaining pool
4. Merges and shuffles results
5. Adds recommendation reasons
6. Returns formatted response

### Route
Location: `routes/api.php`

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('meals')->group(function () {
        Route::get('/recommendations', [MealController::class, 'recommendations']);
        // ... other routes
    });
});
```

### Database Queries
The feature uses efficient queries with:
- Eager loading (`with('category')`)
- Scopes (`available()`, `featured()`)
- Smart filtering (`whereNotIn()` to avoid duplicates)
- Random ordering (`inRandomOrder()`)

## Use Cases

### 1. Home Screen
Display recommended meals when users open the app:
```javascript
const recommendations = await api.get('/meals/recommendations?limit=6');
```

### 2. "You Might Also Like"
Show recommendations after viewing a meal:
```javascript
const suggestions = await api.get('/meals/recommendations?limit=4');
```

### 3. Empty Cart State
Encourage purchases when cart is empty:
```javascript
const deals = await api.get('/meals/recommendations?limit=3');
```

### 4. Personalized Feed
Create a dynamic feed for users:
```javascript
const feed = await api.get('/meals/recommendations?limit=15');
```

## Benefits

### For Users
✅ Discover new meals easily  
✅ Find the best deals quickly  
✅ Get variety in meal options  
✅ See fresh suggestions each time  
✅ Understand why meals are recommended  

### For Business
✅ Increase meal visibility  
✅ Promote featured items  
✅ Boost conversion on offers  
✅ Improve user engagement  
✅ Drive sales through discovery  

## Performance Considerations

- **Efficient Queries**: Uses database indexes and eager loading
- **Reasonable Limits**: Default limit of 10 prevents overloading
- **Cached Relationships**: Category data is preloaded
- **Random Sampling**: Database-level randomization is fast

## Future Enhancements

Potential improvements for future versions:

1. **User-Based Recommendations**
   - Track user preferences and order history
   - Recommend based on past purchases
   - Consider dietary restrictions

2. **Time-Based Recommendations**
   - Morning: Breakfast items
   - Afternoon: Lunch specials
   - Evening: Dinner options

3. **Location-Based**
   - Regional specialties
   - Local seasonal items

4. **Collaborative Filtering**
   - "Users who bought X also liked Y"
   - Social proof integration

5. **A/B Testing**
   - Test different algorithms
   - Optimize for conversion

6. **Machine Learning**
   - Predictive recommendations
   - Personalization at scale

## Testing

### Manual Testing
1. Register/Login to get a token
2. Call the recommendations endpoint
3. Verify:
   - Response contains meals
   - All meals have recommendation reasons
   - Limit is respected
   - Results include variety

### Automated Testing
Location: `test_recommendations.sh`

The script:
- Creates a test user
- Logs in to get token
- Fetches recommendations with different limits
- Displays formatted results

Run:
```bash
chmod +x test_recommendations.sh
./test_recommendations.sh
```

## Troubleshooting

### No Recommendations Returned
**Cause**: No available meals in the database  
**Solution**: Run the seeders: `php artisan db:seed`

### Empty Results
**Cause**: All meals are marked as unavailable  
**Solution**: Update meals to set `is_available = true`

### Same Recommendations Every Time
**Cause**: Database doesn't support `inRandomOrder()` properly  
**Solution**: Ensure MySQL/PostgreSQL is used (not SQLite in testing)

### Performance Issues
**Cause**: Too many meals in database  
**Solution**: Add indexes on `is_available`, `is_featured`, `discount_price`

## Conclusion

The Meal Recommendations API is a powerful feature that enhances user experience and drives business value through intelligent meal discovery. Its flexible design allows for future enhancements while maintaining excellent performance.

For full API documentation, see: `API_DOCUMENTATION.md`
