# Yoryor Dating App - Production Deployment Guide

## Overview
This guide provides comprehensive instructions for deploying the Yoryor Dating App to production environments.

## Prerequisites

### System Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Redis**: 6.0 or higher
- **Nginx**: 1.18 or higher
- **SSL Certificate**: Valid SSL certificate
- **Domain**: Production domain name

### Server Specifications
- **CPU**: 4+ cores
- **RAM**: 8GB+ (16GB recommended)
- **Storage**: 100GB+ SSD
- **Bandwidth**: 1TB+ monthly

## Environment Setup

### 1. Server Configuration

#### Ubuntu 20.04+ Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.1-fpm php8.1-cli php8.1-mysql php8.1-redis php8.1-gd php8.1-curl php8.1-zip php8.1-mbstring php8.1-xml php8.1-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Database Setup

#### MySQL Configuration
```sql
-- Create database
CREATE DATABASE yoryor_dating CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'yoryor_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON yoryor_dating.* TO 'yoryor_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Redis Configuration
```bash
# Edit Redis config
sudo nano /etc/redis/redis.conf

# Set password
requirepass your_redis_password

# Restart Redis
sudo systemctl restart redis-server
```

### 3. Application Deployment

#### Clone Repository
```bash
# Create application directory
sudo mkdir -p /var/www/yoryor
sudo chown -R www-data:www-data /var/www/yoryor

# Clone repository
cd /var/www/yoryor
git clone https://github.com/your-org/yoryor-dating.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/yoryor
sudo chmod -R 755 /var/www/yoryor
```

#### Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install --production

# Build assets
npm run build
```

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure environment variables
nano .env
```

#### Environment Variables
```env
APP_NAME="Yoryor Dating"
APP_ENV=production
APP_KEY=base64:your_generated_key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yoryor_dating
DB_USERNAME=yoryor_user
DB_PASSWORD=secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Yoryor Dating"

# File Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=yoryor-dating-storage

# Video Call Service
VIDEOSDK_API_KEY=your_videosdk_key
VIDEOSDK_SECRET=your_videosdk_secret

# Push Notifications
PUSHER_APP_ID=your_pusher_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_pusher_cluster

# Payment Processing
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

# SMS Service
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_PHONE=your_twilio_phone

# Analytics
GOOGLE_ANALYTICS_ID=your_ga_id
MIXPANEL_TOKEN=your_mixpanel_token
```

### 4. Database Migration

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Nginx Configuration

#### Create Nginx Site Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/yoryor/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/yourdomain.com.crt;
    ssl_certificate_key /etc/ssl/private/yourdomain.com.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # Static Files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP Processing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
}
```

#### Enable Site
```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/yoryor /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 6. SSL Certificate

#### Using Let's Encrypt
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 7. Queue Worker Setup

#### Supervisor Configuration
```ini
[program:yoryor-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yoryor/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/yoryor/storage/logs/worker.log
stopwaitsecs=3600
```

#### Start Supervisor
```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/yoryor-worker.conf

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yoryor-worker:*
```

### 8. Cron Jobs

#### Laravel Scheduler
```bash
# Add to crontab
sudo crontab -e

# Add this line
* * * * * cd /var/www/yoryor && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Monitoring Setup

#### Log Rotation
```bash
# Create logrotate config
sudo nano /etc/logrotate.d/yoryor

# Add configuration
/var/www/yoryor/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        /bin/kill -USR1 `cat /var/run/nginx.pid 2>/dev/null` 2>/dev/null || true
    endscript
}
```

#### Health Checks
```bash
# Create health check script
sudo nano /usr/local/bin/yoryor-health-check.sh

#!/bin/bash
# Check if application is responding
curl -f http://localhost/health || exit 1
# Check database connection
php /var/www/yoryor/artisan tinker --execute="DB::connection()->getPdo();" || exit 1
# Check Redis connection
php /var/www/yoryor/artisan tinker --execute="Redis::ping();" || exit 1

# Make executable
sudo chmod +x /usr/local/bin/yoryor-health-check.sh
```

### 10. Backup Strategy

#### Database Backup
```bash
# Create backup script
sudo nano /usr/local/bin/yoryor-backup.sh

#!/bin/bash
BACKUP_DIR="/var/backups/yoryor"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="yoryor_dating"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u yoryor_user -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/yoryor/storage/app

# Keep only last 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

# Make executable
sudo chmod +x /usr/local/bin/yoryor-backup.sh

# Add to crontab
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/yoryor-backup.sh
```

### 11. Security Hardening

#### Firewall Configuration
```bash
# Install UFW
sudo apt install ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

#### PHP Security
```bash
# Edit PHP configuration
sudo nano /etc/php/8.1/fpm/php.ini

# Security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
display_errors = Off
log_errors = On
```

### 12. Performance Optimization

#### PHP-FPM Configuration
```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.1/fpm/pool.d/www.conf

# Optimize settings
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000
```

#### Redis Optimization
```bash
# Edit Redis configuration
sudo nano /etc/redis/redis.conf

# Performance settings
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 13. Deployment Checklist

#### Pre-Deployment
- [ ] Server requirements met
- [ ] SSL certificate installed
- [ ] Domain DNS configured
- [ ] Environment variables set
- [ ] Database created and configured
- [ ] Redis configured
- [ ] File storage configured

#### Deployment
- [ ] Code deployed to server
- [ ] Dependencies installed
- [ ] Database migrations run
- [ ] Storage link created
- [ ] Configuration cached
- [ ] Nginx configured
- [ ] Queue workers started
- [ ] Cron jobs configured

#### Post-Deployment
- [ ] Application accessible
- [ ] SSL certificate working
- [ ] Database connections working
- [ ] Redis connections working
- [ ] File uploads working
- [ ] Email sending working
- [ ] Queue processing working
- [ ] Monitoring configured
- [ ] Backups configured

### 14. Troubleshooting

#### Common Issues
1. **502 Bad Gateway**: Check PHP-FPM status
2. **Database Connection Error**: Verify database credentials
3. **Redis Connection Error**: Check Redis service status
4. **File Permission Issues**: Check file ownership and permissions
5. **SSL Certificate Issues**: Verify certificate installation

#### Log Locations
- Application logs: `/var/www/yoryor/storage/logs/`
- Nginx logs: `/var/log/nginx/`
- PHP-FPM logs: `/var/log/php8.1-fpm.log`
- System logs: `/var/log/syslog`

### 15. Maintenance

#### Regular Tasks
- Monitor server resources
- Check application logs
- Update dependencies
- Backup verification
- Security updates
- Performance monitoring

#### Update Procedure
```bash
# Pull latest changes
cd /var/www/yoryor
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install --production
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
sudo supervisorctl restart yoryor-worker:*
```

---

This deployment guide ensures a secure, scalable, and maintainable production environment for the Yoryor Dating App.
