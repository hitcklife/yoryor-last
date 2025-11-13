# YorYor Backend Deployment Guide

## Server Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0+ or PostgreSQL 13+
- **Redis**: 6.0+
- **Node.js**: 18+ (for asset compilation)
- **Nginx**: 1.18+ or Apache 2.4+
- **SSL Certificate**: Required for production

### Recommended Specifications
- **CPU**: 4+ cores
- **RAM**: 8GB minimum, 16GB recommended
- **Storage**: 100GB SSD minimum
- **Bandwidth**: Unmetered preferred

## Environment Setup

### 1. Clone Repository
```bash
git clone https://github.com/yoryor/backend.git
cd backend
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME=YorYor
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yoryor.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yoryor_production
DB_USERNAME=yoryor_user
DB_PASSWORD=strong_password_here

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=redis_password_here
REDIS_PORT=6379

# Broadcasting
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=us2

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=yoryor-media
AWS_URL=https://cdn.yoryor.com

# Video SDK
VIDEOSDK_API_KEY=your_videosdk_key
VIDEOSDK_SECRET_KEY=your_videosdk_secret

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 4. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE yoryor_production;"
mysql -u root -p -e "CREATE USER 'yoryor_user'@'localhost' IDENTIFIED BY 'strong_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON yoryor_production.* TO 'yoryor_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force
```

### 5. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Web Server Configuration

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name api.yoryor.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yoryor.com;
    root /var/www/yoryor/public;

    ssl_certificate /etc/ssl/certs/yoryor.com.crt;
    ssl_certificate_key /etc/ssl/private/yoryor.com.key;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
    ssl_prefer_server_ciphers on;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # WebSocket support for Laravel Echo
    location /socket.io {
        proxy_pass http://localhost:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # API rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### Apache Configuration
```apache
<VirtualHost *:443>
    ServerName api.yoryor.com
    DocumentRoot /var/www/yoryor/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/yoryor.com.crt
    SSLCertificateKeyFile /etc/ssl/private/yoryor.com.key

    <Directory /var/www/yoryor/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/yoryor-error.log
    CustomLog ${APACHE_LOG_DIR}/yoryor-access.log combined
</VirtualHost>
```

## Queue Workers

### Supervisor Configuration
```ini
# /etc/supervisor/conf.d/yoryor-worker.conf
[program:yoryor-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yoryor/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/yoryor/storage/logs/worker.log
stopwaitsecs=3600

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

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yoryor-worker:*
sudo supervisorctl start yoryor-horizon
```

## Cron Jobs

Add to crontab:
```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/yoryor && php artisan schedule:run >> /dev/null 2>&1
```

## Performance Optimization

### 1. Laravel Optimization
```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Enable OPcache
# Edit php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
```

### 2. Database Optimization
```sql
-- Add indexes for performance
ALTER TABLE messages ADD INDEX idx_chat_created (chat_id, created_at);
ALTER TABLE users ADD INDEX idx_location (latitude, longitude);
ALTER TABLE users ADD INDEX idx_last_active (last_active_at);

-- Optimize tables periodically
OPTIMIZE TABLE messages;
OPTIMIZE TABLE users;
```

### 3. Redis Configuration
```bash
# /etc/redis/redis.conf
maxmemory 2gb
maxmemory-policy allkeys-lru
save ""
appendonly no
```

## Monitoring

### 1. Laravel Telescope (Development)
```bash
php artisan telescope:install
php artisan migrate
```

### 2. Laravel Horizon (Production)
Access at: https://api.yoryor.com/horizon

### 3. Laravel Pulse
```bash
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate
```

### 4. Server Monitoring
```bash
# Install monitoring tools
apt-get install htop iotop nethogs

# Monitor PHP-FPM
# /etc/php/8.2/fpm/pool.d/www.conf
pm.status_path = /status
```

## Security Hardening

### 1. Firewall Setup
```bash
# Install UFW
apt-get install ufw

# Configure firewall
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow http
ufw allow https
ufw allow 6001/tcp  # Laravel Echo Server
ufw enable
```

### 2. Fail2ban Configuration
```bash
# Install fail2ban
apt-get install fail2ban

# Create jail configuration
# /etc/fail2ban/jail.local
[laravel-api]
enabled = true
port = http,https
filter = laravel-api
logpath = /var/www/yoryor/storage/logs/laravel.log
maxretry = 5
bantime = 3600
```

### 3. SSL Security Headers
Add to Nginx configuration:
```nginx
# Security headers
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "DENY" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';" always;
```

## Backup Strategy

### 1. Database Backup
```bash
#!/bin/bash
# /scripts/backup-database.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"
DB_NAME="yoryor_production"
DB_USER="yoryor_user"

# Create backup
mysqldump -u $DB_USER -p $DB_NAME | gzip > $BACKUP_DIR/yoryor_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete

# Upload to S3
aws s3 cp $BACKUP_DIR/yoryor_$DATE.sql.gz s3://yoryor-backups/database/
```

### 2. Media Backup
```bash
# Sync to S3 (already using S3 for media storage)
aws s3 sync /var/www/yoryor/storage/app/public s3://yoryor-media-backup/
```

## Deployment Script

```bash
#!/bin/bash
# deploy.sh

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart yoryor-worker:*
sudo supervisorctl restart yoryor-horizon
sudo service php8.2-fpm reload
sudo service nginx reload

echo "Deployment completed!"
```

## Rollback Procedure

```bash
#!/bin/bash
# rollback.sh

# Revert to previous commit
git reset --hard HEAD~1

# Reinstall dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Rollback migrations if needed
php artisan migrate:rollback --force

# Clear caches
php artisan cache:clear
php artisan config:clear

# Restart services
sudo supervisorctl restart all
sudo service php8.2-fpm reload
sudo service nginx reload
```

## Health Checks

### 1. API Health Endpoint
```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'services' => [
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
            'redis' => Redis::ping() ? 'connected' : 'disconnected',
            'storage' => Storage::exists('health-check.txt') ? 'accessible' : 'inaccessible',
        ]
    ]);
});
```

### 2. Monitoring Script
```bash
#!/bin/bash
# monitor.sh

# Check API health
curl -f https://api.yoryor.com/api/v1/health || alert "API is down"

# Check queue workers
ps aux | grep -q "[q]ueue:work" || alert "Queue workers are down"

# Check disk space
df -h | awk '$5 > 80 {print "Disk usage alert: " $0}' | alert

# Check memory
free -m | awk 'NR==2{printf "Memory Usage: %s/%sMB (%.2f%%)\n", $3,$2,$3*100/$2 }'
```