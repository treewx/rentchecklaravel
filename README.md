# Rent Tracker

A Laravel application that automatically tracks rental property payments using Akahu's open banking integration.

## Features

- **User Authentication**: Secure login and registration system
- **Akahu Integration**: Connect bank accounts securely through Akahu's API
- **Property Management**: Add and manage rental properties with rent amounts and due dates
- **Automatic Rent Checking**: Automated verification of rent payments on due dates
- **Dashboard**: Overview of all properties, upcoming rent, and overdue payments
- **Real-time Status**: Track payment status (received, late, partial, pending)

## Requirements

- PHP 8.1 or higher
- Laravel 10.x
- MySQL/PostgreSQL database
- Composer
- Akahu API credentials

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd rent-tracker
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   ```

4. **Configure environment variables**
   Edit `.env` file and add:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rent_tracker
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   AKAHU_CLIENT_ID=your_akahu_client_id
   AKAHU_CLIENT_SECRET=your_akahu_client_secret
   AKAHU_API_BASE_URL=https://api.akahu.nz
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Akahu Setup

1. **Register with Akahu**: Visit [Akahu Developer Portal](https://developers.akahu.nz)
2. **Create an Application**: Set up a new application in your Akahu dashboard
3. **Configure Redirect URI**: Add `http://your-domain/akahu/callback` to your Akahu app
4. **Get Credentials**: Copy your Client ID and Client Secret to your `.env` file

## Usage

### Getting Started

1. **Register/Login**: Create an account or log in to the application
2. **Connect Akahu**: Click "Connect Akahu Account" to link your bank account
3. **Add Properties**: Add your rental properties with rent amounts and due dates
4. **Automatic Monitoring**: The system will automatically check for rent payments

### Adding a Property

1. Go to Properties > Add Property
2. Fill in:
   - Property name and address
   - Monthly rent amount
   - Rent due day (1st-31st of each month)
   - Tenant name (optional)
   - Bank account where rent is received

### Rent Checking Process

The system automatically:
- Creates rent checks for each property based on due dates
- Downloads transaction data from Akahu on and after due dates
- Matches transactions to expected rent amounts
- Updates payment status (received, late, partial, pending)
- Generates new rent checks for future months

### Dashboard Features

- **Overview**: See all properties and their current status
- **Upcoming Rent**: View rent due in the next 7 days
- **Overdue Rent**: Track late payments
- **Property Details**: Detailed view of each property and payment history

## Automation

### Scheduled Tasks

The application runs automated rent checks twice daily:
- 9:00 AM: Primary rent check
- 6:00 PM: Secondary check for late payments

To enable scheduled tasks, add to your crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Rent Check

You can also run rent checks manually:
```bash
php artisan rent:check
```

## Database Schema

### Users Table
- User authentication and profile information

### Akahu Credentials Table
- Stores encrypted Akahu API tokens and account information

### Properties Table
- Rental property details, rent amounts, and due dates

### Rent Checks Table
- Individual rent payment checks with status and transaction matching

## Security

- **Encrypted Storage**: Akahu tokens are encrypted in the database
- **Token Refresh**: Automatic token refresh when expired
- **User Authorization**: Each user can only access their own properties
- **CSRF Protection**: All forms protected against CSRF attacks

## API Integration

### Akahu API Endpoints Used
- `/oauth/authorize` - User authorization
- `/oauth/token` - Token exchange and refresh
- `/accounts` - Retrieve user's bank accounts
- `/accounts/{id}/transactions` - Get transaction history

## Troubleshooting

### Common Issues

1. **Akahu Connection Failed**
   - Check your client ID and secret
   - Verify redirect URI matches your Akahu app settings
   - Ensure your domain is accessible to Akahu

2. **No Transactions Found**
   - Verify the correct bank account is selected
   - Check if the account has recent transaction history
   - Confirm Akahu has access to the account

3. **Rent Not Detected**
   - Transaction amounts must match within $0.01
   - Transactions are searched 3 days before to 5 days after due date
   - Partial payments (80%+ of rent amount) are flagged

### Support

For technical support:
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: Set `APP_DEBUG=true` in `.env`
- Review Akahu API documentation: https://developers.akahu.nz

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.