# 🛒 Grocery Delivery Mobile App - Backend API

A comprehensive Laravel-based RESTful API backend for a modern grocery and meal delivery mobile application. This API provides complete e-commerce functionality including user authentication, product management, shopping cart, favorites, AI-powered chatbot, payment processing, and delivery address management.

## ✨ Features

### 🔐 Authentication & User Management
- **User Registration** - Email or phone with username, password, and terms agreement
- **Login** - Email/phone and password authentication
- **Password Reset** - OTP-based password reset via email or phone
- **Profile Management** - Update username, email, phone, country code, and profile image
- **Profile Image Upload** - Upload and manage user profile pictures

### 🍽️ Product Management
- **Categories** - Hierarchical category system
- **Subcategories** - Detailed product organization
- **Meals/Products** - Comprehensive product catalog with:
  - Product images, titles, descriptions
  - Pricing (original, discount, final price)
  - Ratings and reviews (0-5 stars with count)
  - Product size, brand, expiry dates
  - Stock quantity tracking
  - Usage instructions and features
  - Category and subcategory relationships

### 🔍 Search & Discovery
- **Full-Text Search** - Search across titles, descriptions, and brands
- **Advanced Filters** - Category, subcategory, price range, rating, brand, featured, in-stock
- **Sorting Options** - By price, rating, popularity, date, title
- **Today's Meals** - Daily featured meals
- **Recommendations** - AI-powered meal recommendations
- **Favorites** - Save favorite meals for quick access

### 🛒 Shopping Cart
- **Add to Cart** - Add meals with quantity
- **Update Quantities** - Modify item quantities
- **Remove Items** - Delete items from cart
- **Automatic Calculations** - Subtotal, tax, discount, total
- **Stock Validation** - Prevents adding out-of-stock items
- **Expiry Checking** - Validates product expiry dates

### 📍 Delivery Management
- **Address CRUD** - Complete delivery address management
- **Multiple Addresses** - Save multiple delivery locations
- **Default Address** - Set and manage default delivery address
- **GPS Coordinates** - Support for location-based delivery

### 💳 Payment Processing
- **Stripe Integration** - Secure payment processing
- **Card Management** - Save, list, and delete payment cards
- **Setup Intents** - Secure card tokenization
- **Charge Saved Cards** - Process payments with saved cards

### 🤖 AI Chatbot
- **Google Gemini Integration** - AI-powered customer support
- **Meal Context** - Automatically includes all available meals
- **Natural Language** - Understands user questions about products

### 🔔 Notifications
- **Notification System** - User notifications management
- **Notification Settings** - Customizable notification preferences
- **Read/Unread Status** - Track notification status
- **Bulk Operations** - Mark all as read, delete multiple

### 📝 Smart Lists
- **Shopping Lists** - Create and manage shopping lists
- **List Items** - Add products to lists
- **List Management** - Full CRUD operations

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd grocery
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`
```
DB_DATABASE=grocery
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Configure environment variables in `.env`
```
# Database
DB_DATABASE=grocery
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Email
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587

# SMS (Optional - for phone OTP)
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_PHONE_NUMBER=your_phone

# External APIs
GEMINI_API_KEY=your_gemini_api_key
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

6. Run migrations and seeders
```bash
php artisan migrate --seed
```

7. Start the development server
```bash
php artisan serve
```

## 📚 Documentation

For complete API documentation, see:
- **PROJECT_DESCRIPTION.md** - Comprehensive project overview
- **API_DOCUMENTATION.md** - Full API reference
- **NEW_FEATURES_DOCUMENTATION.md** - Feature details
- **CHATBOT_API_COMPLETE.md** - Chatbot integration guide

## 🔗 API Endpoints Overview

### Authentication Endpoints

#### Register
```
POST /api/auth/register
Content-Type: application/json

{
    "username": "johndoe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123",
    "agree_terms": true
}
```

#### Login
```
POST /api/auth/login
Content-Type: application/json

{
    "login": "john@example.com", // or phone number
    "password": "password123"
}
```

#### Logout
```
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Forgot Password
```
POST /api/auth/forgot-password
Content-Type: application/json

{
    "identifier": "john@example.com" // or phone number
}
```

#### Verify OTP
```
POST /api/auth/verify-otp
Content-Type: application/json

{
    "identifier": "john@example.com",
    "otp": "123456"
}
```

#### Reset Password
```
POST /api/auth/reset-password
Content-Type: application/json

{
    "identifier": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Meals Endpoints

#### Get Today's Meals
```
GET /api/meals/today
Authorization: Bearer {token}
```

Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Grilled Chicken",
            "description": "Delicious grilled chicken with herbs",
            "offer_title": "20% OFF",
            "image_url": "https://...",
            "created_at": "2026-01-10T12:00:00.000000Z"
        }
    ]
}
```

### Categories & Subcategories
```
GET /api/categories              - List all categories
GET /api/categories/{id}         - Get category details
GET /api/categories/{id}/meals   - Get meals by category
GET /api/subcategories           - List all subcategories
GET /api/subcategories/{id}      - Get subcategory details
GET /api/subcategories/{id}/meals - Get meals by subcategory
```

### Meals
```
GET /api/meals                  - Search & filter meals
GET /api/meals/today            - Today's meals
GET /api/meals/recommendations  - Meal recommendations
GET /api/meals/{id}             - Get meal details
```

### Shopping Cart
```
GET    /api/cart                - Get cart
POST   /api/cart/items          - Add item to cart
PUT    /api/cart/items/{itemId} - Update cart item
DELETE /api/cart/items/{itemId} - Remove cart item
DELETE /api/cart/clear          - Clear cart
```

### Favorites
```
GET    /api/favorites           - Get all favorites
POST   /api/favorites/{mealId}/toggle - Toggle favorite
GET    /api/favorites/{mealId}/check  - Check if favorited
DELETE /api/favorites/{mealId}   - Remove favorite
```

### Profile
```
GET    /api/profile             - Get profile
POST   /api/profile/image       - Update profile image
PUT    /api/profile/info        - Update profile info
DELETE /api/profile/image       - Delete profile image
```

### Addresses
```
GET    /api/addresses           - List addresses
POST   /api/addresses           - Create address
GET    /api/addresses/{id}      - Get address
PUT    /api/addresses/{id}      - Update address
DELETE /api/addresses/{id}      - Delete address
POST   /api/addresses/{id}/set-default - Set default address
```

### Payments (Stripe)
```
GET    /api/cards               - List saved cards
POST   /api/setup-intent        - Create setup intent
POST   /api/charge-card         - Charge saved card
DELETE /api/cards/{id}          - Delete card
```

### Chatbot
```
POST   /api/chatbot             - Chat with AI about meals
```

### Notifications
```
GET    /api/notifications       - List notifications
GET    /api/notifications/stats - Notification statistics
PUT    /api/notifications/{id}/read - Mark as read
DELETE /api/notifications/{id}  - Delete notification
```

### Smart Lists
```
GET    /api/smart-lists         - List smart lists
POST   /api/smart-lists        - Create smart list
GET    /api/smart-lists/{id}   - Get smart list
PUT    /api/smart-lists/{id}   - Update smart list
DELETE /api/smart-lists/{id}   - Delete smart list
```

## 🛠️ Technology Stack

- **Laravel 10+** - PHP framework
- **Laravel Sanctum** - API authentication
- **MySQL** - Database
- **Google Gemini AI** - Chatbot integration
- **Stripe** - Payment processing

## 📊 Database Schema

### Core Tables
- `users` - User accounts and profiles
- `categories` - Product categories
- `subcategories` - Product subcategories
- `meals` - Products/meals catalog
- `carts` - Shopping carts
- `cart_items` - Cart items
- `addresses` - Delivery addresses
- `favorites` - User favorite meals
- `notifications` - User notifications
- `user_notification_settings` - Notification preferences
- `smart_lists` - Shopping lists
- `otps` - One-time passwords

## 🔒 Security Features

- **API Token Authentication** - Laravel Sanctum
- **Password Hashing** - Bcrypt encryption
- **OTP Verification** - Secure password reset
- **Input Validation** - Comprehensive request validation
- **SQL Injection Protection** - Eloquent ORM
- **XSS Protection** - Laravel built-in protection
- **Rate Limiting** - API request throttling
- **Secure File Uploads** - Image validation and storage

## Security

- All passwords are hashed using bcrypt
- API endpoints are protected with Laravel Sanctum
- OTP expires after 10 minutes
- Input validation on all requests
- CSRF protection enabled

## Testing

Run tests with:
```bash
php artisan test
```

## 🎯 Use Cases

### For Customers
- Browse and search for groceries and meals
- Add items to cart and checkout
- Save favorite products
- Manage delivery addresses
- Chat with AI for product recommendations
- Receive order notifications

### For Business
- Manage product catalog
- Track inventory
- Process payments
- Handle orders
- Send notifications
- Analyze user preferences

## 📈 Performance Features

- **Eager Loading** - Optimized database queries
- **Indexing** - Database indexes on frequently queried fields
- **Caching** - Configuration and route caching
- **Pagination** - Efficient data pagination
- **Lazy Loading** - On-demand data loading

## 🎉 Summary

A complete, production-ready backend API for a modern grocery delivery mobile application with:

✅ Full authentication system  
✅ Comprehensive product management  
✅ Advanced search and filtering  
✅ Shopping cart functionality  
✅ Payment processing  
✅ AI-powered chatbot  
✅ Delivery address management  
✅ Notification system  
✅ User profile management  
✅ Favorites system  
✅ Smart lists  

**Ready for mobile app integration!** 🚀📱

## 📞 Support

For detailed API documentation, see:
- `PROJECT_DESCRIPTION.md` - Comprehensive project overview
- `API_DOCUMENTATION.md` - Full API reference
- `NEW_FEATURES_DOCUMENTATION.md` - Feature details
- `CHATBOT_API_COMPLETE.md` - Chatbot integration

## License

This project is proprietary software for HumaVolve grocery delivery application.
