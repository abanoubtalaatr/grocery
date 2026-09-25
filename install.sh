#!/bin/bash

# Grocery Laravel Application - Installation Script
# This script helps you set up the Laravel application quickly

echo "🛒 Grocery Laravel Application - Installation"
echo "=============================================="
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   Visit: https://getcomposer.org/download/"
    exit 1
fi

echo "✅ Composer found"

# Check if PHP is installed
if ! command -v php &> /dev/null
then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

echo "✅ PHP found ($(php -v | head -n 1))"

# Install Composer dependencies
echo ""
echo "📦 Installing Composer dependencies..."
composer install

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

echo "✅ Composer dependencies installed"

# Copy .env file if it doesn't exist
if [ ! -f .env ]; then
    echo ""
    echo "📄 Creating .env file..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo ""
    echo "ℹ️  .env file already exists"
fi

# Generate application key
echo ""
echo "🔑 Generating application key..."
php artisan key:generate

# Create database
echo ""
echo "🗄️  Database Setup"
echo "=================="
read -p "Enter database name (default: grocery): " db_name
db_name=${db_name:-grocery}

read -p "Enter database username (default: root): " db_user
db_user=${db_user:-root}

read -sp "Enter database password (press enter if none): " db_pass
echo ""

# Update .env file with database credentials
sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env
rm .env.bak 2>/dev/null

echo "✅ Database configuration updated"

# Ask if user wants to create database
read -p "Do you want to create the database now? (y/n): " create_db
if [ "$create_db" = "y" ] || [ "$create_db" = "Y" ]; then
    if command -v mysql &> /dev/null
    then
        mysql -u$db_user -p$db_pass -e "CREATE DATABASE IF NOT EXISTS $db_name;"
        echo "✅ Database created"
    else
        echo "⚠️  MySQL command not found. Please create the database manually."
    fi
fi

# Run migrations
echo ""
read -p "Do you want to run migrations now? (y/n): " run_migrations
if [ "$run_migrations" = "y" ] || [ "$run_migrations" = "Y" ]; then
    echo "🔄 Running migrations..."
    php artisan migrate
    
    if [ $? -eq 0 ]; then
        echo "✅ Migrations completed"
        
        # Ask about seeders
        read -p "Do you want to seed the database with sample data? (y/n): " run_seeders
        if [ "$run_seeders" = "y" ] || [ "$run_seeders" = "Y" ]; then
            echo "🌱 Seeding database..."
            php artisan db:seed
            echo "✅ Database seeded with sample data"
        fi
    else
        echo "❌ Migration failed. Please check your database configuration."
    fi
fi

# Create storage link
echo ""
echo "🔗 Creating storage link..."
php artisan storage:link
echo "✅ Storage link created"

# Clear caches
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo "✅ Caches cleared"

echo ""
echo "✨ Installation Complete!"
echo "========================"
echo ""
echo "🚀 To start the development server, run:"
echo "   php artisan serve"
echo ""
echo "📚 API will be available at:"
echo "   http://localhost:8000/api"
echo ""
echo "📖 Check API_DOCUMENTATION.md for endpoint details"
echo "📖 Check README.md for more information"
echo ""
echo "⚙️  Don't forget to configure:"
echo "   - Email settings (MAIL_*) in .env"
echo "   - SMS settings (TWILIO_*) in .env if using SMS"
echo ""
echo "Happy coding! 🎉"
