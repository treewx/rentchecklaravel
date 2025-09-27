# Laravel Forge Deployment Guide - Rent Tracker

## 🚀 Professional Laravel Deployment with Forge + DigitalOcean

### Why Laravel Forge?
- **Zero-downtime deployments**
- **Automatic queue worker management**
- **Professional-grade server provisioning**
- **Built specifically for Laravel**
- **Industry standard used by Laravel core team**

---

## Phase 1: Account Setup

### 1. **Create Laravel Forge Account**
- Visit: https://forge.laravel.com
- Sign up for Laravel Forge ($12/month)
- Connect your DigitalOcean account

### 2. **DigitalOcean Preparation**
- Create DigitalOcean account (if needed)
- Generate API token in DigitalOcean dashboard
- Connect API token to Laravel Forge

### 3. **GitHub Repository**
- Push your code to GitHub:
  ```bash
  git remote add origin https://github.com/your-username/rent-tracker.git
  git push -u origin main
  ```

---

## Phase 2: Server Provisioning

### 1. **Create Server in Forge**
- **Server Provider**: DigitalOcean
- **Server Type**: App Server
- **Server Size**: Basic ($6/month droplet)
- **Server Name**: rent-tracker-production
- **PHP Version**: PHP 8.2
- **Database**: MySQL 8.0
- **Node.js**: Latest LTS (if needed)

### 2. **Server Features to Enable**
- ✅ **Redis** (for caching and queues)
- ✅ **MySQL** (database)
- ✅ **Nginx** (web server)
- ✅ **UFW Firewall** (security)
- ✅ **Fail2Ban** (security)

### 3. **Wait for Provisioning**
- Forge will automatically provision your server (5-10 minutes)
- Server will be configured with Laravel best practices

---

## Phase 3: Site Configuration

### 1. **Create Site in Forge**
- **Domain**: your-domain.com (or use Forge subdomain for testing)
- **Project Type**: Laravel
- **Web Directory**: `/public`
- **PHP Version**: PHP 8.2

### 2. **Connect GitHub Repository**
- **Repository**: your-username/rent-tracker
- **Branch**: main
- **Deploy When Code is Pushed**: ✅ Enable

### 3. **SSL Certificate**
- **Type**: LetsEncrypt (Free)
- **Domains**: your-domain.com, www.your-domain.com
- Forge will automatically manage SSL renewal

---

## Phase 4: Environment Configuration

### 1. **Update Environment Variables in Forge**
Copy from `.env.production` and customize:

```env
APP_NAME="Rent Tracker"
APP_ENV=production
APP_KEY=[Forge will generate this]
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (Forge auto-configures)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=[Forge provides this]

# Cache & Queue (Redis)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Rent Tracker"

# Akahu Production Credentials
AKAHU_CLIENT_ID=your-production-client-id
AKAHU_CLIENT_SECRET=your-production-client-secret
AKAHU_API_BASE_URL=https://api.akahu.io/v1
```

### 2. **Deploy Script Configuration**
Forge will use the `.forge/deploy.sh` script automatically:
- Zero-downtime deployments
- Automatic cache clearing
- Queue worker restarts
- Database migrations

---

## Phase 5: Queue Workers Setup

### 1. **Create Queue Worker in Forge**
- **Connection**: redis
- **Queue**: default
- **Processes**: 1
- **Max Memory**: 128
- **Max Time**: 3600
- **Sleep**: 3
- **Tries**: 3

### 2. **Queue Worker Configuration**
```bash
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
```

This ensures your email notifications are processed reliably in the background.

---

## Phase 6: Scheduled Tasks

### 1. **Create Scheduled Job in Forge**
- **Command**: `php artisan schedule:run`
- **User**: forge
- **Frequency**: Every minute (`* * * * *`)

### 2. **Verify Rent Checking Schedule**
Your app is configured to run rent checks:
- **9:00 AM daily** - Morning rent check
- **6:00 PM daily** - Evening rent check for late payments

---

## Phase 7: Database Setup

### 1. **Run Initial Migration**
Forge will automatically run this during first deployment:
```bash
php artisan migrate --force
```

### 2. **Database Backups**
- **Enable** daily database backups in Forge
- **Retention**: 7 days (adjustable)
- **Storage**: DigitalOcean Spaces (optional)

---

## Phase 8: Akahu Production Setup

### 1. **Akahu Developer Portal**
- Create production application
- **Redirect URI**: `https://your-domain.com/akahu/callback`
- **Webhook URL**: `https://your-domain.com/akahu/webhook` (if needed)

### 2. **Update Environment Variables**
Add your production Akahu credentials to Forge environment variables.

---

## Phase 9: Email Configuration

### 1. **Gmail Setup (Recommended)**
- Enable 2-factor authentication
- Generate App Password in Google Account settings
- Use App Password as MAIL_PASSWORD

### 2. **Test Email Functionality**
```bash
# SSH into server via Forge
php artisan tinker
Mail::raw('Test email from production', function($m) {
    $m->to('your-email@example.com')->subject('Production Test');
});
```

---

## Phase 10: Deploy & Go Live!

### 1. **Initial Deployment**
- Forge will automatically deploy when you push to GitHub
- Monitor deployment logs in Forge dashboard

### 2. **Post-Deployment Testing**
- [ ] Visit your domain - application loads
- [ ] User registration/login works
- [ ] Akahu connection functional
- [ ] Property creation successful
- [ ] Settings page accessible
- [ ] Queue worker processing jobs
- [ ] Scheduled tasks running

### 3. **Monitor & Verify**
- Check Forge server metrics
- Monitor queue job processing
- Verify email notifications
- Test rent checking functionality

---

## 🎯 Production Checklist

### Security
- [ ] SSL certificate active
- [ ] Firewall configured
- [ ] Fail2Ban enabled
- [ ] APP_DEBUG=false
- [ ] Strong database passwords
- [ ] Secure environment variables

### Performance
- [ ] Redis caching enabled
- [ ] View/route caching active
- [ ] Queue workers running
- [ ] OPcache enabled
- [ ] Gzip compression active

### Functionality
- [ ] Rent checking schedule active
- [ ] Email notifications working
- [ ] Queue processing reliable
- [ ] Database backups enabled
- [ ] Error monitoring active

### Monitoring
- [ ] Server health monitoring
- [ ] Uptime monitoring
- [ ] Queue job monitoring
- [ ] Email delivery monitoring
- [ ] Error reporting configured

---

## 💰 Total Monthly Costs

| Service | Cost |
|---------|------|
| Laravel Forge | $12 |
| DigitalOcean Basic Droplet | $6 |
| Domain (optional) | $10-15 |
| **Total** | **$18-33/month** |

---

## 🔧 Ongoing Maintenance

### Weekly Tasks
- Monitor server performance in Forge
- Check email delivery rates
- Review error logs
- Verify queue processing

### Monthly Tasks
- Review database backup integrity
- Update Laravel dependencies
- Security updates via Forge
- Performance optimization

### Forge Benefits for Maintenance
- **One-click updates** for PHP, security patches
- **Automatic SSL renewal**
- **Queue worker monitoring** with auto-restart
- **Server monitoring** with alerts
- **Database backup management**
- **Zero-downtime deployments**

---

## 🚨 Troubleshooting

### Common Issues

1. **Queue Jobs Not Processing**
   - Check queue worker status in Forge
   - Restart queue workers if needed
   - Verify Redis connection

2. **Emails Not Sending**
   - Test SMTP credentials
   - Check queue job logs
   - Verify Gmail app password

3. **Akahu Connection Issues**
   - Verify production API credentials
   - Check redirect URLs match exactly
   - Test API connectivity

4. **Scheduled Tasks Not Running**
   - Verify cron job in Forge
   - Check Laravel scheduler logs
   - Test manually: `php artisan schedule:run`

### Support Resources
- **Forge Documentation**: https://forge.laravel.com/docs
- **Laravel Documentation**: https://laravel.com/docs
- **DigitalOcean Documentation**: https://docs.digitalocean.com
- **Akahu API Documentation**: https://developers.akahu.nz

---

## 🎉 Success!

Your Laravel Rent Tracker is now running on professional-grade infrastructure with:

- ✅ **Zero-downtime deployments**
- ✅ **Automatic queue management**
- ✅ **Reliable email notifications**
- ✅ **Scheduled rent checking**
- ✅ **SSL security**
- ✅ **Database backups**
- ✅ **Performance optimization**
- ✅ **Professional monitoring**

**You're now running a production-ready SaaS application!** 🚀