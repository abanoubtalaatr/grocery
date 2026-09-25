# 🚀 Quick API Reference - Favorites & Search

## ❤️ Favorites API

### Get All Favorites
```bash
GET /api/favorites
```

### Toggle Favorite (Add/Remove)
```bash
POST /api/favorites/{mealId}/toggle
```

### Check if Favorited
```bash
GET /api/favorites/{mealId}/check
```

### Remove from Favorites
```bash
DELETE /api/favorites/{mealId}
```

---

## 🔍 Search & Filter API

### Base Endpoint
```bash
GET /api/meals
```

### Quick Examples

#### Search
```bash
# Search for "chicken"
GET /api/meals?search=chicken
```

#### Filters
```bash
# By category
GET /api/meals?category_id=1

# By subcategory
GET /api/meals?subcategory_id=5

# By price range
GET /api/meals?min_price=10&max_price=20

# By rating
GET /api/meals?min_rating=4.5

# By brand
GET /api/meals?brand=Premium%20Brand

# Featured only
GET /api/meals?featured=true

# In stock only
GET /api/meals?in_stock=true
```

#### Sorting
```bash
# By price (low to high)
GET /api/meals?sort_by=price&sort_order=asc

# By rating (high to low)
GET /api/meals?sort_by=rating&sort_order=desc

# By popularity
GET /api/meals?sort_by=sold_count&sort_order=desc

# Newest first
GET /api/meals?sort_by=created_at&sort_order=desc
```

#### Combined
```bash
# Vegetables under $15, rated 4.5+, sorted by rating
GET /api/meals?category_id=1&max_price=15&min_rating=4.5&sort_by=rating&sort_order=desc
```

---

## 📊 All Filter Parameters

| Parameter | Type | Example | Description |
|-----------|------|---------|-------------|
| `search` | string | `chicken` | Search title, description, brand |
| `category_id` | int | `1` | Filter by category |
| `subcategory_id` | int | `5` | Filter by subcategory |
| `min_price` | decimal | `10` | Minimum price |
| `max_price` | decimal | `20` | Maximum price |
| `min_rating` | decimal | `4.5` | Minimum rating (0-5) |
| `brand` | string | `Premium Brand` | Filter by brand |
| `featured` | boolean | `true` | Featured meals only |
| `in_stock` | boolean | `true` | In-stock only |
| `sort_by` | string | `price` | Sort field |
| `sort_order` | string | `asc` | Sort direction |

### Sort Fields
- `created_at` - Date added (default)
- `price` - Final price
- `rating` - Average rating
- `title` - Alphabetical
- `sold_count` - Popularity

---

## 🧪 Test Commands

### Favorites
```bash
TOKEN="YOUR_TOKEN_HERE"

# Add to favorites
curl -X POST "http://127.0.0.1:8001/api/favorites/1/toggle" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Get all favorites
curl -X GET "http://127.0.0.1:8001/api/favorites" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Remove from favorites
curl -X DELETE "http://127.0.0.1:8001/api/favorites/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Search & Filter
```bash
# Search
curl -X GET "http://127.0.0.1:8001/api/meals?search=chicken" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Price range
curl -X GET "http://127.0.0.1:8001/api/meals?min_price=10&max_price=20" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Rating filter
curl -X GET "http://127.0.0.1:8001/api/meals?min_rating=4.5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Sort by price
curl -X GET "http://127.0.0.1:8001/api/meals?sort_by=price&sort_order=asc" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## 💡 Mobile App Examples

### Favorites Feature
```javascript
// Add to favorites
const addToFavorites = async (mealId) => {
    await api.post(`/favorites/${mealId}/toggle`);
    showToast('Added to favorites ❤️');
};

// Remove from favorites
const removeFromFavorites = async (mealId) => {
    await api.delete(`/favorites/${mealId}`);
    showToast('Removed from favorites');
};

// Get favorites
const favorites = await api.get('/favorites');
renderFavorites(favorites.data);
```

### Search Feature
```javascript
// Simple search
const searchMeals = async (query) => {
    const results = await api.get(`/meals?search=${query}`);
    displayResults(results.data);
};

// Advanced filters
const filterMeals = async (filters) => {
    const params = new URLSearchParams(filters);
    const results = await api.get(`/meals?${params}`);
    displayResults(results.data);
};
```

---

## ✨ Response Examples

### Meal with Favorite Status
```json
{
    "id": 1,
    "title": "Fresh Organic Salad Mix",
    "price": "12.99",
    "final_price": 10.39,
    "rating": "4.85",
    "is_favorited": true,  ← NEW!
    "category": {...},
    "subcategory": {...}
}
```

### Search Response
```json
{
    "success": true,
    "data": [...meals...],
    "total_count": 5,
    "filters_applied": {  ← NEW!
        "search": "chicken",
        "min_price": null,
        "max_price": null,
        "sort_by": "created_at",
        "sort_order": "desc"
    }
}
```

---

## 🎯 Common Use Cases

### 1. Search Screen
```
Search Bar → GET /api/meals?search={query}
```

### 2. Filter Screen
```
Category Filter → GET /api/meals?category_id={id}
Price Slider → GET /api/meals?min_price={min}&max_price={max}
Rating Filter → GET /api/meals?min_rating={rating}
```

### 3. Sort Options
```
Sort Dropdown → GET /api/meals?sort_by={field}&sort_order={dir}
```

### 4. Favorites Screen
```
Heart Icon → POST /api/favorites/{id}/toggle
View Favorites → GET /api/favorites
```

---

## 📚 Full Documentation

For complete details, see:
- **FAVORITES_AND_SEARCH_COMPLETE.md** - Full feature documentation
- **NEW_FEATURES_DOCUMENTATION.md** - All API features
- **API_DOCUMENTATION.md** - Complete API reference

---

**All features tested and working! Ready for mobile app integration!** 🚀
