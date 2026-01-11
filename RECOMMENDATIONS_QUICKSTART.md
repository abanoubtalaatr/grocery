# 🎉 Meal Recommendations API - Quick Start

## ✨ What's New?

A brand new **Meal Recommendations API** that intelligently suggests meals to your users based on:
- 🌟 Featured meals
- 💰 Special offers & discounts
- 🎲 Variety from different categories
- ✅ Availability

---

## 🚀 Quick Usage

### Endpoint
```
GET /api/meals/recommendations?limit=5
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Response
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

---

## 🎯 Recommendation Reasons

Each meal includes a reason why it was recommended:

| Reason | Meaning |
|--------|---------|
| `Featured with special offer` 🌟💰 | Best deals! |
| `Featured meal` 🌟 | Premium selection |
| `Special offer` 💰 | Great discount |
| `Popular choice` ⭐ | Variety pick |

---

## 📱 Frontend Integration Ideas

### 1. Home Screen
```javascript
// Show 6 recommendations on home
const recommendations = await fetch('/api/meals/recommendations?limit=6', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

### 2. "You Might Also Like"
```javascript
// Show 4 related meals
const suggestions = await fetch('/api/meals/recommendations?limit=4', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

### 3. Empty State
```javascript
// Show 3 deals when cart is empty
const deals = await fetch('/api/meals/recommendations?limit=3', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

---

## 🧪 Test It Now!

### Option 1: Using cURL
```bash
# 1. Register
curl -X POST "http://127.0.0.1:8001/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "username": "testuser",
    "password": "Test123456",
    "password_confirmation": "Test123456",
    "agree_terms": true
  }'

# 2. Login (get token)
curl -X POST "http://127.0.0.1:8001/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "test@example.com",
    "password": "Test123456"
  }'

# 3. Get recommendations (use token from step 2)
curl -X GET "http://127.0.0.1:8001/api/meals/recommendations?limit=5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Option 2: Using Test Script
```bash
./test_recommendations.sh
```

---

## 🎁 What You Get

### Smart Algorithm
- 50% featured meals with offers
- 50% variety from different categories
- Shuffled for uniqueness
- Only available meals

### Rich Data
- Meal details (title, description, images)
- Pricing (original, discount, final)
- Category information
- Offer titles
- Recommendation reasons

### Flexible
- Customizable limit (default: 10)
- Fast and efficient queries
- Easy to integrate

---

## 📚 Documentation

- **Full API Docs**: `API_DOCUMENTATION.md`
- **Feature Details**: `RECOMMENDATIONS_FEATURE.md`
- **Test Script**: `test_recommendations.sh`

---

## ✅ Files Modified/Created

### New Files
- `RECOMMENDATIONS_FEATURE.md` - Comprehensive feature documentation
- `RECOMMENDATIONS_QUICKSTART.md` - This quick start guide
- `test_recommendations.sh` - Automated test script

### Modified Files
- `app/Http/Controllers/Api/MealController.php` - Added `recommendations()` method
- `routes/api.php` - Added recommendations route
- `API_DOCUMENTATION.md` - Added recommendations endpoint documentation

---

## 🎯 Use Cases

1. **Discovery Feed** - Help users find new meals
2. **Deal Highlights** - Promote special offers
3. **Cross-Selling** - Suggest complementary items
4. **Empty States** - Fill empty screens with suggestions
5. **Engagement** - Keep users browsing and interested

---

## 💡 Example Use in Mobile App

```javascript
// React Native / Flutter / Swift example concept

class HomeScreen {
  async loadRecommendations() {
    try {
      const response = await api.get('/meals/recommendations?limit=6');
      
      this.setState({
        recommendations: response.data.data,
        loading: false
      });
    } catch (error) {
      console.error('Failed to load recommendations:', error);
    }
  }

  renderRecommendation(meal) {
    return (
      <MealCard
        title={meal.title}
        image={meal.image_url}
        price={meal.final_price}
        originalPrice={meal.price}
        badge={meal.recommendation_reason}
        hasOffer={meal.has_offer}
        offerTitle={meal.offer_title}
      />
    );
  }
}
```

---

## 🎊 Success!

Your Meal Recommendations API is now ready to use! 

🚀 Start integrating it into your mobile app for an enhanced user experience!

---

**Questions?** Check the full documentation in `RECOMMENDATIONS_FEATURE.md`
