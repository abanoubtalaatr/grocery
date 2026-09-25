# Laravel Server Deployment Checklist

## Common Issues When Deploying to Linux Servers

### ✅ Fixed Issues

1. **Namespace Case Sensitivity** ✅ FIXED
   - `ReviewController` had `namespace App\Http\Controllers\APi;` (lowercase 'i')
   - Fixed to: `namespace App\Http\Controllers\Api;`
   - **Impact**: Would cause "Class not found" errors on Linux servers

2. **Import Case Sensitivity** ✅ FIXED
   - Routes had `APi\SettingController` and `API\NotificationSettingsController`
   - Fixed to: `Api\SettingController` and `Api\NotificationSettingsController`
   - **Impact**: Would cause autoloader failures on Linux servers

---

## Critical Checklist for Server Deployment

### 1. **File & Directory Permissions**
```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 2. **Environment Configuration**
- [ ] Copy `.env.example` to `.env` on server
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`: `php artisan key:generate`
- [ ] Update database credentials
- [ ] Update cache/session drivers for production

### 3. **Composer Autoload**
```bash
# Regenerate autoload files
composer dump-autoload -o
```

### 4. **Laravel Optimization**
```bash
# Clear and cache config
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Optimize autoloader
php artisan optimize
```

### 5. **Case Sensitivity Checks**

#### Check Namespaces
```bash
# Find all namespace declarations
grep -r "namespace.*Api\|APi\|API" app/ --include="*.php"
```

#### Check Directory Names
- Ensure all directories match namespace case exactly
- `app/Http/Controllers/Api/` (not `API/` or `APi/`)

### 6. **Database Migrations**
```bash
# Run migrations
php artisan migrate --force
```

### 7. **Storage Link**
```bash
# Create symbolic link for storage
php artisan storage:link
```

### 8. **Server Configuration**

#### Apache (.htaccess)
Ensure `public/.htaccess` exists and is configured correctly.

#### Nginx
Ensure proper configuration for Laravel:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 9. **PHP Requirements**
- [ ] PHP >= 8.1
- [ ] Required PHP extensions installed:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo

### 10. **Common Errors & Solutions**

#### "Class not found" errors
- **Cause**: Case sensitivity or autoload issues
- **Fix**: Run `composer dump-autoload -o`

#### "500 Internal Server Error"
- **Cause**: Permissions or .env issues
- **Fix**: Check logs in `storage/logs/laravel.log`

#### "Route not found"
- **Cause**: Route cache issues
- **Fix**: Run `php artisan route:clear && php artisan route:cache`

#### "Permission denied" errors
- **Cause**: Wrong file permissions
- **Fix**: Set proper permissions (see #1 above)

---

## Pre-Deployment Script

Create a deployment script:

```bash
#!/bin/bash
# deploy.sh

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Deployment complete!"
```

---

## Post-Deployment Verification

1. Test API endpoints
2. Check error logs: `tail -f storage/logs/laravel.log`
3. Verify database connections
4. Test file uploads (check storage permissions)
5. Verify authentication works
6. Check scheduled tasks (if using cron)

---

## Quick Fix Commands

```bash
# Fix all common issues at once
composer dump-autoload -o
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache
php artisan optimize
chmod -R 775 storage bootstrap/cache
```

---

## Notes

- **Windows/Mac are case-insensitive** - code may work locally but fail on Linux
- **Always test on Linux-like environment** before deploying
- **Use consistent naming**: Always use `Api` (capital A, lowercase i) for namespaces
- **Check git case sensitivity**: Git may not detect case-only renames
