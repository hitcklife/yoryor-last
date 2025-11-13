# YorYor Security & Infrastructure Guide

## Table of Contents
- [Overview](#overview)
- [Security Headers Configuration](#security-headers-configuration)
- [HTTPS/SSL Enforcement](#httpsssl-enforcement)
- [Firewall Rules](#firewall-rules)
- [Rate Limiting Configuration](#rate-limiting-configuration)
- [CORS Setup](#cors-setup)
- [Authentication Security](#authentication-security)
- [Session Security](#session-security)
- [Database Security](#database-security)
- [File Upload Security](#file-upload-security)
- [API Security Best Practices](#api-security-best-practices)
- [Server Hardening](#server-hardening)
- [Regular Security Audits](#regular-security-audits)
- [Incident Response Plan](#incident-response-plan)
- [GDPR Compliance](#gdpr-compliance)
- [Security Monitoring](#security-monitoring)

---

## Overview

This guide covers security configuration and infrastructure hardening for YorYor production deployment. Security is implemented at multiple layers:

1. **Network Layer**: Firewall, DDoS protection, SSL/TLS
2. **Application Layer**: Authentication, authorization, input validation
3. **Data Layer**: Encryption, secure storage, backup
4. **Infrastructure Layer**: Server hardening, monitoring, logging

**Security Principles:**
- Defense in depth (multiple security layers)
- Principle of least privilege
- Fail securely (default deny)
- Security by design
- Regular updates and patches

---

## Security Headers Configuration

### HTTP Security Headers

Security headers protect against common web vulnerabilities (XSS, clickjacking, MIME sniffing, etc.).

#### Nginx Configuration

Add to your Nginx server block:

```nginx
server {
    # ... other configuration ...

    # Strict Transport Security (HSTS)
    # Forces HTTPS connections for 1 year, including subdomains
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Prevent clickjacking attacks
    add_header X-Frame-Options "SAMEORIGIN" always;

    # Prevent MIME type sniffing
    add_header X-Content-Type-Options "nosniff" always;

    # Enable XSS protection in older browsers
    add_header X-XSS-Protection "1; mode=block" always;

    # Control referrer information
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Permissions Policy (formerly Feature Policy)
    add_header Permissions-Policy "geolocation=(self), microphone=(self), camera=(self), payment=(self), usb=(), magnetometer=(), gyroscope=()" always;

    # Content Security Policy (CSP)
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https: blob:; media-src 'self' https:; connect-src 'self' wss: https:; frame-ancestors 'self'; base-uri 'self'; form-action 'self';" always;

    # Expect-CT (Certificate Transparency)
    add_header Expect-CT "max-age=86400, enforce" always;

    # Remove server signature
    server_tokens off;
    more_clear_headers Server;

    # ... rest of configuration ...
}
```

#### Apache Configuration

Add to your Apache VirtualHost:

```apache
<VirtualHost *:443>
    # ... other configuration ...

    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(self), microphone=(self), camera=(self)"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' wss: https:;"

    # Remove server signature
    ServerSignature Off
    ServerTokens Prod

    # ... rest of configuration ...
</VirtualHost>
```

#### Laravel SecurityHeaders Middleware

Custom middleware already implemented: `app/Http/Middleware/SecurityHeaders.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only apply to HTML responses
        if (str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

            // CSP for production
            if (config('app.env') === 'production') {
                $response->headers->set('Content-Security-Policy',
                    "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                    "style-src 'self' 'unsafe-inline'; " .
                    "img-src 'self' data: https:; " .
                    "font-src 'self' data:; " .
                    "connect-src 'self' wss: https:;"
                );
            }
        }

        return $response;
    }
}
```

Registered in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

### Test Security Headers

```bash
# Test with curl
curl -I https://yoryor.com

# Expected headers:
# Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
# Referrer-Policy: strict-origin-when-cross-origin
# Content-Security-Policy: ...
```

**Online testing tools:**
- SecurityHeaders.com: https://securityheaders.com/?q=yoryor.com
- Mozilla Observatory: https://observatory.mozilla.org/analyze/yoryor.com

---

## HTTPS/SSL Enforcement

### Force HTTPS Everywhere

#### 1. Web Server Level (Nginx)

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name yoryor.com www.yoryor.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;

    # ... SSL configuration ...
}
```

#### 2. Application Level (Laravel)

Add to `.env`:

```env
APP_URL=https://yoryor.com
ASSET_URL=https://yoryor.com
```

Force HTTPS in `app/Providers/AppServiceProvider.php`:

```php
public function boot()
{
    // Force HTTPS in production
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

### SSL/TLS Configuration

#### Strong TLS Configuration (Nginx)

```nginx
# SSL Protocols (disable older versions)
ssl_protocols TLSv1.2 TLSv1.3;

# Strong ciphers (prefer modern, secure ciphers)
ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384';

# Prefer server ciphers
ssl_prefer_server_ciphers off;

# SSL session cache
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 10m;
ssl_session_tickets off;

# OCSP stapling (verify certificate validity)
ssl_stapling on;
ssl_stapling_verify on;
ssl_trusted_certificate /etc/letsencrypt/live/yoryor.com/chain.pem;
resolver 8.8.8.8 8.8.4.4 valid=300s;
resolver_timeout 5s;

# DH parameters (for perfect forward secrecy)
ssl_dhparam /etc/ssl/certs/dhparam.pem;
```

#### Generate DH Parameters

```bash
# Generate 2048-bit DH parameters (takes several minutes)
sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048

# For higher security (4096-bit, takes longer)
sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 4096
```

### SSL Certificate Management

#### Let's Encrypt Auto-Renewal

Certbot automatically creates a systemd timer. Verify:

```bash
# Check timer status
sudo systemctl status certbot.timer

# List timers
sudo systemctl list-timers | grep certbot

# Test renewal
sudo certbot renew --dry-run
```

#### Manual Renewal

```bash
# Renew all certificates
sudo certbot renew

# Force renewal (if needed)
sudo certbot renew --force-renewal

# Renew specific domain
sudo certbot renew --cert-name yoryor.com
```

#### Certificate Monitoring

Create monitoring script `/usr/local/bin/check-ssl-expiry.sh`:

```bash
#!/bin/bash

DOMAIN="yoryor.com"
DAYS_WARNING=30

# Get certificate expiry date
EXPIRY_DATE=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | openssl x509 -noout -enddate | cut -d= -f2)
EXPIRY_EPOCH=$(date -d "$EXPIRY_DATE" +%s)
CURRENT_EPOCH=$(date +%s)
DAYS_LEFT=$(( ($EXPIRY_EPOCH - $CURRENT_EPOCH) / 86400 ))

if [ $DAYS_LEFT -lt $DAYS_WARNING ]; then
    echo "[$(date)] WARNING: SSL certificate expires in $DAYS_LEFT days!" >> /var/log/yoryor-ssl-monitor.log
    # Send alert (email, Slack, etc.)
fi
```

Schedule daily check:
```bash
sudo crontab -e
# Add: 0 9 * * * /usr/local/bin/check-ssl-expiry.sh
```

### Test SSL Configuration

```bash
# Test SSL strength
curl -I https://yoryor.com

# Detailed SSL test
nmap --script ssl-enum-ciphers -p 443 yoryor.com
```

**Online SSL testing:**
- SSL Labs: https://www.ssllabs.com/ssltest/analyze.html?d=yoryor.com
- Expected rating: A or A+

---

## Firewall Rules

### UFW (Uncomplicated Firewall)

#### Initial Setup

```bash
# Install UFW
sudo apt install ufw

# Default policies (deny incoming, allow outgoing)
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (IMPORTANT: do this first!)
sudo ufw allow ssh
# Or specific port: sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow http
sudo ufw allow https
# Or: sudo ufw allow 80/tcp
# Or: sudo ufw allow 443/tcp

# Allow Laravel Reverb (if not behind proxy)
# Only if Reverb is directly accessible (not recommended)
# sudo ufw allow 8080/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status verbose
```

**Expected output:**
```
Status: active
Logging: on (low)
Default: deny (incoming), allow (outgoing), disabled (routed)
New profiles: skip

To                         Action      From
--                         ------      ----
22/tcp                     ALLOW IN    Anywhere
80/tcp                     ALLOW IN    Anywhere
443/tcp                    ALLOW IN    Anywhere
```

#### Advanced Rules

```bash
# Allow from specific IP (e.g., your office)
sudo ufw allow from 203.0.113.0/24 to any port 22

# Rate limit SSH (prevent brute force)
sudo ufw limit ssh

# Allow specific IP to access MySQL (for remote management)
sudo ufw allow from 203.0.113.5 to any port 3306

# Deny specific IP
sudo ufw deny from 198.51.100.10

# Delete rule
sudo ufw status numbered
sudo ufw delete [number]
```

#### Logging

```bash
# Enable logging
sudo ufw logging on

# Set log level (off, low, medium, high, full)
sudo ufw logging medium

# View logs
sudo tail -f /var/log/ufw.log
```

### Fail2Ban (Brute Force Protection)

#### Install and Configure

```bash
# Install Fail2Ban
sudo apt install fail2ban

# Create local configuration
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

#### Basic Configuration

```ini
[DEFAULT]
# Ban duration (in seconds)
bantime = 3600

# Time window for counting failures
findtime = 600

# Max failures before ban
maxretry = 5

# Email notifications (optional)
destemail = admin@yoryor.com
sendername = Fail2Ban
action = %(action_mwl)s

# SSH protection
[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600

# Nginx protection
[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/yoryor-error.log

[nginx-noscript]
enabled = true
port = http,https
logpath = /var/log/nginx/yoryor-access.log

[nginx-badbots]
enabled = true
port = http,https
logpath = /var/log/nginx/yoryor-access.log

[nginx-noproxy]
enabled = true
port = http,https
logpath = /var/log/nginx/yoryor-access.log
```

#### Laravel API Protection

Create custom filter: `/etc/fail2ban/filter.d/laravel-api.conf`

```ini
[Definition]
failregex = .*"POST /api/v1/auth/login.*" 401 .*
            .*"POST /api/v1/auth/verify-otp.*" 401 .*
            .*Rate limit exceeded.* from <HOST>
ignoreregex =
```

Create jail: `/etc/fail2ban/jail.d/laravel-api.conf`

```ini
[laravel-api]
enabled = true
port = http,https
filter = laravel-api
logpath = /var/www/yoryor/storage/logs/laravel.log
maxretry = 5
bantime = 3600
findtime = 600
```

#### Start and Monitor Fail2Ban

```bash
# Start Fail2Ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Check status
sudo fail2ban-client status

# Check specific jail
sudo fail2ban-client status sshd
sudo fail2ban-client status laravel-api

# Unban IP
sudo fail2ban-client set laravel-api unbanip 203.0.113.10

# View banned IPs
sudo fail2ban-client banned
```

### Cloud Firewall (Optional)

If using cloud providers (AWS, DigitalOcean, Vultr, etc.), configure Security Groups/Firewalls:

**Allow inbound:**
- Port 22 (SSH) - from your IP only
- Port 80 (HTTP) - from anywhere (will redirect to HTTPS)
- Port 443 (HTTPS) - from anywhere

**Deny all other inbound traffic**

---

## Rate Limiting Configuration

YorYor implements multi-level rate limiting to prevent abuse.

### Application-Level Rate Limiting

#### API Rate Limiting

Custom middleware: `app/Http/Middleware/ApiRateLimit.php`

```php
// Applied via middleware: api.rate.limit:action_type

// Limits per action type:
'auth_action' => '10,1',          // 10 requests per minute
'like_action' => '100,60',         // 100 requests per hour
'message_action' => '500,60',      // 500 requests per hour
'call_action' => '50,60',          // 50 requests per hour
'panic_activation' => '5,1440',    // 5 requests per day
'profile_update' => '30,60',       // 30 requests per hour
'block_action' => '20,60',         // 20 requests per hour
'report_action' => '10,60',        // 10 requests per hour
'verification_submit' => '3,1440', // 3 requests per day
'password_change' => '5,60',       // 5 requests per hour
'email_change' => '3,1440',        // 3 requests per day
'account_deletion' => '1,1440',    // 1 request per day
'data_export' => '2,10080',        // 2 requests per week
'location_update' => '100,60',     // 100 requests per hour
'story_action' => '20,1440',       // 20 requests per day
```

#### Chat Rate Limiting

Custom middleware: `app/Http/Middleware/ChatRateLimit.php`

```php
'create_chat' => '50,60',          // 50 chats per hour
'send_message' => '500,60',        // 500 messages per hour
'mark_read' => '1000,60',          // 1000 read marks per hour
'edit_message' => '100,60',        // 100 edits per hour
'delete_message' => '100,60',      // 100 deletions per hour
```

#### Route Configuration

```php
// routes/api.php

// Authentication routes with rate limiting
Route::middleware('api.rate.limit:auth_action')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Chat routes with chat-specific rate limiting
Route::middleware(['auth:sanctum', 'chat.rate.limit:send_message'])->group(function () {
    Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage']);
});
```

### Server-Level Rate Limiting (Nginx)

```nginx
# Define rate limit zones
http {
    # General API rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;

    # Login endpoint (stricter)
    limit_req_zone $binary_remote_addr zone=login:10m rate=10r/m;

    # File upload (very strict)
    limit_req_zone $binary_remote_addr zone=uploads:10m rate=5r/m;

    # Connection limits per IP
    limit_conn_zone $binary_remote_addr zone=addr:10m;

    server {
        # ... other configuration ...

        # Limit connections per IP
        limit_conn addr 10;

        # API endpoints
        location /api/ {
            limit_req zone=api burst=20 nodelay;
            try_files $uri $uri/ /index.php?$query_string;
        }

        # Login endpoint
        location /api/v1/auth/login {
            limit_req zone=login burst=5 nodelay;
            try_files $uri $uri/ /index.php?$query_string;
        }

        # Upload endpoints
        location ~ ^/api/v1/(photos|media) {
            limit_req zone=uploads burst=3 nodelay;
            try_files $uri $uri/ /index.php?$query_string;
        }
    }
}
```

### DDoS Protection

#### Nginx Anti-DDoS Configuration

```nginx
# Connection timeouts
client_body_timeout 12;
client_header_timeout 12;
keepalive_timeout 15;
send_timeout 10;

# Buffer sizes (prevent buffer overflow attacks)
client_body_buffer_size 1K;
client_header_buffer_size 1k;
client_max_body_size 10M;
large_client_header_buffers 2 1k;

# Limit request methods
if ($request_method !~ ^(GET|HEAD|POST|PUT|DELETE|PATCH)$ ) {
    return 405;
}
```

#### Cloud-Based DDoS Protection

Consider using:
- **Cloudflare**: Free DDoS protection and CDN
- **AWS Shield**: AWS-specific protection
- **Akamai**: Enterprise DDoS mitigation

**Cloudflare Setup:**
1. Sign up at cloudflare.com
2. Add your domain
3. Update nameservers
4. Enable "Under Attack" mode if under DDoS

---

## CORS Setup

### Laravel CORS Configuration

CORS already configured in `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => env('CORS_ALLOWED_ORIGINS', '*'),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
```

### Production CORS Configuration

Update `.env` for production:

```env
# Restrict to your frontend domains
CORS_ALLOWED_ORIGINS=https://yoryor.com,https://www.yoryor.com,https://app.yoryor.com
```

Or in `config/cors.php`:

```php
'allowed_origins' => [
    'https://yoryor.com',
    'https://www.yoryor.com',
    'https://app.yoryor.com',
],

'allowed_headers' => [
    'Content-Type',
    'X-Requested-With',
    'Authorization',
    'Accept',
    'Origin',
],
```

### Nginx CORS Headers (Alternative)

```nginx
location /api/ {
    # CORS headers
    add_header 'Access-Control-Allow-Origin' 'https://yoryor.com' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, PATCH, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type, X-Requested-With' always;
    add_header 'Access-Control-Allow-Credentials' 'true' always;

    # Preflight requests
    if ($request_method = 'OPTIONS') {
        add_header 'Access-Control-Max-Age' 1728000;
        add_header 'Content-Type' 'text/plain; charset=utf-8';
        add_header 'Content-Length' 0;
        return 204;
    }

    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## Authentication Security

### Multi-Factor Authentication (2FA)

YorYor implements multi-layer authentication:

#### 1. Password Requirements

Configured in `app/Services/AuthService.php`:

```php
// Password rules
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    Password::min(8)
        ->mixedCase()
        ->numbers()
        ->symbols()
        ->uncompromised(),
],
```

**Requirements:**
- Minimum 8 characters
- Mixed case (uppercase + lowercase)
- Numbers
- Special symbols
- Not in common password lists (pwned passwords check)

#### 2. OTP (One-Time Password)

After initial login, OTP sent via email/SMS:

```php
// Generate 6-digit OTP
$otp = OtpService::generate($user);

// Send via email/SMS
// Valid for 10 minutes
```

#### 3. Google Authenticator (Optional)

```php
// Generate secret
$secret = TwoFactorAuthService::generateSecret($user);

// Verify TOTP code
$valid = TwoFactorAuthService::verify($user, $code);
```

### Session Security

#### Session Configuration

Update `.env`:

```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.yoryor.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

**Explanation:**
- `SESSION_SECURE_COOKIE=true`: Only transmit over HTTPS
- `SESSION_HTTP_ONLY=true`: Prevent JavaScript access (XSS protection)
- `SESSION_SAME_SITE=lax`: CSRF protection

#### PHP Session Configuration

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = "Lax"
session.use_strict_mode = 1
session.use_only_cookies = 1
session.cookie_lifetime = 0
session.gc_maxlifetime = 7200
```

### API Token Security (Sanctum)

#### Token Configuration

```env
SANCTUM_STATEFUL_DOMAINS=yoryor.com,www.yoryor.com,app.yoryor.com
```

#### Token Expiration

Add to `config/sanctum.php`:

```php
'expiration' => 60 * 24, // 24 hours
```

Implement token refresh endpoint:

```php
Route::post('/auth/refresh-token', function (Request $request) {
    $user = $request->user();

    // Revoke old token
    $request->user()->currentAccessToken()->delete();

    // Issue new token
    $token = $user->createToken('app-token', ['*'], now()->addHours(24));

    return response()->json([
        'token' => $token->plainTextToken,
        'expires_at' => now()->addHours(24),
    ]);
})->middleware('auth:sanctum');
```

### Account Lockout

Prevent brute force attacks:

```php
// In AuthController
use Illuminate\Support\Facades\RateLimiter;

public function login(Request $request)
{
    $key = 'login:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        return response()->json([
            'message' => "Too many login attempts. Try again in {$seconds} seconds."
        ], 429);
    }

    // Attempt login
    if (!$this->authService->authenticate($credentials)) {
        RateLimiter::hit($key, 900); // 15 minutes lockout
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    RateLimiter::clear($key);
    // ... successful login
}
```

### Password Reset Security

```php
// Limit password reset requests
Route::post('/password/email', function (Request $request) {
    $key = 'password-reset:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 3)) {
        return response()->json(['message' => 'Too many requests'], 429);
    }

    RateLimiter::hit($key, 3600); // 1 hour

    // Send password reset email
})->middleware('api.rate.limit:password_change');
```

---

## Database Security

### Connection Security

#### Use Strong Passwords

```env
DB_PASSWORD=GENERATE_WITH_openssl_rand_-base64_32
```

Generate strong password:
```bash
openssl rand -base64 32
```

#### Restrict Database Access

```sql
-- Only allow local connections
CREATE USER 'yoryor_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON yoryor.* TO 'yoryor_user'@'localhost';

-- For remote access (use specific IP)
CREATE USER 'yoryor_user'@'203.0.113.5' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON yoryor.* TO 'yoryor_user'@'203.0.113.5';
```

#### Disable Remote Root Access

```sql
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
```

### MySQL Hardening

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
# Bind to localhost only
bind-address = 127.0.0.1

# Disable LOAD DATA LOCAL INFILE
local-infile = 0

# Disable symbolic links
symbolic-links = 0

# Log suspicious queries
log-warnings = 2

# Enable binary logging (for point-in-time recovery)
log-bin = /var/log/mysql/mysql-bin.log
expire_logs_days = 7

# Enable slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-queries.log
long_query_time = 2
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

### Encryption at Rest

#### Database Encryption (MySQL 8.0+)

```sql
-- Create encrypted tablespace
CREATE TABLESPACE yoryor_encrypted
ADD DATAFILE 'yoryor_encrypted.ibd'
ENCRYPTION = 'Y';

-- Create table in encrypted tablespace
CREATE TABLE sensitive_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data TEXT
) TABLESPACE yoryor_encrypted;
```

#### Application-Level Encryption

Encrypt sensitive fields (SSN, credit cards, etc.):

```php
use Illuminate\Support\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString($sensitiveData);

// Decrypt
$decrypted = Crypt::decryptString($encrypted);
```

Laravel model casting:

```php
class User extends Model
{
    protected $casts = [
        'ssn' => 'encrypted',
        'credit_card' => 'encrypted',
    ];
}
```

### SQL Injection Prevention

**Always use:**
1. **Eloquent ORM** (parameterized queries)
2. **Query Builder** (parameterized)
3. **Prepared statements** (for raw queries)

**Never:**
```php
// NEVER do this!
DB::select("SELECT * FROM users WHERE email = '$email'");
```

**Instead:**
```php
// Good: Eloquent
User::where('email', $email)->first();

// Good: Query Builder
DB::table('users')->where('email', $email)->first();

// Good: Raw with bindings
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

### Database Monitoring

Enable query logging for suspicious activity:

```php
// In AppServiceProvider (development only)
if (config('app.env') === 'local') {
    DB::listen(function ($query) {
        Log::info('Query executed', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
        ]);
    });
}
```

---

## File Upload Security

### Upload Configuration

#### Limit File Types

```php
// In validation rules
'photo' => [
    'required',
    'file',
    'mimes:jpg,jpeg,png,webp',
    'max:10240', // 10MB
],

'document' => [
    'required',
    'file',
    'mimes:pdf,doc,docx',
    'max:5120', // 5MB
],
```

#### Validate File Contents (Not Just Extension)

```php
use Illuminate\Support\Facades\Storage;

public function validateImage($file)
{
    // Check MIME type
    $mimeType = $file->getMimeType();
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mimeType, $allowedMimes)) {
        throw new \Exception('Invalid file type');
    }

    // Verify image can be processed
    try {
        $image = imagecreatefromstring(file_get_contents($file));
        if (!$image) {
            throw new \Exception('Invalid image file');
        }
        imagedestroy($image);
    } catch (\Exception $e) {
        throw new \Exception('Corrupted or malicious image file');
    }

    return true;
}
```

### Prevent Malicious Uploads

#### Image Processing Service

```php
// app/Services/ImageProcessingService.php

public function process($file)
{
    // Validate image
    $this->validateImage($file);

    // Re-encode image (strips EXIF data and potential malware)
    $image = Image::make($file);

    // Resize to prevent huge images
    if ($image->width() > 2048 || $image->height() > 2048) {
        $image->resize(2048, 2048, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    // Save with controlled quality
    $filename = Str::uuid() . '.jpg';
    $path = "photos/{$filename}";

    Storage::disk('r2')->put(
        $path,
        (string) $image->encode('jpg', 85)
    );

    return $path;
}
```

### Storage Security

#### Cloudflare R2 Security

```php
// Private bucket - generate signed URLs
$url = Storage::disk('r2')->temporaryUrl(
    'private/document.pdf',
    now()->addMinutes(30)
);

// Public bucket - direct URLs
$url = Storage::disk('r2')->url('public/photo.jpg');
```

#### Local Storage Security

```bash
# Ensure storage is not publicly accessible
sudo chmod 755 /var/www/yoryor/storage
sudo chmod 644 /var/www/yoryor/storage/app/*

# Only public/storage should be accessible via symlink
```

Nginx configuration:

```nginx
# Deny direct access to storage
location ~ /storage/ {
    deny all;
}

# Allow public/storage (symlinked)
location ~ /public/storage/ {
    # Allow access
}
```

### Virus Scanning (Optional)

For production environments with user-uploaded documents:

```bash
# Install ClamAV
sudo apt install clamav clamav-daemon

# Update virus definitions
sudo freshclam

# Start daemon
sudo systemctl start clamav-daemon
```

PHP integration:

```php
use Xenolope\Quahog\Client;

public function scanFile($filePath)
{
    $client = new Client('unix:///var/run/clamav/clamd.ctl');

    $result = $client->scanFile($filePath);

    if ($result['status'] !== 'OK') {
        throw new \Exception('Virus detected: ' . $result['reason']);
    }

    return true;
}
```

---

## API Security Best Practices

### Input Validation

Always validate all input:

```php
// Use Form Requests
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\+[1-9]\d{1,14}$/'],
            'date_of_birth' => ['required', 'date', 'before:today'],
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => 'Phone must be in E.164 format',
        ];
    }
}
```

### Output Encoding

Laravel automatically escapes output in Blade:

```blade
{{-- Escaped (safe) --}}
{{ $user->name }}

{{-- Unescaped (dangerous - only for trusted content) --}}
{!! $trustedHtml !!}
```

### Mass Assignment Protection

```php
class User extends Model
{
    // Whitelist fillable fields
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    // Or blacklist guarded fields
    protected $guarded = [
        'id',
        'is_admin',
        'is_verified',
    ];
}
```

### API Versioning

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // v1 routes
});

Route::prefix('v2')->group(function () {
    // v2 routes
});
```

### API Response Consistency

```php
// Success response
return response()->json([
    'type' => 'user',
    'id' => $user->id,
    'attributes' => $user->toArray(),
], 200);

// Error response
return response()->json([
    'status' => 'error',
    'message' => 'Resource not found',
    'errors' => [],
], 404);
```

### Request ID Tracking

Add to middleware:

```php
public function handle($request, Closure $next)
{
    $requestId = Str::uuid()->toString();
    $request->headers->set('X-Request-ID', $requestId);

    $response = $next($request);

    $response->headers->set('X-Request-ID', $requestId);

    return $response;
}
```

---

## Server Hardening

### Disable Unnecessary Services

```bash
# List running services
sudo systemctl list-units --type=service --state=running

# Disable unnecessary services
sudo systemctl disable apache2  # If using Nginx
sudo systemctl disable postfix  # If not using local mail
```

### Keep System Updated

```bash
# Enable automatic security updates
sudo apt install unattended-upgrades

# Configure
sudo dpkg-reconfigure -plow unattended-upgrades

# Manually update
sudo apt update && sudo apt upgrade -y
```

### SSH Hardening

Edit `/etc/ssh/sshd_config`:

```bash
# Disable root login
PermitRootLogin no

# Use SSH keys only (disable password auth)
PasswordAuthentication no
PubkeyAuthentication yes

# Disable empty passwords
PermitEmptyPasswords no

# Use protocol 2 only
Protocol 2

# Change default port (security through obscurity)
Port 2222

# Limit user logins
AllowUsers youruser

# Disable X11 forwarding
X11Forwarding no

# Set idle timeout
ClientAliveInterval 300
ClientAliveCountMax 2
```

Restart SSH:
```bash
sudo systemctl restart sshd
```

### File Permissions

```bash
# Restrict file permissions
sudo chmod 644 /var/www/yoryor/.env
sudo chmod 755 /var/www/yoryor
sudo chmod -R 755 /var/www/yoryor/public
sudo chmod -R 775 /var/www/yoryor/storage
sudo chmod -R 775 /var/www/yoryor/bootstrap/cache

# Ownership
sudo chown -R www-data:www-data /var/www/yoryor
```

### Disable PHP Functions

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

**Note**: Some Laravel features require these functions. Test thoroughly.

### PHP Hardening

```ini
; Hide PHP version
expose_php = Off

; Disable dangerous functions
allow_url_fopen = Off
allow_url_include = Off

; Error handling (production)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; File upload limits
upload_max_filesize = 10M
max_file_uploads = 5

; Memory limits
memory_limit = 256M

; Execution limits
max_execution_time = 300
max_input_time = 60
```

---

## Regular Security Audits

### Automated Vulnerability Scanning

#### Composer Security Checker

```bash
# Check for known vulnerabilities in PHP dependencies
composer audit

# Install security advisories checker
composer require --dev roave/security-advisories:dev-latest
```

#### NPM Audit

```bash
# Check for vulnerable packages
npm audit

# Fix automatically (use with caution)
npm audit fix
```

### Code Quality Tools

#### PHP Stan (Static Analysis)

```bash
# Install
composer require --dev phpstan/phpstan

# Run analysis
./vendor/bin/phpstan analyse app
```

#### Laravel Pint (Code Style)

```bash
# Fix code style
./vendor/bin/pint
```

### Penetration Testing

Schedule regular penetration testing:

**Tools:**
- **OWASP ZAP**: Free web app security scanner
- **Burp Suite**: Professional penetration testing
- **Nmap**: Network scanning
- **SQLMap**: SQL injection testing

**Example OWASP ZAP scan:**
```bash
# Install ZAP
sudo apt install zaproxy

# Run automated scan
zap-cli quick-scan https://yoryor.com
```

### Security Checklist (Monthly)

- [ ] Update all dependencies (`composer update`, `npm update`)
- [ ] Review security logs
- [ ] Check SSL certificate expiration
- [ ] Review user permissions
- [ ] Audit database access
- [ ] Review firewall rules
- [ ] Check for failed login attempts
- [ ] Review API usage patterns
- [ ] Test backup restoration
- [ ] Review error logs for anomalies

---

## Incident Response Plan

### Preparation

#### Incident Response Team

- **Primary Contact**: DevOps Lead
- **Secondary Contact**: CTO
- **Security Officer**: Security Lead
- **Communication Lead**: Product Manager

#### Communication Channels

- **Emergency**: Slack #security-incidents
- **Email**: security@yoryor.com
- **Phone**: Emergency hotline

### Detection

#### Monitoring Alerts

```bash
# Setup alerts for:
# 1. Unusual traffic patterns
# 2. High error rates
# 3. Failed login attempts
# 4. Database errors
# 5. Disk space warnings
# 6. CPU/Memory spikes
```

### Response Procedure

#### 1. Identify and Assess

```bash
# Check application logs
tail -f /var/www/yoryor/storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/yoryor-error.log
tail -f /var/log/nginx/yoryor-access.log

# Check system logs
sudo tail -f /var/log/syslog

# Check for security breaches
sudo last -a  # Recent logins
sudo lastb    # Failed login attempts
```

#### 2. Contain the Incident

```bash
# Enable maintenance mode
php artisan down

# Block suspicious IPs (temporarily)
sudo ufw deny from 203.0.113.10

# Revoke user tokens (if account compromised)
php artisan tinker
>>> User::find(123)->tokens()->delete();

# Disable affected services
sudo supervisorctl stop yoryor-worker:*
```

#### 3. Eradicate Threat

```bash
# Remove malicious files
sudo rm -f /var/www/yoryor/public/malicious.php

# Update compromised passwords
# Reset API keys
# Patch vulnerabilities

# Restore from clean backup (if needed)
```

#### 4. Recovery

```bash
# Restore normal operations
php artisan up
sudo supervisorctl start yoryor-worker:*

# Monitor closely for 24-48 hours
```

#### 5. Post-Incident Review

- Document what happened
- How was it detected?
- What was the response time?
- What worked well?
- What needs improvement?
- Update procedures

---

## GDPR Compliance

### Data Protection Principles

1. **Lawfulness, fairness, transparency**
2. **Purpose limitation**
3. **Data minimization**
4. **Accuracy**
5. **Storage limitation**
6. **Integrity and confidentiality**
7. **Accountability**

### User Rights Implementation

#### Right to Access

```php
// Data export endpoint
Route::post('/account/export-data', [AccountController::class, 'exportData'])
    ->middleware(['auth:sanctum', 'api.rate.limit:data_export']);

public function exportData(Request $request)
{
    $user = $request->user();

    // Create export job
    DataExportRequest::create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    // Process in background
    dispatch(new ProcessDataExport($user));

    return response()->json([
        'message' => 'Your data export has been queued. You will receive an email when ready.',
    ]);
}
```

#### Right to Erasure (Right to be Forgotten)

```php
Route::delete('/account', [AccountController::class, 'deleteAccount'])
    ->middleware(['auth:sanctum', 'api.rate.limit:account_deletion']);

public function deleteAccount(Request $request)
{
    $user = $request->user();

    // Anonymize data (GDPR compliant deletion)
    DB::transaction(function () use ($user) {
        // Delete personal data
        $user->profile()->delete();
        $user->photos()->delete();
        $user->preferences()->delete();

        // Anonymize account
        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted_' . Str::uuid() . '@deleted.com',
            'phone' => null,
            'deleted_at' => now(),
        ]);

        // Revoke tokens
        $user->tokens()->delete();
    });

    return response()->json([
        'message' => 'Account deleted successfully',
    ]);
}
```

#### Right to Rectification

Users can update their data via profile endpoints.

#### Right to Data Portability

Export in machine-readable format (JSON):

```php
public function exportUserData($user)
{
    return [
        'personal_data' => [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            // ... other fields
        ],
        'profile' => $user->profile->toArray(),
        'preferences' => $user->preferences->toArray(),
        'photos' => $user->photos->pluck('url')->toArray(),
        'messages' => $user->messages->map(function ($message) {
            return [
                'text' => $message->text,
                'sent_at' => $message->created_at,
            ];
        }),
    ];
}
```

### Data Retention Policy

```php
// Delete old data automatically
// Schedule in app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Delete old OTP codes (older than 24 hours)
    $schedule->call(function () {
        OtpCode::where('created_at', '<', now()->subDay())->delete();
    })->daily();

    // Delete expired sessions (older than 30 days)
    $schedule->call(function () {
        Session::where('last_activity', '<', now()->subDays(30))->delete();
    })->daily();

    // Anonymize deleted accounts (after 30 days grace period)
    $schedule->call(function () {
        User::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(30))
            ->each(function ($user) {
                $user->forceDelete();
            });
    })->daily();
}
```

### Privacy Policy & Terms

- **Privacy Policy**: `/privacy` route
- **Terms of Service**: `/terms` route
- **Cookie Policy**: `/cookies` route

Require acceptance during registration:

```php
'accepted_terms' => 'required|accepted',
'accepted_privacy' => 'required|accepted',
```

---

## Security Monitoring

### Application Monitoring

#### Laravel Logs

```bash
# Monitor application logs in real-time
tail -f /var/www/yoryor/storage/logs/laravel.log

# Filter for errors
grep "ERROR" /var/www/yoryor/storage/logs/laravel.log

# Check failed jobs
php artisan queue:failed
```

#### Access Logs Analysis

```bash
# Most accessed endpoints
awk '{print $7}' /var/log/nginx/yoryor-access.log | sort | uniq -c | sort -rn | head -20

# Top IP addresses
awk '{print $1}' /var/log/nginx/yoryor-access.log | sort | uniq -c | sort -rn | head -20

# 404 errors
awk '$9 == 404' /var/log/nginx/yoryor-access.log

# Slow requests (>1 second)
awk '$NF > 1.0' /var/log/nginx/yoryor-access.log
```

### External Monitoring Services

#### Uptime Monitoring

- **UptimeRobot**: Free uptime monitoring (5-minute intervals)
- **Pingdom**: Website and API monitoring
- **StatusCake**: Uptime and performance monitoring

#### Application Performance Monitoring (APM)

- **New Relic**: Full-stack observability
- **Datadog**: Infrastructure and application monitoring
- **Sentry**: Error tracking and performance monitoring

**Sentry Integration:**

```bash
composer require sentry/sentry-laravel
```

Configure in `.env`:

```env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Security Information and Event Management (SIEM)

For enterprise deployments, consider:
- **Splunk**: Log analysis and SIEM
- **ELK Stack**: Elasticsearch, Logstash, Kibana
- **Graylog**: Open-source log management

---

## Security Best Practices Summary

### Development Practices

- [ ] Use version control (Git)
- [ ] Code reviews for all changes
- [ ] Automated testing (unit, integration, security)
- [ ] Dependency vulnerability scanning
- [ ] Static code analysis
- [ ] Never commit secrets to repository
- [ ] Use environment variables for configuration
- [ ] Follow OWASP Top 10 guidelines

### Deployment Practices

- [ ] HTTPS/SSL everywhere
- [ ] Strong authentication (passwords, 2FA, MFA)
- [ ] Input validation and sanitization
- [ ] Output encoding
- [ ] SQL injection prevention (use ORM)
- [ ] CSRF protection (enabled by default)
- [ ] XSS protection (auto-escaping)
- [ ] Rate limiting
- [ ] Security headers
- [ ] Regular updates and patches

### Infrastructure Practices

- [ ] Firewall configured
- [ ] Fail2Ban for brute force protection
- [ ] SSH key-only authentication
- [ ] Separate database user with limited permissions
- [ ] Encrypted backups
- [ ] Log monitoring
- [ ] Incident response plan
- [ ] Regular security audits
- [ ] Penetration testing (annually)
- [ ] GDPR compliance

### Monitoring Practices

- [ ] Application logs
- [ ] Access logs
- [ ] Error logs
- [ ] Security logs
- [ ] Performance metrics
- [ ] Uptime monitoring
- [ ] Alert notifications
- [ ] Regular log reviews

---

*This security guide ensures YorYor is protected against common vulnerabilities and threats while maintaining compliance with industry standards and regulations.*

**Last Updated:** October 2025
**Version:** 1.0
**Security Officer:** YorYor Security Team
**Next Review:** January 2026
