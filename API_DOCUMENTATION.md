# Grocery API - Postman Collection Guide

This guide helps you test the Grocery API using Postman or any REST client.

## Base URL
```
http://localhost:8000/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your_token_here}
```

---

## API Endpoints

### 1. Health Check
Check if the API is running.

**Endpoint:** `GET /api/health`

**Response:**
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2026-01-10T12:00:00.000000Z"
}
```

---

### 2. Register User

**Endpoint:** `POST /api/auth/register`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "username": "johndoe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123",
    "agree_terms": true
}
```

**Note:** Either `email` or `phone` is required, but you can provide both.

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "created_at": "2026-01-10T12:00:00.000000Z"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxx"
    }
}
```

---

### 3. Login

**Endpoint:** `POST /api/auth/login`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "login": "john@example.com",
    "password": "password123"
}
```

**Note:** `login` can be either email or phone number.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "phone": "+1234567890"
        },
        "token": "2|xxxxxxxxxxxxxxxxxxxxx"
    }
}
```

**Save the token for subsequent requests!**

---

### 4. Get Current User

**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "johndoe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "email_verified": false,
            "phone_verified": false,
            "created_at": "2026-01-10T12:00:00.000000Z"
        }
    }
}
```

---

### 5. Logout

**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Logout successful"
}
```

---

### 6. Forgot Password (Request OTP)

**Endpoint:** `POST /api/auth/forgot-password`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "identifier": "john@example.com"
}
```

**Note:** `identifier` can be email or phone number.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "OTP sent successfully. Please check your email or phone."
}
```

---

### 7. Verify OTP

**Endpoint:** `POST /api/auth/verify-otp`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "identifier": "john@example.com",
    "otp": "123456"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "OTP verified successfully"
}
```

---

### 8. Reset Password

**Endpoint:** `POST /api/auth/reset-password`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "identifier": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Password reset successfully"
}
```

---

### 9. Get Today's Meals

**Endpoint:** `GET /api/meals/today`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Today's meals retrieved successfully",
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
            "category": {
                "id": 1,
                "name": "Vegetables"
            },
            "available_date": "2026-01-10",
            "created_at": "2026-01-10T12:00:00.000000Z"
        }
    ]
}
```

---

### 10. Get Meal Recommendations

**Endpoint:** `GET /api/meals/recommendations`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Query Parameters (Optional):**
- `limit` - Number of recommendations to return (default: 10)

**Example:**
- `GET /api/meals/recommendations?limit=5`

**Response (200 OK):**
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

**Recommendation Reasons:**
- `Featured with special offer` - Featured meal with discount
- `Featured meal` - Featured meal without discount
- `Special offer` - Regular meal with discount
- `Popular choice` - Regular meal

**Algorithm:**
The recommendations API uses an intelligent algorithm that:
1. Prioritizes featured meals with special offers (50% of results)
2. Includes random meals from different categories (remaining 50%)
3. Shuffles the results for variety
4. Returns only available meals

---

### 11. Get All Meals

**Endpoint:** `GET /api/meals`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Query Parameters (Optional):**
- `category_id` - Filter by category ID
- `featured` - true/false to filter featured meals

**Examples:**
- `GET /api/meals?category_id=1`
- `GET /api/meals?featured=true`

**Response:** Similar structure to Today's Meals

---

### 12. Get Single Meal

**Endpoint:** `GET /api/meals/{id}`

**Headers:**
```
Authorization: Bearer {your_token}
```

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
        "price": "12.99",
        "discount_price": "10.39",
        "final_price": 10.39,
        "has_offer": true,
        "is_featured": true,
        "is_available": true,
        "category": {
            "id": 1,
            "name": "Vegetables",
            "slug": "vegetables"
        },
        "available_date": "2026-01-10",
        "created_at": "2026-01-10T12:00:00.000000Z",
        "updated_at": "2026-01-10T12:00:00.000000Z"
    }
}
```

---

### 13. Get All Categories

**Endpoint:** `GET /api/categories`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Categories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Vegetables",
            "slug": "vegetables",
            "description": "Fresh organic vegetables",
            "image_url": "https://images.unsplash.com/photo-xxx",
            "meals_count": 5,
            "sort_order": 1,
            "created_at": "2026-01-10T12:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Fruits",
            "slug": "fruits",
            "description": "Fresh seasonal fruits",
            "image_url": "https://images.unsplash.com/photo-xxx",
            "meals_count": 3,
            "sort_order": 2,
            "created_at": "2026-01-10T12:00:00.000000Z"
        }
    ]
}
```

---

### 14. Get Single Category with Meals

**Endpoint:** `GET /api/categories/{id}`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Category retrieved successfully",
    "data": {
        "id": 1,
        "name": "Vegetables",
        "slug": "vegetables",
        "description": "Fresh organic vegetables",
        "image_url": "https://images.unsplash.com/photo-xxx",
        "sort_order": 1,
        "meals": [
            {
                "id": 1,
                "title": "Fresh Organic Salad Mix",
                "slug": "fresh-organic-salad-mix",
                "description": "A delightful mix...",
                "image_url": "https://...",
                "offer_title": "20% OFF Today",
                "price": "12.99",
                "discount_price": "10.39",
                "final_price": 10.39,
                "has_offer": true,
                "is_featured": true
            }
        ],
        "created_at": "2026-01-10T12:00:00.000000Z",
        "updated_at": "2026-01-10T12:00:00.000000Z"
    }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ]
    }
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Login failed",
    "errors": {
        "login": [
            "The provided credentials are incorrect."
        ]
    }
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Meal not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Failed to retrieve meals",
    "error": "Error details..."
}
```

---

## Testing Flow

1. **Start Server:** `php artisan serve`
2. **Register:** Create a new account
3. **Save Token:** Copy the token from register/login response
4. **Test Meals:** Use the token to access meals endpoints
5. **Test Categories:** Use the token to access categories endpoints
6. **Test Password Reset:** Try forgot password flow

---

## Postman Tips

1. **Environment Variables:**
   - Create a variable `base_url` = `http://localhost:8000/api`
   - Create a variable `token` and update it after login
   - Use `{{base_url}}` and `{{token}}` in requests

2. **Authorization:**
   - Type: Bearer Token
   - Token: `{{token}}`

3. **Pre-request Script (for auto token):**
   ```javascript
   pm.environment.set("token", pm.response.json().data.token);
   ```

---

## Mobile App Integration Notes

- All responses follow consistent JSON structure
- Images use full URLs (can be from storage or external)
- Dates are in ISO 8601 format
- Use token in Authorization header: `Bearer {token}`
- Handle errors using the `success` boolean flag
- OTP expires in 10 minutes (configurable)
- Phone numbers should include country code (+1234567890)
