# 🚀 Quick API Testing Guide

Use these cURL commands to test all new features. Replace `YOUR_TOKEN` with your actual auth token.

## 🔐 Get Auth Token First

```bash
# Register a new user
curl -X POST "http://127.0.0.1:8001/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "testuser@example.com",
    "username": "testuser",
    "password": "Test123456",
    "password_confirmation": "Test123456",
    "agree_terms": true
  }'

# Or login with existing user
curl -X POST "http://127.0.0.1:8001/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "testuser@example.com",
    "password": "Test123456"
  }'
```

---

## 📦 1. Subcategories API

### Get All Subcategories
```bash
curl -X GET "http://127.0.0.1:8001/api/subcategories" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

### Get Subcategories for Category
```bash
curl -X GET "http://127.0.0.1:8001/api/subcategories?category_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

### Get Single Subcategory
```bash
curl -X GET "http://127.0.0.1:8001/api/subcategories/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

### Get Meals by Subcategory
```bash
curl -X GET "http://127.0.0.1:8001/api/subcategories/1/meals" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

---

## 🍽️ 2. Enhanced Meal Details

### Get Detailed Meal Information
```bash
curl -X GET "http://127.0.0.1:8001/api/meals/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

**Check for these fields:**
- ⭐ `rating` and `rating_count`
- 📦 `size` and `brand`
- 💰 `price`, `discount_price`, `final_price`
- 📋 `includes` (what's in the package)
- 📚 `how_to_use` (usage instructions)
- ✨ `features` (product highlights)
- 📅 `expiry_date` and `days_until_expiry`
- 📊 `stock_quantity` and `in_stock`
- 🎯 `sold_count`

---

## 🛒 3. Shopping Cart

### View Your Cart
```bash
curl -X GET "http://127.0.0.1:8001/api/cart" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

### Add Item to Cart
```bash
curl -X POST "http://127.0.0.1:8001/api/cart/items" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "meal_id": 1,
    "quantity": 2
  }' | python3 -m json.tool
```

### Add Multiple Items
```bash
# Add first item
curl -X POST "http://127.0.0.1:8001/api/cart/items" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"meal_id": 1, "quantity": 2}'

# Add second item
curl -X POST "http://127.0.0.1:8001/api/cart/items" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"meal_id": 3, "quantity": 1}'

# Add third item
curl -X POST "http://127.0.0.1:8001/api/cart/items" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"meal_id": 7, "quantity": 3}'
```

### Update Item Quantity
```bash
# First, get your cart to find the item ID
curl -X GET "http://127.0.0.1:8001/api/cart" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Then update (replace ITEM_ID with actual ID from cart)
curl -X PUT "http://127.0.0.1:8001/api/cart/items/ITEM_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 5
  }' | python3 -m json.tool
```

### Remove Item from Cart
```bash
curl -X DELETE "http://127.0.0.1:8001/api/cart/items/ITEM_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

### Clear Entire Cart
```bash
curl -X DELETE "http://127.0.0.1:8001/api/cart/clear" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool
```

---

## 🧪 Complete Test Flow

Run this sequence to test everything:

```bash
# Set your token as a variable (replace with your actual token)
TOKEN="YOUR_AUTH_TOKEN_HERE"

echo "=== 1. Browse Subcategories ==="
curl -s -X GET "http://127.0.0.1:8001/api/subcategories" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool | head -30

echo -e "\n=== 2. View Enhanced Meal Details ==="
curl -s -X GET "http://127.0.0.1:8001/api/meals/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool

echo -e "\n=== 3. Add First Item to Cart ==="
curl -s -X POST "http://127.0.0.1:8001/api/cart/items" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"meal_id": 1, "quantity": 2}' | python3 -m json.tool

echo -e "\n=== 4. Add Second Item to Cart ==="
curl -s -X POST "http://127.0.0.1:8001/api/cart/items" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"meal_id": 3, "quantity": 1}' | python3 -m json.tool

echo -e "\n=== 5. View Complete Cart ==="
curl -s -X GET "http://127.0.0.1:8001/api/cart" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | python3 -m json.tool

echo -e "\n✅ All tests complete!"
```

---

## 📊 Expected Results

### Subcategories Response
```json
{
    "success": true,
    "message": "Subcategories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Leafy Greens",
            "category": {"name": "Vegetables"},
            "meals_count": 0
        }
    ]
}
```

### Enhanced Meal Response
```json
{
    "success": true,
    "data": {
        "rating": "4.85",
        "rating_count": 243,
        "size": "1kg",
        "brand": "Premium Brand",
        "expiry_date": "2026-01-15",
        "days_until_expiry": 5,
        "stock_quantity": 67,
        "in_stock": true
    }
}
```

### Cart Response
```json
{
    "success": true,
    "data": {
        "items": [...],
        "item_count": 3,
        "subtotal": "26.57",
        "tax": "2.66",
        "discount": "9.20",
        "total": "20.03"
    }
}
```

---

## 🎯 Postman Collection

Import this JSON into Postman:

```json
{
    "info": {
        "name": "Grocery API - New Features",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Subcategories",
            "item": [
                {
                    "name": "Get All Subcategories",
                    "request": {
                        "method": "GET",
                        "header": [
                            {"key": "Accept", "value": "application/json"},
                            {"key": "Authorization", "value": "Bearer {{token}}"}
                        ],
                        "url": "{{base_url}}/api/subcategories"
                    }
                }
            ]
        },
        {
            "name": "Cart",
            "item": [
                {
                    "name": "Get Cart",
                    "request": {
                        "method": "GET",
                        "header": [
                            {"key": "Accept", "value": "application/json"},
                            {"key": "Authorization", "value": "Bearer {{token}}"}
                        ],
                        "url": "{{base_url}}/api/cart"
                    }
                },
                {
                    "name": "Add to Cart",
                    "request": {
                        "method": "POST",
                        "header": [
                            {"key": "Accept", "value": "application/json"},
                            {"key": "Content-Type", "value": "application/json"},
                            {"key": "Authorization", "value": "Bearer {{token}}"}
                        ],
                        "body": {
                            "mode": "raw",
                            "raw": "{\n    \"meal_id\": 1,\n    \"quantity\": 2\n}"
                        },
                        "url": "{{base_url}}/api/cart/items"
                    }
                }
            ]
        }
    ],
    "variable": [
        {"key": "base_url", "value": "http://127.0.0.1:8001"},
        {"key": "token", "value": "YOUR_TOKEN_HERE"}
    ]
}
```

---

## 🐛 Troubleshooting

### Issue: "Unauthenticated"
**Solution:** Make sure you're using a valid token from login/register

### Issue: "Meal not found"
**Solution:** Check available meals:
```bash
curl -X GET "http://127.0.0.1:8001/api/meals" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Issue: "Out of stock"
**Solution:** Choose a different meal or check stock:
```bash
curl -X GET "http://127.0.0.1:8001/api/meals/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | grep stock_quantity
```

### Issue: Server not running
**Solution:** Start the Laravel development server:
```bash
cd /Users/abanoubtalaat/projects/HumaVolve/grocery
php artisan serve --port=8001
```

---

## ✅ Quick Checklist

- [ ] Server is running on port 8001
- [ ] Database is running (MySQL)
- [ ] You have a valid auth token
- [ ] Replace `YOUR_TOKEN` in commands
- [ ] Use `python3 -m json.tool` for formatted output
- [ ] Check `stock_quantity` before adding to cart

---

## 📚 Full Documentation

For complete details, see:
- **NEW_FEATURES_DOCUMENTATION.md** - Complete API documentation
- **IMPLEMENTATION_SUMMARY.md** - Implementation overview
- **API_DOCUMENTATION.md** - Original API docs

Happy testing! 🚀
