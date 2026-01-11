# 🛒 Grocery Delivery Mobile App - Backend API

## 📋 Project Overview

A comprehensive Laravel-based RESTful API backend for a modern grocery and meal delivery mobile application. This API provides complete e-commerce functionality including user authentication, product management, shopping cart, favorites, AI-powered chatbot, payment processing, and delivery address management.

---

## ✨ Key Features

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
- **Advanced Filters**:
  - Filter by category and subcategory
  - Price range filtering
  - Rating filtering
  - Brand filtering
  - Featured items
  - In-stock only
- **Sorting Options** - By price, rating, popularity, date, title
- **Today's Meals** - Daily featured meals
- **Recommendations** - AI-powered meal recommendations
- **Favorites** - Save favorite meals for quick access

### 🛒 Shopping Cart
- **Add to Cart** - Add meals with quantity
- **Update Quantities** - Modify item quantities
- **Remove Items** - Delete items from cart
- **Clear Cart** - Remove all items
- **Automatic Calculations**:
  - Subtotal calculation
  - Tax calculation (10%)
  - Discount application
  - Total calculation
- **Stock Validation** - Prevents adding out-of-stock items
- **Expiry Checking** - Validates product expiry dates

### 📍 Delivery Management
- **Address CRUD** - Complete delivery address management
- **Multiple Addresses** - Save multiple delivery locations
- **Default Address** - Set and manage default delivery address
- **Address Labels** - Home, Work, Other labels
- **GPS Coordinates** - Support for location-based delivery
- **Detailed Address Fields** - Building, floor, apartment, landmarks

### 💳 Payment Processing
- **Stripe Integration** - Secure payment processing
- **Card Management** - Save, list, and delete payment cards
- **Setup Intents** - Secure card tokenization
- **Charge Saved Cards** - Process payments with saved cards

### 🤖 AI Chatbot
- **Google Gemini Integration** - AI-powered customer support
- **Meal Context** - Automatically includes all available meals
- **Natural Language** - Understands user questions about products
- **Smart Responses** - Answers questions about prices, categories, ratings

### 🔔 Notifications
- **Notification System** - User notifications management
- **Notification Settings** - Customizable notification preferences
- **Read/Unread Status** - Track notification status
- **Bulk Operations** - Mark all as read, delete multiple
- **Notification Types** - Category-based notification filtering
- **Statistics** - Unread count, recent notifications

### 📝 Smart Lists
- **Shopping Lists** - Create and manage shopping lists
- **List Items** - Add products to lists
- **List Management** - Full CRUD operations

---

## 🛠️ Technology Stack

### Backend Framework
- **Laravel 10+** - PHP framework
- **Laravel Sanctum** - API authentication
- **MySQL** - Database

### External Services
- **Google Gemini AI** - Chatbot integration
- **Stripe** - Payment processing
- **Email/Phone OTP** - Authentication

### Key Packages
- Laravel Sanctum (API tokens)
- Laravel Notifications
- HTTP Client (for external APIs)

---

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
- `otps` - One-time passwords for authentication

---

## 🚀 API Endpoints

### Authentication
```
POST   /api/auth/register          - Register new user
POST   /api/auth/login             - User login
POST   /api/auth/logout            - User logout
POST   /api/auth/forgot-password   - Request password reset
POST   /api/auth/verify-otp        - Verify OTP
POST   /api/auth/reset-password    - Reset password
GET    /api/auth/me                - Get current user
```

### Profile
```
GET    /api/profile                - Get profile
POST   /api/profile/image          - Update profile image
PUT    /api/profile/info           - Update profile info
DELETE /api/profile/image          - Delete profile image
```

### Categories & Subcategories
```
GET    /api/categories              - List categories
GET    /api/categories/{id}        - Get category
GET    /api/categories/{id}/meals  - Get meals by category
GET    /api/subcategories          - List subcategories
GET    /api/subcategories/{id}     - Get subcategory
GET    /api/subcategories/{id}/meals - Get meals by subcategory
```

### Meals
```
GET    /api/meals                  - Search & filter meals
GET    /api/meals/today            - Today's meals
GET    /api/meals/recommendations  - Meal recommendations
GET    /api/meals/{id}             - Get meal details
```

### Shopping Cart
```
GET    /api/cart                   - Get cart
POST   /api/cart/items             - Add item to cart
PUT    /api/cart/items/{itemId}    - Update cart item
DELETE /api/cart/items/{itemId}    - Remove cart item
DELETE /api/cart/clear             - Clear cart
```

### Favorites
```
GET    /api/favorites              - Get all favorites
POST   /api/favorites/{mealId}/toggle - Toggle favorite
GET    /api/favorites/{mealId}/check - Check if favorited
DELETE /api/favorites/{mealId}     - Remove favorite
```

### Addresses
```
GET    /api/addresses              - List addresses
POST   /api/addresses              - Create address
GET    /api/addresses/{id}         - Get address
PUT    /api/addresses/{id}         - Update address
DELETE /api/addresses/{id}         - Delete address
POST   /api/addresses/{id}/set-default - Set default address
```

### Payments (Stripe)
```
GET    /api/cards                  - List saved cards
POST   /api/setup-intent           - Create setup intent
POST   /api/charge-card            - Charge saved card
DELETE /api/cards/{id}             - Delete card
```

### Chatbot
```
POST   /api/chatbot                - Chat with AI about meals
```

### Notifications
```
GET    /api/notifications          - List notifications
GET    /api/notifications/stats    - Notification statistics
GET    /api/notifications/unread-count - Unread count
PUT    /api/notifications/{id}/read - Mark as read
DELETE /api/notifications/{id}     - Delete notification
PUT    /api/notifications/mark-all-read - Mark all as read
```

### Smart Lists
```
GET    /api/smart-lists            - List smart lists
POST   /api/smart-lists            - Create smart list
GET    /api/smart-lists/{id}       - Get smart list
PUT    /api/smart-lists/{id}       - Update smart list
DELETE /api/smart-lists/{id}       - Delete smart list
```

---

## 🎯 Key Capabilities

### Product Discovery
- Browse by category and subcategory
- Search with natural language
- Filter by price, rating, brand, stock
- Sort by multiple criteria
- Get personalized recommendations
- Save favorites for quick access

### Shopping Experience
- Add items to cart with validation
- Real-time stock checking
- Automatic price calculations
- Discount application
- Tax calculation
- Multiple delivery addresses

### User Experience
- Secure authentication with OTP
- Profile customization
- AI-powered chatbot support
- Notification management
- Payment card management
- Shopping list creation

---

## 🔒 Security Features

- **API Token Authentication** - Laravel Sanctum
- **Password Hashing** - Bcrypt encryption
- **OTP Verification** - Secure password reset
- **Input Validation** - Comprehensive request validation
- **SQL Injection Protection** - Eloquent ORM
- **XSS Protection** - Laravel built-in protection
- **Rate Limiting** - API request throttling
- **Secure File Uploads** - Image validation and storage

---

## 📱 Mobile App Integration

This API is designed for mobile applications (iOS/Android) and provides:

- **RESTful Architecture** - Standard HTTP methods
- **JSON Responses** - Consistent response format
- **Error Handling** - Comprehensive error messages
- **Pagination** - Efficient data loading
- **Image URLs** - Direct image access
- **Real-time Data** - Up-to-date product information

---

## 🎨 Response Format

All API responses follow a consistent format:

**Success Response:**
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error message",
    "errors": { ... }
}
```

---

## 📈 Performance Features

- **Eager Loading** - Optimized database queries
- **Indexing** - Database indexes on frequently queried fields
- **Caching** - Configuration and route caching
- **Pagination** - Efficient data pagination
- **Lazy Loading** - On-demand data loading

---

## 🧪 Testing

The API includes:
- Comprehensive validation
- Error handling
- Input sanitization
- Database constraints
- Relationship integrity

---

## 📚 Documentation

Complete API documentation available in:
- `API_DOCUMENTATION.md` - Full API reference
- `NEW_FEATURES_DOCUMENTATION.md` - Feature details
- `PROFILE_ADDRESS_COMPLETE.md` - Profile & address management
- `FAVORITES_AND_SEARCH_COMPLETE.md` - Search & favorites
- `CHATBOT_API_COMPLETE.md` - Chatbot integration

---

## 🚀 Getting Started

### Requirements
- PHP 8.1+
- MySQL 5.7+
- Composer
- Laravel 10+

### Installation
1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env`
4. Configure database and API keys
5. Run migrations: `php artisan migrate`
6. Seed database: `php artisan db:seed`
7. Start server: `php artisan serve`

### Environment Variables
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery
DB_USERNAME=root
DB_PASSWORD=

GEMINI_API_KEY=your_gemini_api_key
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

---

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

---

## 🔮 Future Enhancements

Potential features for future development:
- Order management system
- Order history
- Product reviews and ratings submission
- Wishlist functionality
- Coupon and promo code system
- Delivery tracking
- Multi-language support
- Push notifications
- Analytics dashboard

---

## 📞 Support

For API documentation and support, refer to the documentation files in the project root.

---

## 📄 License

This project is proprietary software for HumaVolve grocery delivery application.

---

## 🎉 Summary

A complete, production-ready backend API for a modern grocery delivery mobile application with:
- ✅ Full authentication system
- ✅ Comprehensive product management
- ✅ Advanced search and filtering
- ✅ Shopping cart functionality
- ✅ Payment processing
- ✅ AI-powered chatbot
- ✅ Delivery address management
- ✅ Notification system
- ✅ User profile management
- ✅ Favorites system

**Ready for mobile app integration!** 🚀📱
