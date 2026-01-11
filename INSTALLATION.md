# Installation Guide

This guide provides detailed instructions for setting up the Grocery Laravel application.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP >= 8.1** with required extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
- **Composer** (latest version)
- **MySQL >= 5.7** or **MariaDB >= 10.3**
- **Node.js & NPM** (for asset compilation, if needed)

## Quick Installation

### Option 1: Using Installation Script (Recommended)

```bash
# Make the installation script executable
chmod +x install.sh

# Run the installation script
./install.sh
```

The script will guide you through:
1. Installing dependencies
2. Configuring environment variables
3. Setting up the database
4. Running migrations and seeders

### Option 2: Manual Installation

#### Step 1: Install Dependencies

```bash
composer install
```

#### Step 2: Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 3: Configure Database

Edit the `.env` file and update the database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Step 4: Create Database

Create a new MySQL database:

```sql
CREATE DATABASE grocery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 5: Run Migrations

```bash
php artisan migrate
```

#### Step 6: Seed Database (Optional)

To populate the database with sample categories and meals:

```bash
php artisan db:seed
```

#### Step 7: Create Storage Link

```bash
php artisan storage:link
```

#### Step 8: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Configuration

### Email Configuration

Update the `.env` file with your email settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@grocery.app"
MAIL_FROM_NAME="${APP_NAME}"
```

**For Development:** Consider using [Mailtrap](https://mailtrap.io/) or [MailHog](https://github.com/mailhog/MailHog)

**For Production:** Use services like:
- SendGrid
- Amazon SES
- Mailgun
- Postmark

### SMS Configuration (Optional)

For phone-based OTP, configure Twilio in `.env`:

```env
TWILIO_SID=your_twilio_account_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=+1234567890
```

Sign up at [Twilio](https://www.twilio.com/) to get credentials.

### OTP Configuration

Customize OTP settings in `.env`:

```env
OTP_EXPIRY_MINUTES=10
OTP_LENGTH=6
```

## Running the Application

### Development Server

Start the Laravel development server:

```bash
php artisan serve
```

The API will be available at: `http://localhost:8000/api`

### Custom Host/Port

```bash
php artisan serve --host=0.0.0.0 --port=8080
```

## Verifying Installation

### 1. Check API Health

Visit: `http://localhost:8000/api/health`

Expected response:
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2026-01-10T12:00:00.000000Z"
}
```

### 2. Test Registration

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "agree_terms": true
  }'
```

### 3. View Sample Data

If you seeded the database, you should be able to access categories and meals after logging in.

## Database Schema

The application creates the following tables:

- `users` - User accounts
- `otps` - One-time passwords for verification
- `categories` - Meal categories
- `meals` - Meal items
- `personal_access_tokens` - API tokens (Laravel Sanctum)
- `password_reset_tokens` - Password reset tokens
- `failed_jobs` - Failed queue jobs

## Troubleshooting

### Issue: "Key length too long" error during migration

**Solution:** Add this to `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

### Issue: Storage link already exists

**Solution:** Remove the existing link and recreate:

```bash
rm public/storage
php artisan storage:link
```

### Issue: Permission denied errors

**Solution:** Set proper permissions:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Class not found errors

**Solution:** Regenerate autoload files:

```bash
composer dump-autoload
php artisan clear-compiled
```

### Issue: OTP emails not sending

**Solutions:**
1. Check your `.env` mail configuration
2. Verify SMTP credentials
3. Check Laravel logs: `storage/logs/laravel.log`
4. For development, use `log` driver: `MAIL_MAILER=log`

## Production Deployment

### 1. Environment

Set production environment in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
```

### 2. Optimize for Production

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Security Checklist

- [ ] Change `APP_KEY` to a secure random value
- [ ] Set `APP_DEBUG=false`
- [ ] Use HTTPS
- [ ] Configure proper CORS settings
- [ ] Set up proper file permissions
- [ ] Enable rate limiting
- [ ] Configure secure session settings
- [ ] Set up proper backup strategy
- [ ] Enable logging and monitoring

### 4. Queue Workers

For better performance, set up queue workers:

```bash
# In .env
QUEUE_CONNECTION=database

# Run queue worker
php artisan queue:work --tries=3
```

### 5. Task Scheduling

Add to crontab for automated OTP cleanup:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

### Run Tests

```bash
php artisan test
```

### API Testing

Use the provided `API_DOCUMENTATION.md` with:
- Postman
- Insomnia
- cURL
- Any REST client

## Getting Help

- Check the `README.md` for API documentation
- Review `API_DOCUMENTATION.md` for endpoint details
- Check Laravel logs: `storage/logs/laravel.log`
- Review Laravel documentation: https://laravel.com/docs

## Next Steps

1. ✅ Customize the email templates
2. ✅ Add more meal categories
3. ✅ Implement order system (if needed)
4. ✅ Add payment integration
5. ✅ Implement push notifications
6. ✅ Add admin panel
7. ✅ Set up automated backups
8. ✅ Configure monitoring and alerts

Happy Development! 🚀
