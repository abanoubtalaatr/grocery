# 🚀 Quick Start Guide - Grocery API

Get your Laravel Grocery API up and running in 5 minutes!

---

## Prerequisites Check ✓

Before starting, verify you have:
- [ ] PHP 8.1 or higher (`php -v`)
- [ ] Composer (`composer -v`)
- [ ] MySQL/MariaDB running
- [ ] Terminal/Command prompt access

---

## Option 1: Automated Installation (Recommended) ⚡

### Step 1: Run Installation Script
```bash
chmod +x install.sh
./install.sh
```

The script will:
1. ✅ Install all dependencies
2. ✅ Create `.env` file
3. ✅ Generate application key
4. ✅ Configure database
5. ✅ Run migrations
6. ✅ Seed sample data
7. ✅ Setup storage links

### Step 2: Start Server
```bash
php artisan serve
```

### Step 3: Test API
Visit: http://localhost:8000/api/health

You should see:
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2026-01-10T..."
}
```

✨ **Done! Your API is ready!**

---

## Option 2: Manual Installation 📝

### Step 1: Install Dependencies
```bash
composer install
```

### Step 2: Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Step 3: Configure Database
Edit `.env`:
```env
DB_DATABASE=grocery
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Create Database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE grocery;
exit;
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Seed Sample Data (Optional)
```bash
php artisan db:seed
```

### Step 7: Create Storage Link
```bash
php artisan storage:link
```

### Step 8: Start Server
```bash
php artisan serve
```

✨ **Done! Visit http://localhost:8000/api/health**

---

## First API Test 🧪

### 1. Register a User

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "agree_terms": true
  }'
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {...},
        "token": "1|xxxxx..."
    }
}
```

**💡 Save the token!**

---

### 2. Login

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "login": "john@example.com",
    "password": "password123"
  }'
```

---

### 3. Get Today's Meals

**Request:**
```bash
curl -X GET http://localhost:8000/api/meals/today \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### 4. Get Categories

**Request:**
```bash
curl -X GET http://localhost:8000/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Using Postman 📮

### Setup Postman Environment

1. **Create Environment**: "Grocery API"
2. **Add Variables**:
   - `base_url` = `http://localhost:8000/api`
   - `token` = (leave empty for now)

### Import Collection

Create requests with:
- **URL**: `{{base_url}}/auth/register`
- **Authorization**: Bearer Token → `{{token}}`

### Auto-save Token

In **Login** request, add **Tests**:
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("token", response.data.token);
}
```

---

## Testing the Complete Flow 🔄

### Full Authentication Flow

```bash
# 1. Register
POST /api/auth/register

# 2. Login (get token)
POST /api/auth/login

# 3. Get user info
GET /api/auth/me (with token)

# 4. Browse meals
GET /api/meals/today (with token)

# 5. Browse categories
GET /api/categories (with token)

# 6. Logout
POST /api/auth/logout (with token)
```

### Password Reset Flow

```bash
# 1. Request OTP
POST /api/auth/forgot-password
Body: { "identifier": "john@example.com" }

# 2. Check email/logs for OTP

# 3. Verify OTP
POST /api/auth/verify-otp
Body: { "identifier": "john@example.com", "otp": "123456" }

# 4. Reset password
POST /api/auth/reset-password
Body: {
    "identifier": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}

# 5. Login with new password
POST /api/auth/login
```

---

## Email/SMS Testing (Development) 📧

### Option 1: Log Driver (No SMTP needed)
`.env`:
```env
MAIL_MAILER=log
```

Check OTPs in: `storage/logs/laravel.log`

### Option 2: Mailtrap (Free)
1. Sign up at https://mailtrap.io
2. Get credentials
3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

---

## Troubleshooting 🔧

### Error: "Connection refused"
```bash
# Check if MySQL is running
# Mac:
brew services start mysql

# Linux:
sudo systemctl start mysql

# Windows:
# Start MySQL from Services
```

### Error: "Table not found"
```bash
php artisan migrate:fresh --seed
```

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "Permission denied"
```bash
chmod -R 775 storage bootstrap/cache
```

### OTP not showing
```bash
# Use log driver for testing
MAIL_MAILER=log

# Check logs
tail -f storage/logs/laravel.log
```

---

## Next Steps 🎯

1. ✅ **Read Documentation**
   - `README.md` - Overview
   - `API_DOCUMENTATION.md` - All endpoints
   - `PROJECT_SUMMARY.md` - Architecture

2. ✅ **Configure Email**
   - Setup SMTP for real emails
   - Or use log driver for testing

3. ✅ **Test All Endpoints**
   - Use Postman collection
   - Test error cases

4. ✅ **Customize**
   - Add more categories
   - Add more meals
   - Modify validation rules

5. ✅ **Deploy**
   - Choose hosting (AWS, DigitalOcean, etc.)
   - Setup production environment
   - Configure domain

---

## Common Commands 📋

```bash
# Start server
php artisan serve

# Run migrations
php artisan migrate

# Reset database
php artisan migrate:fresh --seed

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# View routes
php artisan route:list

# Create storage link
php artisan storage:link

# Run scheduler (cron jobs)
php artisan schedule:run

# Run queue worker
php artisan queue:work
```

---

## API Endpoint Quick Reference 📌

### Public
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/forgot-password
POST   /api/auth/verify-otp
POST   /api/auth/reset-password
GET    /api/health
```

### Protected (Requires Bearer Token)
```
POST   /api/auth/logout
GET    /api/auth/me
GET    /api/meals/today
GET    /api/meals
GET    /api/meals/{id}
GET    /api/categories
GET    /api/categories/{id}
```

---

## Pro Tips 💡

1. **Use environment variables** for all sensitive data
2. **Enable logging** during development (`APP_DEBUG=true`)
3. **Test with Postman** before mobile integration
4. **Use log mail driver** for OTP testing
5. **Clear caches** after config changes
6. **Backup database** before major changes
7. **Use git** for version control
8. **Read logs** when errors occur

---

## Getting Help 🆘

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Debug Mode
```env
APP_DEBUG=true
```

### Test Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Verify Routes
```bash
php artisan route:list | grep api
```

---

## Mobile App Integration 📱

When ready to connect your mobile app:

1. **Base URL**: `http://your-server.com/api`
2. **Save Token**: Store securely (Keychain/Keystore)
3. **Add Bearer Token**: Include in all protected requests
4. **Handle Errors**: Check `success` field in responses
5. **Token Expiry**: Re-login if token invalid
6. **Network Errors**: Show friendly messages

---

## Success Checklist ✅

- [ ] Server starts successfully
- [ ] Database connected
- [ ] Migrations completed
- [ ] Sample data seeded
- [ ] Health endpoint works
- [ ] Can register user
- [ ] Can login
- [ ] Can get meals (with token)
- [ ] Can get categories (with token)
- [ ] OTP system working

---

## 🎉 You're Ready!

Your Grocery API is now running and ready for mobile app integration!

**Next**: Check `API_DOCUMENTATION.md` for detailed endpoint information.

---

**Need Help?** Review the `INSTALLATION.md` file for detailed troubleshooting.

**Ready to Deploy?** Check the production deployment section in `PROJECT_SUMMARY.md`.

---

Happy Coding! 🚀
