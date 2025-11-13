# YorYor Production Deployment Guide

## Table of Contents
- [Overview](#overview)
- [System Requirements](#system-requirements)
- [Deployment Architecture](#deployment-architecture)
- [Environment Setup](#environment-setup)
- [Server Configuration](#server-configuration)
- [Dependencies Installation](#dependencies-installation)
- [Database Setup](#database-setup)
- [Storage Configuration](#storage-configuration)
- [Queue Workers Setup](#queue-workers-setup)
- [WebSocket Server](#websocket-server)
- [Production Optimizations](#production-optimizations)
- [Monitoring Setup](#monitoring-setup)
- [SSL/HTTPS Setup](#ssl-https-setup)
- [Backup Strategies](#backup-strategies)
- [Zero-Downtime Deployment](#zero-downtime-deployment)
- [Health Checks](#health-checks)
- [Rollback Procedures](#rollback-procedures)
- [Deployment Checklist](#deployment-checklist)
- [Troubleshooting](#troubleshooting)

---

## Overview

This guide covers deploying YorYor to a production environment. YorYor is built on Laravel 12 with Livewire 3 and requires specific infrastructure components for full functionality.

**Tech Stack:**
- Laravel 12 (PHP 8.2+)
- MySQL 8.0+ / PostgreSQL 13+
- Redis 6.0+
- Laravel Reverb (WebSocket server)
- Cloudflare R2 (Media storage)
- Nginx 1.18+ / Apache 2.4+

**Core Features Requiring Infrastructure:**
- Real-time messaging (Laravel Reverb on port 8080)
- Video calling (VideoSDK.live + Agora RTC backup)
- Media storage (Cloudflare R2)
- Background jobs (Queue workers)
- Caching (Redis)

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
- **RAM**: 8+ GB (16GB for high traffic)
- **Storage**: 50+ GB SSD (100GB+ recommended)
- **Network**: 1 Gbps
- **Bandwidth**: 1TB+ monthly

### Software Requirements

**Required:**
- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx 1.18+ or Apache 2.4+

**Recommended:**
- **Redis**: 6.0+ (highly recommended for caching and queues)
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

Installation:
```bash
sudo apt update
sudo apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath php8.2-soap php8.2-imagick
```

---

## Deployment Architecture

### Production Architecture Diagram

```
                        ┌─────────────────┐
                        │  Load Balancer  │
                        │   (Optional)    │
                        └────────┬────────┘
                                 │
                        ┌────────┴────────┐
                        │                 │
                   ┌────▼─────┐     ┌────▼─────┐
                   │ Web      │     │ Web      │  (Multiple instances)
                   │ Server   │     │ Server   │
                   │ (Nginx)  │     │ (Nginx)  │
                   └────┬─────┘     └────┬─────┘
                        │                │
                        └────────┬───────┘
                                 │
                        ┌────────▼────────┐
                        │  Application    │
                        │  (Laravel 12)   │
                        └────────┬────────┘
                                 │
                    ┌────────────┼────────────┐
                    │            │            │
           ┌────────▼───┐  ┌────▼────┐  ┌───▼──────┐
           │ Database   │  │ Redis   │  │ Storage  │
           │ (MySQL 8)  │  │ Cache   │  │ (R2)     │
           └────────────┘  └────┬────┘  └──────────┘
                                │
                    ┌───────────┼───────────┐
           ┌────────▼───┐  ┌────▼────────┐ │
           │ Queue      │  │ Laravel     │ │
           │ Workers    │  │ Reverb      │ │
           │ (Supervisor)│  │ (Port 8080) │ │
           └────────────┘  └─────────────┘ │
```

### Component Breakdown

**Frontend Layer:**
- Load Balancer (optional, for multi-server setup)
- Nginx/Apache web servers
- SSL termination

**Application Layer:**
- Laravel application
- Livewire full-stack components
- API endpoints (Sanctum auth)

**Data Layer:**
- MySQL/PostgreSQL (primary database)
- Redis (cache, sessions, queues)
- Cloudflare R2 (media storage)

**Background Services:**
- Queue workers (Supervisor managed)
- Laravel Reverb (WebSocket server)
- Cron scheduler

---

## Environment Setup

### 1. Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/yoryor
cd /var/www/yoryor

# Clone repository
git clone https://github.com/yourusername/yoryor.git .

# Set ownership
sudo chown -R www-data:www-data /var/www/yoryor
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
LOG_DEPRECATIONS_CHANNEL=null
```

**Important**: Generate `APP_KEY`:
```bash
php artisan key:generate
```

#### Database Configuration

**MySQL (Recommended):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yoryor
DB_USERNAME=yoryor_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Connection pooling
DB_POOL_MIN=2
DB_POOL_MAX=10
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
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.yoryor.com

CACHE_STORE=redis
CACHE_PREFIX=yoryor_

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_secure_redis_password
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_DB=0
REDIS_CACHE_DB=1
```

#### Queue Configuration

```env
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids
```

#### Broadcasting (Laravel Reverb)

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=yoryor-production
REVERB_APP_KEY=your-secure-key-here-32chars
REVERB_APP_SECRET=your-secure-secret-here-32chars
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https

# Client-side configuration (for Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=yoryor.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

**Note**: Reverb replaces Pusher for WebSocket broadcasting.

#### Mail Configuration

**Using SMTP (SendGrid example):**
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

**Using Mailgun:**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.yoryor.com
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
```

#### Storage Configuration (Cloudflare R2)

```env
FILESYSTEM_DISK=r2

# Cloudflare R2 Configuration
CLOUDFLARE_R2_ACCESS_KEY_ID=your-access-key-id
CLOUDFLARE_R2_SECRET_ACCESS_KEY=your-secret-access-key
CLOUDFLARE_R2_DEFAULT_REGION=auto
CLOUDFLARE_R2_BUCKET=yoryor-production
CLOUDFLARE_R2_URL=https://pub-123456789.r2.dev
CLOUDFLARE_R2_ENDPOINT=https://account-id.r2.cloudflarestorage.com
CLOUDFLARE_R2_USE_PATH_STYLE_ENDPOINT=true
```

**Note**: R2 replaces AWS S3 for cost-effective media storage.

#### Third-Party Services

**VideoSDK (Primary Video Provider):**
```env
VIDEOSDK_API_KEY=your-videosdk-api-key
VIDEOSDK_SECRET_KEY=your-videosdk-secret
VIDEOSDK_API_ENDPOINT=https://api.videosdk.live/v2
```

**Agora (Backup Video Provider):**
```env
AGORA_APP_ID=your-agora-app-id
AGORA_APP_CERTIFICATE=your-agora-certificate
```

**Google OAuth (Optional):**
```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://yoryor.com/auth/google/callback
```

**Expo Push Notifications:**
```env
EXPO_PUSH_NOTIFICATION_URL=https://exp.host/--/api/v2/push/send
```

---

## Server Configuration

### Install Required Software

#### Ubuntu 22.04 Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath php8.2-soap php8.2-imagick

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Supervisor (for process management)
sudo apt install -y supervisor

# Install Certbot (for SSL)
sudo apt install -y certbot python3-certbot-nginx
```

#### Verify Installations

```bash
php -v  # Should show PHP 8.2.x
composer -V  # Should show Composer 2.x
node -v  # Should show Node 18.x
npm -v  # Should show NPM 9.x
mysql --version  # Should show MySQL 8.0.x
redis-cli --version  # Should show Redis 6.0.x
nginx -v  # Should show Nginx 1.18+
```

---

## Dependencies Installation

### 1. Install PHP Dependencies

```bash
cd /var/www/yoryor
composer install --optimize-autoloader --no-dev
```

**Flags explained:**
- `--optimize-autoloader`: Optimizes class map for production (faster autoloading)
- `--no-dev`: Excludes development dependencies (Telescope, etc.)

### 2. Install Node Dependencies

```bash
npm ci
```

**Note**: `npm ci` is preferred over `npm install` for production as it uses the lock file strictly and performs a clean install.

### 3. Build Frontend Assets

```bash
npm run build
```

This compiles and minifies JavaScript and CSS files using Vite.

**Expected output:**
```
vite v5.x.x building for production...
✓ x modules transformed.
dist/assets/app-xxx.js    xxx.xx kB │ gzip: xxx.xx kB
dist/assets/app-xxx.css   xxx.xx kB │ gzip: xxx.xx kB
✓ built in xxxms
```

---

## Database Setup

### 1. Create Database

#### MySQL Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Login to MySQL
sudo mysql -u root -p
```

```sql
-- Create database with correct charset
CREATE DATABASE yoryor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with strong password
CREATE USER 'yoryor_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON yoryor.* TO 'yoryor_user'@'localhost';
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SHOW GRANTS FOR 'yoryor_user'@'localhost';

EXIT;
```

#### PostgreSQL Setup (Alternative)

```bash
# Switch to postgres user
sudo -u postgres psql
```

```sql
-- Create database
CREATE DATABASE yoryor
    WITH ENCODING 'UTF8'
    LC_COLLATE = 'en_US.UTF-8'
    LC_CTYPE = 'en_US.UTF-8'
    TEMPLATE template0;

-- Create user
CREATE USER yoryor_user WITH ENCRYPTED PASSWORD 'STRONG_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE yoryor TO yoryor_user;

\q
```

### 2. Run Migrations

```bash
cd /var/www/yoryor

# Run migrations (--force is required in production)
php artisan migrate --force
```

**Expected output:**
```
Migration table created successfully.
Migrating: 2025_09_24_211011_create_users_table
Migrated:  2025_09_24_211011_create_users_table (xxx.xxms)
...
```

### 3. Seed Database (Initial Data Only)

```bash
# Seed countries data (required)
php artisan db:seed --class=CountrySeeder --force

# Seed subscription plans (required)
php artisan db:seed --class=SubscriptionPlanSeeder --force

# Seed roles (required)
php artisan db:seed --class=RoleSeeder --force
```

**Warning**: Do NOT run `DatabaseSeeder` in production as it creates test/fake data.

### 4. Optimize Database

```bash
# For MySQL, optimize tables after initial migration
mysql -u yoryor_user -p yoryor -e "OPTIMIZE TABLE users, profiles, messages, chats, matches;"
```

---

## Storage Configuration

### 1. Create Storage Directories

```bash
# Create symbolic link from public/storage to storage/app/public
php artisan storage:link
```

**Expected output:**
```
The [public/storage] link has been connected to [storage/app/public].
```

### 2. Set Permissions

```bash
# Set correct ownership and permissions
sudo chown -R www-data:www-data /var/www/yoryor/storage
sudo chown -R www-data:www-data /var/www/yoryor/bootstrap/cache
sudo chmod -R 775 /var/www/yoryor/storage
sudo chmod -R 775 /var/www/yoryor/bootstrap/cache
```

**Explanation:**
- `www-data` is the default Nginx/Apache user on Ubuntu
- `775` allows web server to read/write files
- Storage and cache directories must be writable

### 3. Configure Cloudflare R2

#### Create R2 Bucket

1. Login to Cloudflare Dashboard
2. Navigate to R2 Object Storage
3. Create new bucket: `yoryor-production`
4. Generate API credentials:
   - Access Key ID
   - Secret Access Key
5. Note your account ID and bucket URL

#### Configure in Laravel

Update `config/filesystems.php` (already configured):

```php
'disks' => [
    // ...
    'r2' => [
        'driver' => 's3',
        'key' => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
        'secret' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
        'region' => env('CLOUDFLARE_R2_DEFAULT_REGION', 'auto'),
        'bucket' => env('CLOUDFLARE_R2_BUCKET'),
        'url' => env('CLOUDFLARE_R2_URL'),
        'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
        'use_path_style_endpoint' => env('CLOUDFLARE_R2_USE_PATH_STYLE_ENDPOINT', true),
        'throw' => false,
    ],
],
```

#### Test R2 Connection

```bash
php artisan tinker
```

```php
// Test upload
Storage::disk('r2')->put('test.txt', 'Hello World from YorYor');

// Test read
Storage::disk('r2')->exists('test.txt'); // Should return true
Storage::disk('r2')->get('test.txt'); // Should return "Hello World from YorYor"

// Clean up
Storage::disk('r2')->delete('test.txt');

exit
```

---

## Queue Workers Setup

YorYor uses queues for background jobs (emails, notifications, image processing, panic button alerts, etc.).

### 1. Install Supervisor

```bash
sudo apt-get install supervisor
```

### 2. Create Queue Worker Configuration

Create file: `/etc/supervisor/conf.d/yoryor-worker.conf`

```bash
sudo nano /etc/supervisor/conf.d/yoryor-worker.conf
```

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
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

**Configuration explained:**
- `numprocs=4`: Runs 4 worker processes
- `--sleep=3`: Wait 3 seconds when queue is empty
- `--tries=3`: Retry failed jobs up to 3 times
- `--max-time=3600`: Restart worker after 1 hour
- `--timeout=60`: Kill jobs running longer than 60 seconds

### 3. Start Queue Workers

```bash
# Read new configuration
sudo supervisorctl reread

# Update Supervisor
sudo supervisorctl update

# Start workers
sudo supervisorctl start yoryor-worker:*

# Check status
sudo supervisorctl status yoryor-worker:*
```

**Expected output:**
```
yoryor-worker:yoryor-worker_00   RUNNING   pid xxxxx, uptime 0:00:05
yoryor-worker:yoryor-worker_01   RUNNING   pid xxxxx, uptime 0:00:05
yoryor-worker:yoryor-worker_02   RUNNING   pid xxxxx, uptime 0:00:05
yoryor-worker:yoryor-worker_03   RUNNING   pid xxxxx, uptime 0:00:05
```

### 4. Monitor Queue Workers

```bash
# View logs
tail -f /var/www/yoryor/storage/logs/worker.log

# Restart all workers (after code deployment)
sudo supervisorctl restart yoryor-worker:*

# Stop all workers
sudo supervisorctl stop yoryor-worker:*
```

### Alternative: Laravel Horizon (Advanced)

For advanced queue management with a dashboard:

```bash
# Install Horizon
composer require laravel/horizon

# Publish configuration
php artisan horizon:install
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

**Note**: Protect Horizon with authentication middleware in production.

---

## WebSocket Server

YorYor uses Laravel Reverb for real-time features (messaging, typing indicators, online status).

### 1. Create Reverb Supervisor Configuration

Create file: `/etc/supervisor/conf.d/yoryor-reverb.conf`

```bash
sudo nano /etc/supervisor/conf.d/yoryor-reverb.conf
```

```ini
[program:yoryor-reverb]
process_name=%(program_name)s
command=php /var/www/yoryor/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/yoryor/storage/logs/reverb.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=10
```

**Configuration explained:**
- `--host=0.0.0.0`: Listen on all interfaces
- `--port=8080`: WebSocket server port (default)
- Logs to `storage/logs/reverb.log`

### 2. Start Reverb Server

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yoryor-reverb

# Check status
sudo supervisorctl status yoryor-reverb
```

**Expected output:**
```
yoryor-reverb                    RUNNING   pid xxxxx, uptime 0:00:05
```

### 3. Configure Nginx for WebSocket Proxy

Laravel Reverb runs on port 8080 internally. Nginx will proxy WebSocket connections through HTTPS.

Add to your Nginx site configuration (see full config below):

```nginx
# WebSocket proxy for Laravel Reverb
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
    proxy_connect_timeout 60;
    proxy_send_timeout 60;
}
```

Reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Test WebSocket Connection

```bash
# Test local connection
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/app/${REVERB_APP_KEY}
```

**Expected response headers:**
```
HTTP/1.1 101 Switching Protocols
Upgrade: websocket
Connection: Upgrade
```

### 5. Systemd Service (Alternative to Supervisor)

Create file: `/etc/systemd/system/yoryor-reverb.service`

```bash
sudo nano /etc/systemd/system/yoryor-reverb.service
```

```ini
[Unit]
Description=YorYor Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/yoryor
ExecStart=/usr/bin/php /var/www/yoryor/artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=5
StandardOutput=append:/var/www/yoryor/storage/logs/reverb.log
StandardError=append:/var/www/yoryor/storage/logs/reverb.log

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable yoryor-reverb
sudo systemctl start yoryor-reverb
sudo systemctl status yoryor-reverb
```

---

## Production Optimizations

### 1. Optimize Laravel Configuration

```bash
cd /var/www/yoryor

# Cache configuration files
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

**Important**: Run these commands after every deployment. Clear with:
```bash
php artisan optimize:clear
```

### 2. Optimize Composer Autoloader

Already done if you used `composer install --optimize-autoloader`.

If not:
```bash
composer dump-autoload --optimize
```

### 3. Enable PHP OPcache

Edit PHP configuration: `/etc/php/8.2/fpm/php.ini`

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Add/update OPcache settings:

```ini
[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
opcache.revalidate_freq=0
```

**Important**: `opcache.validate_timestamps=0` disables file change checks for maximum performance. After code deployment, restart PHP-FPM to clear OPcache.

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

### 4. Configure Nginx

Create Nginx configuration: `/etc/nginx/sites-available/yoryor`

```bash
sudo nano /etc/nginx/sites-available/yoryor
```

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name yoryor.com www.yoryor.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yoryor.com www.yoryor.com;

    root /var/www/yoryor/public;
    index index.php index.html;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/yoryor.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yoryor.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;

    # OCSP stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /etc/letsencrypt/live/yoryor.com/chain.pem;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(self), microphone=(self), camera=(self)" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript application/xml+atom font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
    gzip_disable "msie6";

    # Client body size (for file uploads)
    client_max_body_size 10M;

    # Logs
    access_log /var/log/nginx/yoryor-access.log;
    error_log /var/log/nginx/yoryor-error.log;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 300;
    }

    # WebSocket proxy for Laravel Reverb
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
        proxy_connect_timeout 60;
        proxy_send_timeout 60;
    }

    # Static asset caching
    location ~* \.(jpg|jpeg|png|gif|ico|svg|css|js|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Deny access to sensitive directories
    location ~ /(storage|bootstrap/cache|vendor|node_modules) {
        deny all;
    }

    # Block common exploits
    location ~* (\.git|\.env|composer\.json|composer\.lock|package\.json|package-lock\.json|phpunit\.xml) {
        deny all;
    }
}
```

Enable site and reload Nginx:
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/yoryor /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 5. Configure Apache (Alternative)

Create Apache configuration: `/etc/apache2/sites-available/yoryor.conf`

```bash
sudo nano /etc/apache2/sites-available/yoryor.conf
```

```apache
# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName yoryor.com
    ServerAlias www.yoryor.com
    Redirect permanent / https://yoryor.com/
</VirtualHost>

# HTTPS Configuration
<VirtualHost *:443>
    ServerName yoryor.com
    ServerAlias www.yoryor.com
    DocumentRoot /var/www/yoryor/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yoryor.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yoryor.com/privkey.pem
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder off
    SSLSessionTickets off

    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Directory configuration
    <Directory /var/www/yoryor/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # WebSocket proxy for Laravel Reverb
    ProxyRequests Off
    ProxyPreserveHost On
    ProxyPass /app/ http://127.0.0.1:8080/app/
    ProxyPassReverse /app/ http://127.0.0.1:8080/app/

    # WebSocket upgrade headers
    <Location /app/>
        RewriteEngine On
        RewriteCond %{HTTP:Upgrade} =websocket [NC]
        RewriteRule /(.*)           ws://127.0.0.1:8080/$1 [P,L]
        RewriteCond %{HTTP:Upgrade} !=websocket [NC]
        RewriteRule /(.*)           http://127.0.0.1:8080/$1 [P,L]
    </Location>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/yoryor-error.log
    CustomLog ${APACHE_LOG_DIR}/yoryor-access.log combined
</VirtualHost>
```

Enable required modules and site:
```bash
# Enable modules
sudo a2enmod ssl rewrite headers proxy proxy_http proxy_wstunnel

# Enable site
sudo a2ensite yoryor

# Disable default site
sudo a2dissite 000-default

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### 6. Configure PHP-FPM

Edit PHP-FPM pool configuration: `/etc/php/8.2/fpm/pool.d/www.conf`

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Update performance settings:

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data

; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; PHP configuration
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M
php_admin_value[max_execution_time] = 300
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

## Monitoring Setup

### 1. Laravel Telescope (Development/Staging Only)

**DO NOT enable in production** due to performance impact and security concerns.

For staging environment:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Protect with authentication:
```php
// app/Providers/TelescopeServiceProvider.php
protected function gate()
{
    Gate::define('viewTelescope', function ($user) {
        return in_array($user->email, [
            'admin@yoryor.com',
        ]);
    });
}
```

Access: `https://staging.yoryor.com/telescope`

### 2. Laravel Pulse

Real-time performance monitoring for production.

```bash
# Publish configuration
php artisan vendor:publish --tag=pulse-config

# Run migrations
php artisan migrate
```

Protect with authentication in `routes/web.php`:

```php
use Laravel\Pulse\Facades\Pulse;

Route::middleware(['auth', 'admin'])->group(function () {
    Pulse::routes();
});
```

Access: `https://yoryor.com/pulse`

### 3. Laravel Horizon (Queue Monitoring)

Already covered in Queue Workers section.

Protect with authentication:

```php
// app/Providers/HorizonServiceProvider.php
protected function gate()
{
    Gate::define('viewHorizon', function ($user) {
        return in_array($user->email, [
            'admin@yoryor.com',
        ]);
    });
}
```

Access: `https://yoryor.com/horizon`

### 4. Server Monitoring

#### Install Monitoring Tools

```bash
# Install htop for process monitoring
sudo apt-get install htop

# Install iotop for I/O monitoring
sudo apt-get install iotop

# Install nethogs for network monitoring
sudo apt-get install nethogs
```

#### Netdata (Real-time Monitoring)

```bash
# Install Netdata
bash <(curl -Ss https://my-netdata.io/kickstart.sh)
```

Access Netdata: `http://your-server-ip:19999`

**Secure Netdata** by configuring Nginx reverse proxy:

```nginx
location /netdata/ {
    proxy_pass http://127.0.0.1:19999/;
    proxy_set_header Host $host;
    auth_basic "Restricted Access";
    auth_basic_user_file /etc/nginx/.htpasswd;
}
```

Create password file:
```bash
sudo apt install apache2-utils
sudo htpasswd -c /etc/nginx/.htpasswd admin
```

### 5. Application Logging

#### Configure Log Rotation

Create `/etc/logrotate.d/yoryor`:

```bash
sudo nano /etc/logrotate.d/yoryor
```

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
    postrotate
        /bin/kill -USR1 $(cat /var/run/nginx.pid 2>/dev/null) 2>/dev/null || true
    endscript
}
```

Test log rotation:
```bash
sudo logrotate -f /etc/logrotate.d/yoryor
```

#### Centralized Logging (Optional)

For multi-server setups, consider using:
- **Papertrail**: Cloud log management
- **Loggly**: Log aggregation service
- **ELK Stack**: Elasticsearch, Logstash, Kibana

Configure in `.env`:
```env
LOG_CHANNEL=stack
LOG_SLACK_WEBHOOK_URL=your-slack-webhook-url
```

---

## SSL/HTTPS Setup

### Using Let's Encrypt (Recommended)

#### 1. Install Certbot

```bash
sudo apt install certbot python3-certbot-nginx
```

#### 2. Obtain SSL Certificate

```bash
# For Nginx
sudo certbot --nginx -d yoryor.com -d www.yoryor.com

# For Apache
sudo certbot --apache -d yoryor.com -d www.yoryor.com
```

Follow the prompts:
- Enter email address for renewal notifications
- Agree to Terms of Service
- Choose whether to redirect HTTP to HTTPS (recommended: yes)

#### 3. Test SSL Configuration

```bash
# Test SSL strength
curl -I https://yoryor.com

# Test with SSL Labs
# Visit: https://www.ssllabs.com/ssltest/analyze.html?d=yoryor.com
```

#### 4. Setup Auto-Renewal

Certbot automatically installs a cron job. Verify:

```bash
sudo systemctl status certbot.timer
```

Test renewal:
```bash
sudo certbot renew --dry-run
```

### Using Custom SSL Certificate

If you have a commercial SSL certificate:

```bash
# Copy certificate files
sudo mkdir -p /etc/ssl/private
sudo cp your-certificate.crt /etc/ssl/certs/yoryor.com.crt
sudo cp your-private-key.key /etc/ssl/private/yoryor.com.key
sudo chmod 600 /etc/ssl/private/yoryor.com.key
```

Update Nginx/Apache configuration with correct paths.

---

## Backup Strategies

### 1. Database Backup

#### Automated MySQL Backup Script

Create `/usr/local/bin/backup-yoryor-db.sh`:

```bash
sudo nano /usr/local/bin/backup-yoryor-db.sh
```

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/var/backups/yoryor/database"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
FILENAME="yoryor_db_$DATE.sql.gz"
DB_NAME="yoryor"
DB_USER="yoryor_user"
DB_PASSWORD="YOUR_DB_PASSWORD"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create database dump
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME | gzip > "$BACKUP_DIR/$FILENAME"

# Check if backup was successful
if [ $? -eq 0 ]; then
    echo "[$(date)] Backup completed successfully: $FILENAME" >> /var/log/yoryor-backup.log
else
    echo "[$(date)] Backup FAILED!" >> /var/log/yoryor-backup.log
    exit 1
fi

# Keep only last 30 days
find $BACKUP_DIR -name "yoryor_db_*.sql.gz" -mtime +30 -delete

# Optional: Upload to cloud storage (Cloudflare R2)
# aws s3 cp "$BACKUP_DIR/$FILENAME" s3://yoryor-backups/database/ --endpoint-url=YOUR_R2_ENDPOINT

echo "[$(date)] Backup completed: $FILENAME"
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/backup-yoryor-db.sh
```

#### PostgreSQL Backup Script

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/yoryor/database"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
FILENAME="yoryor_db_$DATE.sql.gz"
DB_NAME="yoryor"
DB_USER="yoryor_user"

mkdir -p $BACKUP_DIR

pg_dump -U $DB_USER -d $DB_NAME | gzip > "$BACKUP_DIR/$FILENAME"

find $BACKUP_DIR -name "yoryor_db_*.sql.gz" -mtime +30 -delete
```

#### Schedule Backups with Cron

```bash
sudo crontab -e
```

Add daily backup at 2 AM:
```cron
0 2 * * * /usr/local/bin/backup-yoryor-db.sh
```

### 2. Application Files Backup

Media files are stored on Cloudflare R2, so no local backup needed. For other application files:

```bash
#!/bin/bash
# /usr/local/bin/backup-yoryor-files.sh

BACKUP_DIR="/var/backups/yoryor/files"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
FILENAME="yoryor_files_$DATE.tar.gz"

mkdir -p $BACKUP_DIR

# Backup storage/app (excluding public media)
tar -czf "$BACKUP_DIR/$FILENAME" \
    --exclude='/var/www/yoryor/storage/app/public' \
    --exclude='/var/www/yoryor/storage/framework/cache' \
    --exclude='/var/www/yoryor/storage/framework/sessions' \
    /var/www/yoryor/storage/app

# Keep only last 7 days
find $BACKUP_DIR -name "yoryor_files_*.tar.gz" -mtime +7 -delete
```

### 3. Offsite Backup to Cloudflare R2

Using AWS CLI (compatible with R2):

```bash
# Configure AWS CLI for R2
aws configure --profile r2
# Enter R2 Access Key ID
# Enter R2 Secret Access Key
# Region: auto
# Output format: json

# Upload backup
aws s3 cp /var/backups/yoryor/database/ s3://yoryor-backups/database/ \
    --recursive \
    --endpoint-url https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com \
    --profile r2
```

### 4. Restore from Backup

#### Database Restore

```bash
# Decompress and restore MySQL
gunzip < /var/backups/yoryor/database/yoryor_db_2025-10-07_02-00-00.sql.gz | mysql -u yoryor_user -p yoryor

# PostgreSQL
gunzip < /var/backups/yoryor/database/yoryor_db_2025-10-07_02-00-00.sql.gz | psql -U yoryor_user -d yoryor
```

#### Files Restore

```bash
tar -xzf /var/backups/yoryor/files/yoryor_files_2025-10-07_02-00-00.tar.gz -C /
```

---

## Zero-Downtime Deployment

### Using Laravel Envoyer (Recommended)

Laravel Envoyer provides zero-downtime deployments with rollback support.

**Features:**
- Atomic deployments
- Health checks
- Rollback support
- Deployment hooks

### Manual Zero-Downtime Deployment

#### 1. Setup Blue-Green Deployment

```bash
# Create releases directory
sudo mkdir -p /var/www/yoryor-releases
sudo mkdir -p /var/www/yoryor-shared

# Shared directories
sudo mkdir -p /var/www/yoryor-shared/storage
sudo mkdir -p /var/www/yoryor-shared/bootstrap/cache
```

#### 2. Deployment Script

Create `/usr/local/bin/deploy-yoryor.sh`:

```bash
#!/bin/bash

set -e

PROJECT_DIR="/var/www/yoryor"
RELEASES_DIR="/var/www/yoryor-releases"
SHARED_DIR="/var/www/yoryor-shared"
REPO_URL="https://github.com/yourusername/yoryor.git"
BRANCH="main"
RELEASE_NAME=$(date +%Y%m%d%H%M%S)
RELEASE_DIR="$RELEASES_DIR/$RELEASE_NAME"

echo "==> Starting deployment: $RELEASE_NAME"

# 1. Clone repository
echo "==> Cloning repository..."
git clone --depth 1 --branch $BRANCH $REPO_URL $RELEASE_DIR

# 2. Install dependencies
echo "==> Installing dependencies..."
cd $RELEASE_DIR
composer install --optimize-autoloader --no-dev --no-interaction
npm ci
npm run build

# 3. Link shared directories
echo "==> Linking shared directories..."
rm -rf $RELEASE_DIR/storage
ln -s $SHARED_DIR/storage $RELEASE_DIR/storage
rm -rf $RELEASE_DIR/bootstrap/cache
ln -s $SHARED_DIR/bootstrap/cache $RELEASE_DIR/bootstrap/cache

# 4. Copy .env
cp $PROJECT_DIR/.env $RELEASE_DIR/.env

# 5. Run migrations
echo "==> Running migrations..."
php $RELEASE_DIR/artisan migrate --force

# 6. Optimize
echo "==> Optimizing..."
php $RELEASE_DIR/artisan config:cache
php $RELEASE_DIR/artisan route:cache
php $RELEASE_DIR/artisan view:cache
php $RELEASE_DIR/artisan event:cache

# 7. Atomic switch
echo "==> Switching to new release..."
ln -sfn $RELEASE_DIR /var/www/yoryor-new
mv -Tf /var/www/yoryor-new $PROJECT_DIR

# 8. Reload services
echo "==> Reloading services..."
sudo supervisorctl restart yoryor-worker:*
sudo supervisorctl restart yoryor-reverb
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# 9. Cleanup old releases (keep last 5)
echo "==> Cleaning up old releases..."
cd $RELEASES_DIR
ls -1dt */ | tail -n +6 | xargs rm -rf

echo "==> Deployment completed successfully!"
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/deploy-yoryor.sh
```

Run deployment:
```bash
sudo /usr/local/bin/deploy-yoryor.sh
```

### Rollback Script

```bash
#!/bin/bash
# /usr/local/bin/rollback-yoryor.sh

RELEASES_DIR="/var/www/yoryor-releases"
PROJECT_DIR="/var/www/yoryor"

# Get previous release
PREVIOUS_RELEASE=$(ls -1dt $RELEASES_DIR/*/ | sed -n 2p)

if [ -z "$PREVIOUS_RELEASE" ]; then
    echo "No previous release found!"
    exit 1
fi

echo "==> Rolling back to: $PREVIOUS_RELEASE"

# Switch to previous release
ln -sfn $PREVIOUS_RELEASE /var/www/yoryor-rollback
mv -Tf /var/www/yoryor-rollback $PROJECT_DIR

# Reload services
sudo supervisorctl restart yoryor-worker:*
sudo supervisorctl restart yoryor-reverb
sudo systemctl reload php8.2-fpm

echo "==> Rollback completed!"
```

---

## Health Checks

### 1. Application Health Endpoint

Create health check route in `routes/api.php`:

```php
Route::get('/health', function () {
    $checks = [
        'database' => false,
        'redis' => false,
        'storage' => false,
    ];

    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Exception $e) {
        //
    }

    try {
        Redis::ping();
        $checks['redis'] = true;
    } catch (\Exception $e) {
        //
    }

    try {
        $checks['storage'] = Storage::disk('r2')->exists('.health-check') || true;
    } catch (\Exception $e) {
        //
    }

    $healthy = array_reduce($checks, fn($carry, $check) => $carry && $check, true);

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
    ], $healthy ? 200 : 503);
});
```

Test:
```bash
curl https://yoryor.com/api/v1/health
```

### 2. Monitoring Script

Create `/usr/local/bin/monitor-yoryor.sh`:

```bash
#!/bin/bash

# Check API health
if ! curl -f -s https://yoryor.com/api/v1/health > /dev/null; then
    echo "[$(date)] ALERT: API health check failed!" >> /var/log/yoryor-monitor.log
    # Send alert (email, Slack, etc.)
fi

# Check queue workers
if ! pgrep -f "queue:work" > /dev/null; then
    echo "[$(date)] ALERT: Queue workers are not running!" >> /var/log/yoryor-monitor.log
    sudo supervisorctl restart yoryor-worker:*
fi

# Check Reverb server
if ! pgrep -f "reverb:start" > /dev/null; then
    echo "[$(date)] ALERT: Reverb server is not running!" >> /var/log/yoryor-monitor.log
    sudo supervisorctl restart yoryor-reverb
fi

# Check disk space
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "[$(date)] ALERT: Disk usage is ${DISK_USAGE}%!" >> /var/log/yoryor-monitor.log
fi

# Check memory usage
MEM_USAGE=$(free | awk 'NR==2 {printf "%.0f", $3*100/$2}')
if [ $MEM_USAGE -gt 90 ]; then
    echo "[$(date)] ALERT: Memory usage is ${MEM_USAGE}%!" >> /var/log/yoryor-monitor.log
fi
```

Schedule monitoring:
```bash
sudo crontab -e
```

```cron
*/5 * * * * /usr/local/bin/monitor-yoryor.sh
```

### 3. External Monitoring Services

Consider using:
- **UptimeRobot**: Free website monitoring
- **Pingdom**: Website and API monitoring
- **New Relic**: Application performance monitoring
- **Datadog**: Infrastructure and application monitoring

---

## Rollback Procedures

### Emergency Rollback

```bash
# Using the rollback script
sudo /usr/local/bin/rollback-yoryor.sh
```

### Manual Rollback

```bash
# 1. Switch to previous release
cd /var/www/yoryor-releases
PREVIOUS_RELEASE=$(ls -1dt */ | sed -n 2p)
ln -sfn /var/www/yoryor-releases/$PREVIOUS_RELEASE /var/www/yoryor

# 2. Rollback database (if needed)
php artisan migrate:rollback --step=1 --force

# 3. Clear caches
php artisan optimize:clear

# 4. Restart services
sudo supervisorctl restart yoryor-worker:*
sudo supervisorctl restart yoryor-reverb
sudo systemctl reload php8.2-fpm
```

### Git-Based Rollback

```bash
# 1. Revert to previous commit
cd /var/www/yoryor
git log --oneline -10  # Find commit hash
git reset --hard COMMIT_HASH

# 2. Reinstall dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# 3. Rollback migrations (if needed)
php artisan migrate:rollback --step=1 --force

# 4. Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart services
sudo supervisorctl restart all
sudo systemctl reload php8.2-fpm
```

---

## Deployment Checklist

### Pre-Deployment Checklist

- [ ] Code reviewed and approved
- [ ] All tests passing (`php artisan test`)
- [ ] Database migrations tested on staging
- [ ] `.env` file configured with production values
- [ ] Third-party API keys verified
- [ ] SSL certificate valid and not expiring soon
- [ ] Backup completed and verified
- [ ] Rollback plan prepared
- [ ] Team notified of deployment schedule
- [ ] Maintenance mode message prepared (if needed)

### Deployment Steps

1. **Enable Maintenance Mode (Optional)**
   ```bash
   php artisan down --secret="bypass-token-12345"
   ```

2. **Pull Latest Code**
   ```bash
   cd /var/www/yoryor
   git pull origin main
   ```

3. **Install Dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm ci
   npm run build
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

5. **Clear Old Caches**
   ```bash
   php artisan optimize:clear
   ```

6. **Build New Caches**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

7. **Restart Services**
   ```bash
   sudo supervisorctl restart yoryor-worker:*
   sudo supervisorctl restart yoryor-reverb
   sudo systemctl reload php8.2-fpm
   sudo systemctl reload nginx
   ```

8. **Disable Maintenance Mode**
   ```bash
   php artisan up
   ```

### Post-Deployment Checklist

- [ ] Application accessible at https://yoryor.com
- [ ] SSL certificate working correctly
- [ ] Database connections working
- [ ] Redis connections working
- [ ] WebSocket connections working (test messaging)
- [ ] File uploads working
- [ ] Email sending working
- [ ] Queue processing working (`supervisorctl status`)
- [ ] No errors in logs (`tail -f storage/logs/laravel.log`)
- [ ] Test critical user flows:
  - [ ] User registration
  - [ ] User login
  - [ ] Profile creation
  - [ ] Messaging
  - [ ] Video calling
  - [ ] Image upload
- [ ] Monitor server resources (CPU, RAM, disk)
- [ ] Check application performance
- [ ] Notify team of successful deployment

---

## Troubleshooting

### Queue Workers Not Processing

**Symptoms:**
- Jobs stuck in `jobs` table
- Background tasks not executing
- Emails not sending

**Diagnosis:**
```bash
# Check worker status
sudo supervisorctl status yoryor-worker:*

# Check worker logs
tail -f /var/www/yoryor/storage/logs/worker.log

# Check failed jobs
php artisan queue:failed
```

**Solutions:**
```bash
# Restart workers
sudo supervisorctl restart yoryor-worker:*

# Retry failed jobs
php artisan queue:retry all

# Clear stuck jobs (use with caution)
php artisan queue:flush
```

### WebSocket Connection Failed

**Symptoms:**
- Real-time features not working
- Messages not appearing instantly
- "WebSocket connection failed" in console

**Diagnosis:**
```bash
# Check Reverb status
sudo supervisorctl status yoryor-reverb

# Check Reverb logs
tail -f /var/www/yoryor/storage/logs/reverb.log

# Test WebSocket connection
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/app/${REVERB_APP_KEY}
```

**Solutions:**
```bash
# Restart Reverb
sudo supervisorctl restart yoryor-reverb

# Check Nginx proxy configuration
sudo nginx -t
sudo systemctl reload nginx

# Verify .env configuration
cat .env | grep REVERB
```

### Database Connection Error

**Symptoms:**
- "SQLSTATE[HY000] [2002] Connection refused"
- Application showing database errors

**Diagnosis:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u yoryor_user -p -h 127.0.0.1

# From Laravel
php artisan tinker
>>> DB::connection()->getPdo();
```

**Solutions:**
```bash
# Restart MySQL
sudo systemctl restart mysql

# Check credentials in .env
cat .env | grep DB_

# Check MySQL user permissions
mysql -u root -p
mysql> SHOW GRANTS FOR 'yoryor_user'@'localhost';
```

### Storage/Upload Errors

**Symptoms:**
- "Failed to write file to disk"
- "Permission denied" errors
- Images not uploading

**Diagnosis:**
```bash
# Check permissions
ls -la /var/www/yoryor/storage/

# Check disk space
df -h

# Test R2 connection
php artisan tinker
>>> Storage::disk('r2')->put('test.txt', 'test');
```

**Solutions:**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/yoryor/storage
sudo chmod -R 775 /var/www/yoryor/storage

# Recreate storage link
php artisan storage:link

# Verify R2 credentials
cat .env | grep CLOUDFLARE_R2
```

### Performance Issues

**Symptoms:**
- Slow page loads
- High server load
- Timeouts

**Diagnosis:**
```bash
# Check server resources
htop
free -h
df -h

# Check slow queries
php artisan pulse

# Check Nginx/PHP-FPM logs
tail -f /var/log/nginx/yoryor-error.log
tail -f /var/log/php8.2-fpm.log
```

**Solutions:**
```bash
# Clear all caches
php artisan optimize:clear
redis-cli FLUSHDB

# Restart services
sudo systemctl restart php8.2-fpm nginx redis-server mysql

# Check queue workers
sudo supervisorctl status yoryor-worker:*

# Optimize database
mysql -u yoryor_user -p yoryor -e "OPTIMIZE TABLE users, profiles, messages, chats, matches;"
```

### 500 Internal Server Error

**Symptoms:**
- Generic 500 error page
- "Whoops, something went wrong"

**Diagnosis:**
```bash
# Check Laravel logs
tail -f /var/www/yoryor/storage/logs/laravel.log

# Check Nginx error logs
tail -f /var/log/nginx/yoryor-error.log

# Check PHP-FPM logs
tail -f /var/log/php8.2-fpm.log

# Enable debug mode temporarily (ONLY FOR DIAGNOSIS)
# Edit .env: APP_DEBUG=true
# REMEMBER TO DISABLE AFTER DIAGNOSIS
```

**Common causes:**
1. **Incorrect file permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/yoryor
   sudo chmod -R 755 /var/www/yoryor
   sudo chmod -R 775 /var/www/yoryor/storage /var/www/yoryor/bootstrap/cache
   ```

2. **Missing .env or APP_KEY**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Cached configuration with errors**
   ```bash
   php artisan optimize:clear
   ```

4. **PHP memory limit exceeded**
   ```bash
   sudo nano /etc/php/8.2/fpm/php.ini
   # Increase: memory_limit = 256M
   sudo systemctl restart php8.2-fpm
   ```

### SSL Certificate Issues

**Symptoms:**
- "Your connection is not private"
- SSL certificate expired warnings

**Diagnosis:**
```bash
# Check certificate expiration
sudo certbot certificates

# Test SSL configuration
curl -I https://yoryor.com
```

**Solutions:**
```bash
# Renew certificate
sudo certbot renew

# Force renewal
sudo certbot renew --force-renewal

# Restart web server
sudo systemctl reload nginx
```

---

## Deployment Automation Script

Complete automated deployment script:

```bash
#!/bin/bash
# /usr/local/bin/deploy-yoryor-complete.sh

set -e

echo "========================================="
echo "YorYor Production Deployment"
echo "========================================="

PROJECT_DIR="/var/www/yoryor"

# Enable maintenance mode
echo "==> Enabling maintenance mode..."
php $PROJECT_DIR/artisan down --secret="deploy-$(date +%s)"

# Pull latest code
echo "==> Pulling latest code..."
cd $PROJECT_DIR
git pull origin main

# Install dependencies
echo "==> Installing dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
npm ci
npm run build

# Run migrations
echo "==> Running migrations..."
php artisan migrate --force

# Clear old caches
echo "==> Clearing caches..."
php artisan optimize:clear

# Build new caches
echo "==> Building caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
echo "==> Restarting services..."
sudo supervisorctl restart yoryor-worker:*
sudo supervisorctl restart yoryor-reverb
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# Disable maintenance mode
echo "==> Disabling maintenance mode..."
php artisan up

# Health check
echo "==> Running health check..."
sleep 5
HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" https://yoryor.com/api/v1/health)

if [ $HEALTH_CHECK -eq 200 ]; then
    echo "========================================="
    echo "Deployment completed successfully!"
    echo "========================================="
else
    echo "========================================="
    echo "WARNING: Health check failed (HTTP $HEALTH_CHECK)"
    echo "Please verify application status"
    echo "========================================="
fi

# Log deployment
echo "[$(date)] Deployment completed (Health: $HEALTH_CHECK)" >> /var/log/yoryor-deployments.log
```

Make executable and run:
```bash
sudo chmod +x /usr/local/bin/deploy-yoryor-complete.sh
sudo /usr/local/bin/deploy-yoryor-complete.sh
```

---

## Continuous Deployment (Optional)

### GitHub Actions Workflow

Create `.github/workflows/deploy-production.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            /usr/local/bin/deploy-yoryor-complete.sh
```

Add GitHub secrets:
- `SERVER_HOST`: your server IP/domain
- `SERVER_USER`: SSH username
- `SSH_PRIVATE_KEY`: SSH private key

---

*This production deployment guide ensures YorYor runs smoothly with optimal performance, security, and reliability.*

**Last Updated:** October 2025
**Version:** 1.0
**Maintained by:** YorYor DevOps Team
