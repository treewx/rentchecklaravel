# Rent Tracker - Production Deployment Guide

## 🚀 Deployment to DigitalOcean App Platform

### Prerequisites
- DigitalOcean account
- GitHub repository with your code
- Akahu production API credentials
- Domain name (optional)

### Phase 1: Repository Setup

1. **Initialize Git Repository** (if not already done):
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/your-username/rent-tracker.git
   git push -u origin main
   ```

2. **Environment Files**:
   - Keep `.env.production` for reference (DO NOT commit)
   - Ensure `.env` is in `.gitignore`
   - Update `.env.production` with your actual values

### Phase 2: DigitalOcean Setup

1. **Create App on DigitalOcean**:
   - Go to DigitalOcean Apps console
   - Click "Create App"
   - Connect your GitHub repository
   - Select the `main` branch

2. **Configure App Settings**:
   - Upload the `.do/app.yaml` configuration file
   - Or manually configure:
     - **Web Service**: Laravel app with PHP 8.1+
     - **Worker Service**: Queue worker
     - **Database**: PostgreSQL 15
     - **Redis**: For caching and queues
     - **Cron Job**: Laravel scheduler

3. **Environment Variables Setup**:
   ```env
   APP_NAME=Rent Tracker
   APP_ENV=production
   APP_KEY=[Generate new key]
   APP_DEBUG=false
   APP_URL=https://your-app-name.ondigitalocean.app

   # Database (Auto-configured by DigitalOcean)
   DATABASE_HOST=${db.HOSTNAME}
   DATABASE_PORT=${db.PORT}
   DATABASE_NAME=${db.DATABASE}
   DATABASE_USERNAME=${db.USERNAME}
   DATABASE_PASSWORD=${db.PASSWORD}

   # Redis (Auto-configured by DigitalOcean)
   REDIS_HOST=${redis.HOSTNAME}
   REDIS_PASSWORD=${redis.PASSWORD}
   REDIS_PORT=${redis.PORT}

   # Email Configuration
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@your-domain.com
   MAIL_FROM_NAME=Rent Tracker

   # Akahu Production Credentials
   AKAHU_CLIENT_ID=your-production-client-id
   AKAHU_CLIENT_SECRET=your-production-client-secret
   AKAHU_API_BASE_URL=https://api.akahu.io/v1

   # Cache & Queue
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis
   ```

### Phase 3: Akahu Production Setup

1. **Create Production App in Akahu Developer Portal**:
   - Visit https://developers.akahu.nz
   - Create new application for production
   - Set redirect URI to: `https://your-domain.com/akahu/callback`
   - Note down Client ID and Client Secret

2. **Update Akahu Settings**:
   - Add production domain to allowed origins
   - Configure webhook URLs if needed

### Phase 4: Database Migration

1. **Production Migration** (Automatic via deploy script):
   ```bash
   php artisan migrate --force
   php artisan db:seed --force  # If you have seeders
   ```

2. **Queue Setup** (Automatic via worker service):
   ```bash
   php artisan queue:work --sleep=3 --tries=3
   ```

### Phase 5: Email Configuration

1. **Gmail Setup** (Recommended):
   - Enable 2-factor authentication
   - Generate App Password
   - Use App Password in MAIL_PASSWORD

2. **Alternative Email Providers**:
   - **Mailgun**: Set up Mailgun account and API key
   - **SendGrid**: Configure SendGrid SMTP
   - **Postmark**: Use Postmark for transactional emails

### Phase 6: Domain Configuration (Optional)

1. **Custom Domain**:
   - Add your domain in DigitalOcean Apps
   - Update DNS records to point to DigitalOcean
   - SSL certificate will be auto-generated

2. **Update Environment**:
   ```env
   APP_URL=https://your-domain.com
   SANCTUM_STATEFUL_DOMAINS=your-domain.com
   SESSION_DOMAIN=.your-domain.com
   ```

### Phase 7: Testing & Verification

1. **Application Testing**:
   - [ ] User registration/login works
   - [ ] Akahu connection functional
   - [ ] Property creation successful
   - [ ] Rent checking system operational
   - [ ] Email notifications working
   - [ ] Settings page accessible

2. **Cron Job Verification**:
   - Check that rent:check runs twice daily
   - Verify email notifications are sent
   - Monitor application logs

3. **Performance Monitoring**:
   - Check queue worker is processing jobs
   - Monitor database performance
   - Verify Redis caching is working

### Phase 8: Ongoing Maintenance

1. **Monitoring**:
   - Set up DigitalOcean monitoring alerts
   - Monitor application logs regularly
   - Check email delivery rates

2. **Backups**:
   - Database backups are automatic with DigitalOcean
   - Consider weekly application file backups

3. **Updates**:
   - Regular security updates
   - Laravel framework updates
   - Dependency updates via Composer

### Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] HTTPS enabled
- [ ] Secure session cookies enabled
- [ ] Database credentials secured
- [ ] Email credentials secured
- [ ] Akahu credentials secured
- [ ] No sensitive data in repository
- [ ] Failed job monitoring set up
- [ ] Error reporting configured

### Estimated Costs

| Service | Monthly Cost |
|---------|-------------|
| DigitalOcean App Platform Basic | $5-12 |
| PostgreSQL Database | Included |
| Redis Cache | Included |
| Domain (optional) | $10-15 |
| **Total** | **$5-27/month** |

### Support & Troubleshooting

1. **Common Issues**:
   - Queue jobs not processing → Check worker service logs
   - Emails not sending → Verify SMTP credentials
   - Akahu connection failing → Check API credentials and URLs
   - Database connection issues → Verify environment variables

2. **Debugging**:
   ```bash
   # Check application logs
   php artisan log:clear

   # Test queue system
   php artisan queue:work --once

   # Test email configuration
   php artisan tinker
   Mail::raw('Test email', function($message) {
       $message->to('test@example.com')->subject('Test');
   });
   ```

3. **Performance Optimization**:
   - Enable OPcache in production
   - Use Redis for sessions and caching
   - Optimize database queries
   - Monitor queue performance

### Rollback Plan

1. **If deployment fails**:
   - Revert to previous GitHub commit
   - DigitalOcean will auto-deploy the previous version

2. **Database rollback**:
   - Use DigitalOcean database backups
   - Manual migration rollback if needed

### Success Metrics

- [ ] Application loads successfully
- [ ] User registration/login functional
- [ ] Akahu integration working
- [ ] Automated rent checking operational
- [ ] Email notifications sending
- [ ] Scheduled tasks running
- [ ] Queue processing working
- [ ] Performance acceptable (<2s page loads)

---

**Ready for Production!** 🎉

Once all steps are completed, your Rent Tracker application will be running securely in production with automatic rent checking, email notifications, and proper monitoring.