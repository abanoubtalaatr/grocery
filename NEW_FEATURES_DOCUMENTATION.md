# 🎉 New Features - API Documentation

## Overview
This document describes the newly added features to the Grocery API:
1. **Subcategories API** - Browse products by subcategories
2. **Enhanced Meal Details** - Detailed product information including ratings, size, expiry, etc.
3. **Shopping Cart Feature** - Full cart management functionality

---

## 📦 1. Subcategories API

### Get All Subcategories
Retrieve list of all active subcategories.

**Endpoint:** `GET /api/subcategories`

**Headers:**
```
Authorization: Bearer {your_token}
Accept: application/json
```

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `category_id` | integer | No | Filter subcategories by category ID |

**Examples:**
```bash
# Get all subcategories
GET /api/subcategories

# Get subcategories for a specific category
GET /api/subcategories?category_id=1
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Subcategories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Leafy Greens",
            "slug": "leafy-greens",
            "description": "Fresh lettuce, spinach, kale and more",
            "image_url": null,
            "order": 1,
            "category": {
                "id": 1,
                "name": "Vegetables",
                "slug": "vegetables"
            },
            "meals_count": 5,
            "created_at": "2026-01-10T10:45:50.000000Z"
        }
    ]
}
```

---

### Get Single Subcategory
Retrieve detailed information about a specific subcategory including sample meals.

**Endpoint:** `GET /api/subcategories/{id}`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Subcategory retrieved successfully",
    "data": {
        "id": 1,
        "name": "Leafy Greens",
        "slug": "leafy-greens",
        "description": "Fresh lettuce, spinach, kale and more",
        "image_url": null,
        "order": 1,
        "is_active": true,
        "category": {
            "id": 1,
            "name": "Vegetables",
            "slug": "vegetables"
        },
        "meals": [
            {
                "id": 1,
                "title": "Fresh Organic Salad Mix",
                "slug": "fresh-organic-salad-mix",
                "image_url": "https://...",
                "price": "12.99",
                "discount_price": "10.39",
                "final_price": 10.39,
                "rating": "4.85",
                "is_featured": true
            }
        ],
        "meals_count": 12,
        "created_at": "2026-01-10T10:45:50.000000Z",
        "updated_at": "2026-01-10T10:45:50.000000Z"
    }
}
```

---

### Get Meals by Subcategory
Retrieve all meals in a specific subcategory.

**Endpoint:** `GET /api/subcategories/{id}/meals`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `featured` | boolean | No | Filter by featured meals only |

**Response:** Returns list of meals with enhanced details.

---

## 🍽️ 2. Enhanced Meal Details

### Get Meal Details
Now returns comprehensive product information.

**Endpoint:** `GET /api/meals/{id}`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Meal retrieved successfully",
    "data": {
        "id": 1,
        "title": "Fresh Organic Salad Mix",
        "slug": "fresh-organic-salad-mix",
        "description": "A delightful mix of fresh organic vegetables...",
        "image_url": "https://images.unsplash.com/photo-xxx",
        "offer_title": "20% OFF Today",
        
        // PRICING
        "price": "12.99",
        "discount_price": "10.39",
        "final_price": 10.39,
        "has_offer": true,
        
        // RATING
        "rating": "4.85",
        "rating_count": 243,
        
        // PRODUCT DETAILS
        "size": "500g",
        "brand": "Fresh Farms",
        "includes": "1 pack (500g)",
        "how_to_use": "Wash thoroughly before use. Perfect for salads, wraps, and sandwiches.",
        "features": "Organic, Fresh, Locally sourced",
        
        // EXPIRY & AVAILABILITY
        "expiry_date": "2026-01-15",
        "days_until_expiry": 5,
        "is_expired": false,
        
        // STOCK
        "stock_quantity": 67,
        "in_stock": true,
        "sold_count": 234,
        
        // STATUS
        "is_featured": true,
        "is_available": true,
        "available_date": "2026-01-10",
        
        // RELATIONSHIPS
        "category": {
            "id": 1,
            "name": "Vegetables",
            "slug": "vegetables"
        },
        "subcategory": {
            "id": 1,
            "name": "Leafy Greens",
            "slug": "leafy-greens"
        },
        
        "created_at": "2026-01-10T07:58:25.000000Z",
        "updated_at": "2026-01-10T07:58:25.000000Z"
    }
}
```

### New Meal Fields Explanation

| Field | Type | Description |
|-------|------|-------------|
| `rating` | decimal | Average rating (0-5 stars) |
| `rating_count` | integer | Number of ratings received |
| `size` | string | Product size (e.g., 500g, 1kg, 2L) |
| `brand` | string | Product brand name |
| `includes` | string | What's included in the package |
| `how_to_use` | text | Instructions on how to use the product |
| `features` | text | Product features and highlights |
| `expiry_date` | date | Product expiry date |
| `days_until_expiry` | integer | Days remaining until expiry (null if no expiry) |
| `is_expired` | boolean | Whether the product has expired |
| `stock_quantity` | integer | Available stock quantity |
| `in_stock` | boolean | Whether product is in stock |
| `sold_count` | integer | Number of times product has been sold |
| `subcategory` | object | Subcategory information (if assigned) |

---

## 🛒 3. Shopping Cart Feature

### Get Cart
Retrieve the user's active shopping cart.

**Endpoint:** `GET /api/cart`

**Headers:**
```
Authorization: Bearer {your_token}
Accept: application/json
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Cart retrieved successfully",
    "data": {
        "id": 1,
        "status": "active",
        "items": [
            {
                "id": 1,
                "meal": {
                    "id": 1,
                    "title": "Fresh Organic Salad Mix",
                    "slug": "fresh-organic-salad-mix",
                    "image_url": "https://...",
                    "price": "12.99",
                    "discount_price": "10.39",
                    "final_price": 10.39,
                    "rating": "4.85",
                    "size": "500g",
                    "brand": "Fresh Farms",
                    "stock_quantity": 67,
                    "is_available": true,
                    "in_stock": true,
                    "category": {
                        "id": 1,
                        "name": "Vegetables"
                    },
                    "subcategory": null
                },
                "quantity": 2,
                "unit_price": "10.39",
                "discount_amount": "5.20",
                "subtotal": "15.58"
            }
        ],
        "item_count": 2,
        "subtotal": "15.58",
        "tax": "1.56",
        "discount": "5.20",
        "total": "11.94",
        "is_empty": false,
        "created_at": "2026-01-10T10:46:42.000000Z",
        "updated_at": "2026-01-10T10:46:42.000000Z"
    }
}
```

---

### Add Item to Cart
Add a meal to the shopping cart.

**Endpoint:** `POST /api/cart/items`

**Headers:**
```
Authorization: Bearer {your_token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
    "meal_id": 1,
    "quantity": 2
}
```

**Validation Rules:**
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `meal_id` | integer | Yes | Must exist in meals table |
| `quantity` | integer | Yes | Min: 1, Max: 100 |

**Response (200 OK):**
Returns the updated cart with the new item added.

**Error Responses:**
```json
// Product unavailable
{
    "success": false,
    "message": "This meal is currently unavailable"
}

// Out of stock
{
    "success": false,
    "message": "This meal is out of stock"
}

// Insufficient stock
{
    "success": false,
    "message": "Only 5 items available in stock"
}

// Product expired
{
    "success": false,
    "message": "This meal has expired"
}
```

---

### Update Cart Item
Update the quantity of an item in the cart.

**Endpoint:** `PUT /api/cart/items/{itemId}`

**Request Body:**
```json
{
    "quantity": 3
}
```

**Response (200 OK):**
Returns the updated cart.

---

### Remove Item from Cart
Remove a specific item from the cart.

**Endpoint:** `DELETE /api/cart/items/{itemId}`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Item removed from cart successfully",
    "data": {
        // Updated cart data
    }
}
```

---

### Clear Cart
Remove all items from the cart.

**Endpoint:** `DELETE /api/cart/clear`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Cart cleared successfully",
    "data": {
        "id": 1,
        "status": "active",
        "items": [],
        "item_count": 0,
        "subtotal": "0.00",
        "tax": "0.00",
        "discount": "0.00",
        "total": "0.00",
        "is_empty": true,
        "created_at": "2026-01-10T10:46:42.000000Z",
        "updated_at": "2026-01-10T10:50:12.000000Z"
    }
}
```

---

## 🔄 Cart Calculation Logic

The cart automatically calculates:

1. **Subtotal**: Sum of all item subtotals
2. **Tax**: 10% of subtotal (configurable)
3. **Discount**: Sum of all discount amounts from items
4. **Total**: `Subtotal + Tax - Discount`

### Per-Item Calculation:
- **Unit Price**: Final price of the meal (after discount)
- **Discount Amount**: `(Original Price - Discount Price) × Quantity`
- **Subtotal**: `Unit Price × Quantity`

---

## 📱 Mobile App Integration Examples

### Example 1: Product Detail Screen
```javascript
// Fetch enhanced meal details
const mealResponse = await fetch('/api/meals/1', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const meal = await mealResponse.json();

// Display:
// - meal.data.image_url
// - meal.data.title
// - meal.data.rating (show stars)
// - meal.data.rating_count (e.g., "(243 reviews)")
// - meal.data.price (crossed out if discount exists)
// - meal.data.final_price (prominent)
// - meal.data.size
// - meal.data.brand
// - meal.data.includes
// - meal.data.how_to_use
// - meal.data.features
// - meal.data.expiry_date (show countdown if close)
// - meal.data.stock_quantity (show "Only X left" if low)
```

### Example 2: Add to Cart
```javascript
// Add item to cart
const addToCart = async (mealId, quantity) => {
    try {
        const response = await fetch('/api/cart/items', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                meal_id: mealId,
                quantity: quantity
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            showToast(`${quantity} item(s) added to cart`);
            // Update cart badge
            updateCartBadge(result.data.item_count);
        } else {
            // Show error message
            showError(result.message);
        }
    } catch (error) {
        showError('Failed to add item to cart');
    }
};
```

### Example 3: Cart Screen
```javascript
// Fetch and display cart
const loadCart = async () => {
    const response = await fetch('/api/cart', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const cart = await response.json();
    
    if (cart.data.is_empty) {
        showEmptyState();
    } else {
        renderCartItems(cart.data.items);
        displaySummary({
            subtotal: cart.data.subtotal,
            tax: cart.data.tax,
            discount: cart.data.discount,
            total: cart.data.total
        });
    }
};

// Update quantity
const updateQuantity = async (itemId, newQuantity) => {
    const response = await fetch(`/api/cart/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ quantity: newQuantity })
    });
    
    const result = await response.json();
    if (result.success) {
        loadCart(); // Refresh cart
    }
};

// Remove item
const removeItem = async (itemId) => {
    const response = await fetch(`/api/cart/items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const result = await response.json();
    if (result.success) {
        loadCart(); // Refresh cart
    }
};
```

### Example 4: Browse by Subcategory
```javascript
// Show subcategory navigation
const loadSubcategories = async (categoryId) => {
    const response = await fetch(`/api/subcategories?category_id=${categoryId}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const subcategories = await response.json();
    renderSubcategoryTabs(subcategories.data);
};

// Load meals for selected subcategory
const loadSubcategoryMeals = async (subcategoryId) => {
    const response = await fetch(`/api/subcategories/${subcategoryId}/meals`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const meals = await response.json();
    renderMealGrid(meals.data.meals);
};
```

---

## 🎨 UI/UX Recommendations

### Product Details
- Display rating as stars (★★★★☆)
- Show "Only X left" badge when stock is low (< 10)
- Add expiry countdown if within 3 days
- Highlight discounts prominently
- Show size and brand clearly
- Use expandable sections for "How to Use" and "Features"

### Shopping Cart
- Show product thumbnails
- Allow quantity adjustment with +/- buttons
- Show savings from discounts
- Display item count badge on cart icon
- Add "Proceed to Checkout" button
- Show empty cart illustration when cart is empty

### Subcategories
- Use horizontal scrollable tabs for subcategories
- Show meal count for each subcategory
- Add icons/images for visual appeal

---

## 📊 Complete API Endpoint List

### Authentication
- `POST /api/auth/register` - Register user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/verify-otp` - Verify OTP
- `POST /api/auth/reset-password` - Reset password
- `GET /api/auth/me` - Get current user

### Meals
- `GET /api/meals/today` - Get today's meals
- `GET /api/meals/recommendations` - Get recommendations
- `GET /api/meals` - Get all meals
- `GET /api/meals/{id}` - Get meal details (ENHANCED)

### Categories
- `GET /api/categories` - Get all categories
- `GET /api/categories/{id}` - Get category details

### Subcategories (NEW)
- `GET /api/subcategories` - Get all subcategories
- `GET /api/subcategories/{id}` - Get subcategory details
- `GET /api/subcategories/{id}/meals` - Get meals by subcategory

### Shopping Cart (NEW)
- `GET /api/cart` - Get cart
- `POST /api/cart/items` - Add item to cart
- `PUT /api/cart/items/{itemId}` - Update cart item
- `DELETE /api/cart/items/{itemId}` - Remove cart item
- `DELETE /api/cart/clear` - Clear cart

### Health
- `GET /api/health` - API health check

---

## ✅ Testing Checklist

### Subcategories
- [ ] List all subcategories
- [ ] Filter subcategories by category
- [ ] View single subcategory with meals
- [ ] View meals by subcategory

### Enhanced Meal Details
- [ ] View meal with all new fields
- [ ] Check rating display
- [ ] Verify expiry date calculation
- [ ] Confirm stock status
- [ ] Test with/without subcategory

### Shopping Cart
- [ ] Get empty cart
- [ ] Add item to cart
- [ ] Add same item twice (quantity increases)
- [ ] Add multiple different items
- [ ] Update item quantity
- [ ] Remove item from cart
- [ ] Clear entire cart
- [ ] Test stock validation
- [ ] Test expiry validation
- [ ] Verify cart calculations

---

## 🎉 Summary

You now have a complete e-commerce solution with:

✅ **Product Organization** - Categories and Subcategories  
✅ **Detailed Product Info** - Ratings, size, expiry, usage instructions  
✅ **Shopping Cart** - Full cart management with automatic calculations  
✅ **Stock Management** - Real-time stock tracking  
✅ **Pricing** - Discounts, taxes, and final price calculations  
✅ **User Experience** - Recommendations, ratings, and reviews  

Ready to integrate into your mobile application! 🚀
