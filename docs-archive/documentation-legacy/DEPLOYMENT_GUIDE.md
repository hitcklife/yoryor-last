# YorYor Deployment Guide

## Table of Contents
- [Overview](#overview)
- [System Requirements](#system-requirements)
- [Environment Setup](#environment-setup)
- [Dependencies Installation](#dependencies-installation)
- [Database Setup](#database-setup)
- [Storage Configuration](#storage-configuration)
- [Queue Workers](#queue-workers)
- [WebSocket Server](#websocket-server)
- [Production Optimizations](#production-optimizations)
- [Monitoring Setup](#monitoring-setup)
- [Deployment Checklist](#deployment-checklist)
- [Troubleshooting](#troubleshooting)

---

## Overview

This guide covers deploying YorYor to a production environment. YorYor is built on Laravel 12 and requires specific infrastructure components for full functionality.

### Deployment Architecture

```
┌─────────────────┐
│   Load Balancer │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
┌───▼───┐ ┌──▼────┐
│ Web   │ │ Web   │  (Multiple instances)
│ Server│ │ Server│
└───┬───┘ └──┬────┘
    │        │
    └────┬───┘
         │
┌────────▼────────┐
│  Application    │
│  (Laravel)      │
└────────┬────────┘
         │
    ┌────┴────┐
    │         │
┌───▼───┐ ┌──▼────┐ ┌─────────┐
│Database│ │ Redis │ │ Storage │
│ (MySQL)│ │ Cache │ │ (R2/S3) │
└────────┘ └───────┘ └─────────┘
         │
    ┌────┴────┐
┌───▼───┐ ┌──▼────┐
│ Queue │ │Reverb │
│Workers│ │WebSock│
└───────┘ └───────┘
```

---

## System Requirements

### Server Requirements

**Minimum:**
- **OS**: Ubuntu 20.04 LTS or later / Debian 11+ / CentOS 8+
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Storage**: 20 GB SSD
- **Network**: 100 Mbps

**Recommended (Production):**
- **OS**: Ubuntu 22.04 LTS
- **CPU**: 4+ cores
- **RAM**: 8+ GB
- **Storage**: 50+ GB SSD
- **Network**: 1 Gbps

### Software Requirements

**Required:**
- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx 1.18+ or Apache 2.4+

**Optional:**
- **Redis**: 6.0+ (recommended for caching and queues)
- **Supervisor**: For process management
- **Certbot**: For SSL certificates (Let's Encrypt)

### PHP Extensions

Required extensions:
```bash
php8.2-cli
php8.2-fpm
php8.2-mysql
php8.2-pgsql
php8.2-sqlite3
php8.2-redis
php8.2-mbstring
php8.2-xml
php8.2-curl
php8.2-zip
php8.2-gd
php8.2-intl
php8.2-bcmath
php8.2-soap
php8.2-imagick
```

---

## Environment Setup

### 1. Clone Repository

```bash
cd /var/www
git clone https://github.com/yourusername/yoryor.git
cd yoryor
```

### 2. Create Environment File

```bash
cp .env.example .env
nano .env
```

### 3. Configure Environment Variables

#### Application Settings

```env
APP_NAME="YorYor"
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_WITH_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://yoryor.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=info
```

**Important**: Generate `APP_KEY`:
```bash
php artisan key:generate
```

#### Database Configuration

**MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yoryor
DB_USERNAME=yoryor_user
DB_PASSWORD=STRONG_PASSWORD_HERE
```

**PostgreSQL (Alternative):**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=yoryor
DB_USERNAME=yoryor_user
DB_PASSWORD=STRONG_PASSWORD_HERE
```

#### Session & Cache

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=redis
CACHE_PREFIX=yoryor_

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
```

#### Queue Configuration

```env
QUEUE_CONNECTION=redis
```

#### Broadcasting (WebSocket)

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=yoryor-production
REVERB_APP_KEY=your-secure-key-here
REVERB_APP_SECRET=your-secure-secret-here
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=yoryor.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

#### Mail Configuration

**Using SMTP (e.g., SendGrid, Mailgun):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yoryor.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Storage Configuration (Cloudflare R2)

```env
FILESYSTEM_DISK=cloudflare-r2

CLOUDFLARE_R2_ACCESS_KEY_ID=your-access-key-id
CLOUDFLARE_R2_SECRET_ACCESS_KEY=your-secret-access-key
CLOUDFLARE_R2_DEFAULT_REGION=auto
CLOUDFLARE_R2_BUCKET=yoryor-production
CLOUDFLARE_R2_URL=https://your-bucket-url.r2.cloudflarestorage.com
CLOUDFLARE_R2_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
CLOUDFLARE_R2_USE_PATH_STYLE_ENDPOINT=true
```

#### Third-Party Services

**Agora (Video Calling):**
```env
AGORA_APP_ID=your-agora-app-id
AGORA_APP_CERTIFICATE=your-agora-certificate
```

**VideoSDK (Primary Video Provider):**
```env
VIDEOSDK_API_KEY=your-videosdk-api-key
VIDEOSDK_SECRET_KEY=your-videosdk-secret
VIDEOSDK_API_ENDPOINT=https://api.videosdk.live/v2
```

**Google OAuth (Optional):**
```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://yoryor.com/auth/google/callback
```

---

## Dependencies Installation

### 1. Install PHP Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

**Flags explained:**
- `--optimize-autoloader`: Optimizes class map for production
- `--no-dev`: Excludes development dependencies

### 2. Install Node Dependencies

```bash
npm ci
```

**Note**: `npm ci` is preferred over `npm install` for production as it uses the lock file strictly.

### 3. Build Frontend Assets

```bash
npm run build
```

This compiles and minifies JavaScript and CSS files.

---

## Database Setup

### 1. Create Database

**MySQL:**
```bash
mysql -u root -p
```

```sql
CREATE DATABASE yoryor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'yoryor_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON yoryor.* TO 'yoryor_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**PostgreSQL:**
```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE yoryor;
CREATE USER yoryor_user WITH PASSWORD 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE yoryor TO yoryor_user;
\q
```

### 2. Run Migrations

```bash
php artisan migrate --force
```

**Note**: `--force` flag is required in production.

### 3. Seed Database (Optional)

For initial data (countries, etc.):
```bash
php artisan db:seed --class=CountrySeeder --force
```

**Warning**: Do NOT run DatabaseSeeder in production (it creates test data).

---

## Storage Configuration

### 1. Create Storage Directories

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

### 2. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Replace `www-data` with your web server user if different.

### 3. Configure Cloudflare R2

1. Create R2 bucket in Cloudflare dashboard
2. Generate API credentials
3. Add credentials to `.env` (see Environment Setup)
4. Test connection:

```bash
php artisan tinker
```

```php
Storage::disk('cloudflare-r2')->put('test.txt', 'Hello World');
Storage::disk('cloudflare-r2')->exists('test.txt'); // Should return true
Storage::disk('cloudflare-r2')->delete('test.txt');
```

---

## Queue Workers

YorYor uses queues for background jobs (emails, notifications, image processing).

### 1. Install Supervisor

**Ubuntu/Debian:**
```bash
sudo apt-get install supervisor
```

**CentOS:**
```bash
sudo yum install supervisor
```

### 2. Create Supervisor Configuration

Create file: `/etc/supervisor/conf.d/yoryor-worker.conf`

```ini
[program:yoryor-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yoryor/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
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

### 3. Start Queue Workers

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yoryor-worker:*
```

### 4. Monitor Queue Workers

```bash
sudo supervisorctl status yoryor-worker:*
```

### Alternative: Laravel Horizon (Advanced)

For advanced queue management with dashboard:

```bash
php artisan horizon:install
php artisan horizon:publish
```

Create Supervisor config: `/etc/supervisor/conf.d/yoryor-horizon.conf`

```ini
[program:yoryor-horizon]
process_name=%(program_name)s
command=php /var/www/yoryor/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/yoryor/storage/logs/horizon.log
stopwaitsecs=3600
```

Start Horizon:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yoryor-horizon
```

Access Horizon dashboard: `https://yoryor.com/horizon`

---

## WebSocket Server

YorYor uses Laravel Reverb for WebSockets.

### 1. Create Reverb Supervisor Configuration

Create file: `/etc/supervisor/conf.d/yoryor-reverb.conf`

```ini
[program:yoryor-reverb]
process_name=%(program_name)s
command=php /var/www/yoryor/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/yoryor/storage/logs/reverb.log
stopwaitsecs=10
```

### 2. Start Reverb Server

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yoryor-reverb
```

### 3. Configure Nginx for WebSocket Proxy

Add to Nginx site configuration:

```nginx
# WebSocket proxy for Reverb
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

Reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## Production Optimizations

### 1. Optimize Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**Important**: Run these after every deployment. Clear with:
```bash
php artisan optimize:clear
```

### 2. Optimize Autoloader

Already done if you used `composer install --optimize-autoloader`.

If not:
```bash
composer dump-autoload -o
```

### 3. Enable OPcache

Edit PHP configuration (`/etc/php/8.2/fpm/php.ini`):

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

### 4. Configure Nginx

Example Nginx configuration (`/etc/nginx/sites-available/yoryor`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yoryor.com www.yoryor.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yoryor.com www.yoryor.com;

    root /var/www/yoryor/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yoryor.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yoryor.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    # Client body size (for uploads)
    client_max_body_size 10M;

    # Logs
    access_log /var/log/nginx/yoryor-access.log;
    error_log /var/log/nginx/yoryor-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # WebSocket proxy (added earlier)
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_read_timeout 86400;
    }
}
```

Enable site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/yoryor /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Setup SSL Certificate

Using Let's Encrypt:

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d yoryor.com -d www.yoryor.com
```

Auto-renewal:
```bash
sudo certbot renew --dry-run
```

### 6. Configure PHP-FPM

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

## Monitoring Setup

### 1. Laravel Telescope (Development/Staging Only)

**DO NOT enable in production** (performance impact).

For staging environment:
```bash
php artisan telescope:install
php artisan migrate
```

Access: `https://staging.yoryor.com/telescope`

### 2. Laravel Pulse

Real-time performance monitoring.

```bash
php artisan vendor:publish --tag=pulse-config
php artisan migrate
```

Access: `https://yoryor.com/pulse`

Protect with authentication in routes:
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Pulse::routes();
});
```

### 3. Laravel Horizon (Queue Monitoring)

Already covered in Queue Workers section.

Access: `https://yoryor.com/horizon`

### 4. Server Monitoring

**Install monitoring tools:**

```bash
# Install htop for process monitoring
sudo apt-get install htop

# Install netdata for real-time monitoring
bash <(curl -Ss https://my-netdata.io/kickstart.sh)
```

Access Netdata: `http://your-server-ip:19999`

### 5. Application Logging

Configure log rotation in `/etc/logrotate.d/yoryor`:

```
/var/www/yoryor/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## Deployment Checklist

### Pre-Deployment

- [ ] Code reviewed and tested
- [ ] Database migrations tested
- [ ] .env file configured correctly
- [ ] Third-party API keys added
- [ ] SSL certificate obtained
- [ ] Backup strategy in place
- [ ] Rollback plan prepared

### Deployment Steps

- [ ] Pull latest code from repository
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm ci && npm run build`
- [ ] Run `php artisan migrate --force`
- [ ] Run optimization commands:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  ```
- [ ] Restart services:
  ```bash
  sudo supervisorctl restart all
  sudo systemctl reload php8.2-fpm
  sudo systemctl reload nginx
  ```
- [ ] Clear application cache (if needed):
  ```bash
  php artisan cache:clear
  ```
- [ ] Test critical features:
  - User registration/login
  - Profile creation
  - Messaging
  - Video calling
  - Payment processing

### Post-Deployment

- [ ] Monitor logs for errors
- [ ] Check queue processing
- [ ] Verify WebSocket connections
- [ ] Test API endpoints
- [ ] Monitor server resources
- [ ] Notify team of deployment

---

## Troubleshooting

### Queue Workers Not Processing

**Check worker status:**
```bash
sudo supervisorctl status yoryor-worker:*
```

**Restart workers:**
```bash
sudo supervisorctl restart yoryor-worker:*
```

**Check logs:**
```bash
tail -f storage/logs/worker.log
```

### WebSocket Connection Failed

**Check Reverb is running:**
```bash
sudo supervisorctl status yoryor-reverb
```

**Test WebSocket connection:**
```bash
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/app/yoryor-key
```

**Check Nginx proxy configuration**

### Database Connection Error

**Test database connection:**
```bash
php artisan tinker
```

```php
DB::connection()->getPdo();
```

**Check database credentials in .env**

**Verify database server is running:**
```bash
sudo systemctl status mysql
```

### Storage/Upload Errors

**Check permissions:**
```bash
ls -la storage/
```

**Fix permissions:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Test cloud storage:**
```bash
php artisan tinker
```

```php
Storage::disk('cloudflare-r2')->put('test.txt', 'test');
```

### Performance Issues

**Check server resources:**
```bash
htop
free -h
df -h
```

**Check slow queries:**
```bash
php artisan pulse
```

**Clear caches:**
```bash
php artisan optimize:clear
redis-cli FLUSHDB
```

**Restart services:**
```bash
sudo systemctl restart php8.2-fpm nginx redis-server mysql
```

### 500 Internal Server Error

**Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

**Check Nginx error logs:**
```bash
tail -f /var/log/nginx/yoryor-error.log
```

**Check PHP-FPM logs:**
```bash
tail -f /var/log/php8.2-fpm.log
```

**Enable debug mode temporarily (then disable!):**
```env
APP_DEBUG=true
```

---

## Automated Deployment Script

Create `deploy.sh`:

```bash
#!/bin/bash

set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo supervisorctl restart all
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "Deployment completed successfully!"
```

Make executable:
```bash
chmod +x deploy.sh
```

Run deployment:
```bash
./deploy.sh
```

---

## Maintenance Mode

**Enable maintenance mode:**
```bash
php artisan down --secret="maintenance-bypass-token"
```

Access site during maintenance: `https://yoryor.com/maintenance-bypass-token`

**Disable maintenance mode:**
```bash
php artisan up
```

---

## Backup Strategy

### Database Backup

Create daily backup script (`/usr/local/bin/backup-yoryor-db.sh`):

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/yoryor"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
FILENAME="yoryor_db_$DATE.sql.gz"

mkdir -p $BACKUP_DIR

mysqldump -u yoryor_user -p'PASSWORD' yoryor | gzip > "$BACKUP_DIR/$FILENAME"

# Keep only last 30 days
find $BACKUP_DIR -name "yoryor_db_*.sql.gz" -mtime +30 -delete

echo "Backup completed: $FILENAME"
```

Add to crontab:
```bash
sudo crontab -e
```

```cron
0 2 * * * /usr/local/bin/backup-yoryor-db.sh
```

### Application Backup

Backup uploaded files (if not using cloud storage):
```bash
tar -czf yoryor_files_$(date +%Y-%m-%d).tar.gz storage/app/public
```

---

*This deployment guide ensures YorYor runs smoothly in production with optimal performance and reliability.*

Last Updated: September 2025