# 🎛️ Admin Dashboard Documentation

## Overview

A comprehensive Filament admin panel has been set up to manage all models in your grocery delivery application.

## Access

**URL:** `http://your-domain.com/admin`

**Login:** Only users with `is_admin = true` can access the admin dashboard. Regular users will be denied access.

### Default Admin Account
A default admin account has been created:
- **Username:** `admin`
- **Email:** `admin@example.com`
- **Password:** `admin123`

⚠️ **Important:** Change the default password immediately after first login!

## Features

### 📊 Dashboard
- **Statistics Overview Widget** showing:
  - Total Users
  - Total Meals
  - Total Orders
  - Total Reviews
  - Pending Orders
  - Active Users

### 📦 Resources Available

#### Products Group
1. **Categories** (`/admin/categories`)
   - Manage product categories
   - Image upload support
   - Active/Inactive status
   - Sort order management

2. **Subcategories** (`/admin/subcategories`)
   - Manage subcategories linked to categories
   - Category filtering
   - Image upload support

3. **Meals** (`/admin/meals`)
   - Complete meal/product management
   - Category and subcategory relationships
   - Image upload
   - Pricing (price, discount price)
   - Stock management
   - Featured and availability toggles
   - Rating and review counts

4. **Reviews** (`/admin/reviews`)
   - Customer review management
   - Approval system
   - Rating filtering
   - Image upload support

#### Sales Group
1. **Orders** (`/admin/orders`)
   - Order management
   - Status tracking (pending, confirmed, preparing, ready, out for delivery, delivered, cancelled)
   - Payment method tracking
   - Delivery type management
   - User and address relationships

#### Users Group
1. **Users** (`/admin/users`)
   - User account management
   - Profile image upload
   - Email and phone verification status
   - Active/Inactive status
   - **Admin status management** (toggle to grant/revoke admin access)
   - Password management

#### Additional Resources
- **Addresses** - Delivery addresses
- **Carts** - Shopping carts
- **Cart Items** - Cart items
- **Order Items** - Order line items
- **Favorites** - User favorites
- **Notifications** - User notifications
- **Settings** - Application settings
- **Static Pages** - Static content pages
- **FAQs** - Frequently asked questions
- **Contact Messages** - Contact form submissions
- **Smart Lists** - User smart lists
- **OTPs** - One-time passwords

## Key Features

### 🔍 Search & Filtering
- Full-text search on most resources
- Advanced filters (status, category, dates, etc.)
- Toggleable columns for custom views

### 📝 Forms
- Auto-slug generation for categories, subcategories, and meals
- Relationship dropdowns (category → subcategory, user → address)
- Image upload with preview
- Rich text areas for descriptions
- Validation and required fields

### 📊 Tables
- Sortable columns
- Searchable fields
- Badge indicators for status
- Color-coded statuses (orders, reviews)
- Image columns with circular display
- Bulk actions (delete, etc.)

### 🎨 UI/UX
- Modern, responsive design
- Navigation groups for organization
- Icon-based navigation
- Color-coded status badges
- Intuitive forms and tables

## Creating an Admin User

### Method 1: Using Artisan Command (Recommended)

```bash
php artisan admin:create-user
```

Or with options:
```bash
php artisan admin:create-user --username=admin --email=admin@example.com --password=securepassword
```

The command will prompt you for:
- Username (must be unique)
- Email (must be unique)
- Password (minimum 8 characters)

### Method 2: Through Admin Dashboard

1. Login as an existing admin user
2. Navigate to `/admin/users/create`
3. Fill in the user details
4. **Enable the "Admin" toggle** in the form
5. Save the user

### Method 3: Using Laravel Tinker

```bash
php artisan tinker
```

```php
User::create([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('securepassword'),
    'is_admin' => true,  // Important: Set this to true
    'is_active' => true,
    'email_verified' => true,
    'agree_terms' => true,
    'email_verified_at' => now(),
    'country_code' => '+20',
]);
```

### Method 4: Update Existing User to Admin

```bash
php artisan tinker
```

```php
$user = User::where('email', 'user@example.com')->first();
$user->update(['is_admin' => true]);
```

## Customization

### Adding New Resources
Resources are auto-discovered from `app/Filament/Resources/`. To add a new resource:

```bash
php artisan make:filament-resource YourModel --generate
```

### Customizing Widgets
Widgets are in `app/Filament/Widgets/`. The main stats widget is `StatsOverview.php`.

### Navigation Groups
Resources are organized into groups:
- **Products** - Categories, Subcategories, Meals, Reviews
- **Sales** - Orders
- **Users** - Users

You can customize groups in each resource file:
```php
protected static ?string $navigationGroup = 'Your Group';
protected static ?int $navigationSort = 1;
```

## Security

- **Admin-only access:** Only users with `is_admin = true` can access the dashboard
- All routes are protected by authentication middleware
- Custom `EnsureUserIsAdmin` middleware checks admin status
- Uses Laravel's session-based authentication
- CSRF protection enabled
- Password hashing for user creation/updates
- Non-admin users attempting to access `/admin` will receive a 403 Forbidden error

## Next Steps

1. **Access the dashboard** at `/admin`
2. **Login** with the admin account:
   - Username: `admin`
   - Password: `admin123`
3. **Change the default password** immediately
4. **Explore** the resources and features
5. **Create additional admin users** if needed
6. **Customize** as needed for your specific requirements

## Support

For Filament documentation, visit: https://filamentphp.com/docs
