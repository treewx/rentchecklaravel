# Quick Start Guide

## ✅ Installation Complete!

PHP 8.3 and Composer have been successfully installed and configured. Laravel is now running!

## 🚀 Laravel Development Server

Your Laravel application is currently running at:
**http://localhost:8000**

## 🛠️ Available Commands

Use these batch files for development:

- `php.bat` - Run PHP commands
- `composer.bat` - Run Composer commands
- `artisan.bat` - Run Laravel Artisan commands

## 📝 Next Steps

1. **Database Setup**: Configure your database in `.env` file
2. **Run Migrations**: `./artisan.bat migrate`
3. **Akahu Setup**: Get your API credentials from https://developers.akahu.nz
4. **Configure Environment**: Update `.env` with your Akahu credentials

## 🗄️ Database Configuration

Edit `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rent_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 🔑 Akahu API Configuration

Add your Akahu credentials to `.env`:

```env
AKAHU_CLIENT_ID=your_client_id
AKAHU_CLIENT_SECRET=your_client_secret
AKAHU_API_BASE_URL=https://api.akahu.nz
```

## 📊 Run Database Migrations

Once your database is configured:

```bash
./artisan.bat migrate
```

## 🎯 Features Available

- ✅ User Authentication (Login/Register)
- ✅ Akahu Bank Account Integration
- ✅ Property Management
- ✅ Automated Rent Checking
- ✅ Dashboard with Rent Status
- ✅ Payment History Tracking

## 🔧 Development Commands

- Start server: `./artisan.bat serve`
- Clear cache: `./artisan.bat cache:clear`
- Check rent: `./artisan.bat rent:check`
- Run tests: `./php.bat vendor/bin/phpunit`

## 📖 Full Documentation

See `README.md` for complete documentation and feature details.

---

**Your Rent Tracker application is ready! 🎉**