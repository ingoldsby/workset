# Staging Deployment Guide - staging.workset.kneebone.com.au

This guide covers the complete setup and deployment process for the Workset staging environment on DigitalOcean with Cloudflare DNS.

## Prerequisites

- **DigitalOcean Account** with an existing droplet
- **Cloudflare Account** with DNS management for kneebone.com.au
- **SSH Access** to your DigitalOcean droplet
- **GitHub Access** to the workset repository

## Overview

The staging environment consists of:
- **Laravel 12.x** application (PHP 8.4)
- **MySQL 8.0** database
- **Redis** for cache/sessions/queues
- **Meilisearch** for full-text search
- **Horizon** for queue management
- **Reverb** for WebSockets
- **Nginx** web server with SSL
- All services containerised with Docker Compose

---

## Part 1: DigitalOcean Droplet Initial Setup

### 1.1 Droplet Requirements

**Recommended Specifications:**
- **Size**: Basic Droplet (2 GB RAM / 1 CPU minimum)
- **OS**: Ubuntu 22.04 LTS or 24.04 LTS
- **Region**: Choose closest to your users
- **Firewall**: Allow ports 22 (SSH), 80 (HTTP), 443 (HTTPS)

### 1.2 Initial Server Setup

SSH into your droplet:

```bash
ssh root@your-droplet-ip
```

Update the system:

```bash
apt update && apt upgrade -y
```

Create a deployment user (recommended, not running as root):

```bash
# Create user
adduser deploy
usermod -aG sudo deploy

# Setup SSH key authentication
mkdir -p /home/deploy/.ssh
cp /root/.ssh/authorized_keys /home/deploy/.ssh/
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys

# Switch to deploy user
su - deploy
```

### 1.3 Install Docker and Docker Compose

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add current user to docker group
sudo usermod -aG docker $USER

# Activate group (or logout/login)
newgrp docker

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Verify installations
docker --version
docker compose version
```

### 1.4 Install Additional Dependencies

```bash
# Git
sudo apt install git -y

# Certbot (for SSL certificates)
sudo apt install certbot -y

# htpasswd (for HTTP basic auth)
sudo apt install apache2-utils -y
```

---

## Part 2: Cloudflare DNS Configuration

### 2.1 Add DNS Record

1. Log in to **Cloudflare Dashboard**
2. Select your domain: **kneebone.com.au**
3. Go to **DNS** → **Records**
4. Click **Add record**

**Configuration:**
- **Type**: A
- **Name**: staging.workset
- **IPv4 address**: Your DigitalOcean droplet IP
- **Proxy status**: ☁️ Proxied (orange cloud) - RECOMMENDED
- **TTL**: Auto

Click **Save**

### 2.2 SSL/TLS Settings (Cloudflare)

1. Go to **SSL/TLS** tab
2. Set **SSL/TLS encryption mode** to: **Full (strict)** or **Full**
3. Enable **Always Use HTTPS**
4. Enable **Automatic HTTPS Rewrites**

> **Note**: With Cloudflare proxy enabled, you can use Cloudflare's Origin Certificates or Let's Encrypt on your server.

### 2.3 Verify DNS Propagation

```bash
# Check DNS resolution
dig staging.workset.kneebone.com.au

# Or use online tools
# https://dnschecker.org
```

---

## Part 3: Application Deployment

### 3.1 Clone Repository

```bash
# Create directory structure
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www

# Clone repository
cd /var/www
git clone https://github.com/ingoldsby/workset.git
cd workset

# Checkout appropriate branch (if needed)
git checkout main
```

### 3.2 Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env
```

**Critical Environment Variables for Staging:**

```env
APP_NAME="Workset Staging"
APP_ENV=staging
APP_KEY=  # Will generate in next step
APP_DEBUG=false
APP_URL=https://staging.workset.kneebone.com.au
APP_TIMEZONE=Australia/Sydney

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=workset_staging
DB_USERNAME=workset
DB_PASSWORD=<generate-secure-password>

# Cache & Sessions (Redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue (Horizon)
HORIZON_ENABLED=true
HORIZON_PREFIX=horizon:staging

# Scout (Meilisearch)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=

# Reverb (WebSockets)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=staging-app-id
REVERB_APP_KEY=<generate-key>
REVERB_APP_SECRET=<generate-secret>
REVERB_HOST=staging.workset.kneebone.com.au
REVERB_PORT=443
REVERB_SCHEME=https

# Mail (configure as needed)
MAIL_MAILER=log  # For staging, use log driver
MAIL_FROM_ADDRESS=noreply@workset.kneebone.com.au
MAIL_FROM_NAME="${APP_NAME}"

# hCaptcha (get staging keys from hCaptcha)
HCAPTCHA_SITE_KEY=10000000-ffff-ffff-ffff-000000000001  # Test key
HCAPTCHA_SECRET_KEY=0x0000000000000000000000000000000000000000  # Test key

# Analytics (optional for staging)
FATHOM_SITE_ID=
```

### 3.3 Generate Application Key

```bash
# Build the app container first
docker compose build app

# Generate app key
docker compose run --rm app php artisan key:generate
```

### 3.4 SSL Certificate Setup

**Option A: Let's Encrypt (Recommended if not using Cloudflare proxy)**

```bash
# Stop nginx if running
docker compose down

# Generate certificate
sudo certbot certonly --standalone \
  -d staging.workset.kneebone.com.au \
  --agree-tos \
  --email your-email@example.com

# Create certificates directory
mkdir -p docker/nginx/certs

# Copy certificates
sudo cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/fullchain.pem \
  docker/nginx/certs/staging.workset.kneebone.com.au.crt
sudo cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/privkey.pem \
  docker/nginx/certs/staging.workset.kneebone.com.au.key

# Set permissions
sudo chown -R $USER:$USER docker/nginx/certs
chmod 644 docker/nginx/certs/*.crt
chmod 600 docker/nginx/certs/*.key
```

**Option B: Cloudflare Origin Certificate**

1. In Cloudflare Dashboard, go to **SSL/TLS** → **Origin Server**
2. Click **Create Certificate**
3. Select **Let Cloudflare generate a private key and a CSR**
4. Set hostnames: `staging.workset.kneebone.com.au`
5. Set validity: 15 years
6. Click **Create**
7. Save the certificate and private key:

```bash
mkdir -p docker/nginx/certs

# Save origin certificate
nano docker/nginx/certs/staging.workset.kneebone.com.au.crt
# Paste the certificate

# Save private key
nano docker/nginx/certs/staging.workset.kneebone.com.au.key
# Paste the private key

# Set permissions
chmod 644 docker/nginx/certs/*.crt
chmod 600 docker/nginx/certs/*.key
```

### 3.5 Create Nginx Configuration for Staging

```bash
nano docker/nginx/staging.conf
```

**Nginx Configuration:**

```nginx
# HTTP -> HTTPS Redirect
server {
    listen 80;
    server_name staging.workset.kneebone.com.au;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    server_name staging.workset.kneebone.com.au;

    root /var/www/html/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/nginx/certs/staging.workset.kneebone.com.au.crt;
    ssl_certificate_key /etc/nginx/certs/staging.workset.kneebone.com.au.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # HTTP Basic Auth for staging
    auth_basic "Workset Staging Environment";
    auth_basic_user_file /etc/nginx/.htpasswd;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    charset utf-8;

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Max upload size
    client_max_body_size 20M;
}
```

### 3.6 Create HTTP Basic Auth Password

```bash
# Create .htpasswd file (replace 'jim' and password as needed)
htpasswd -c docker/nginx/.htpasswd jim

# You'll be prompted to enter password: empirefitness
```

### 3.7 Update docker-compose.yml for Production Use

The existing docker-compose.yml is configured for development. No changes needed, but ensure volumes are properly mounted.

### 3.8 Build and Start Services

```bash
# Build all containers
docker compose build

# Start all services in detached mode
docker compose up -d

# Check service status
docker compose ps
```

### 3.9 Run Initial Setup Commands

```bash
# Install Composer dependencies (production mode)
docker compose exec app composer install --no-dev --optimize-autoloader

# Run database migrations
docker compose exec app php artisan migrate --force

# Optimise Laravel
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache

# Create storage symlink
docker compose exec app php artisan storage:link

# Set correct permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 3.10 Seed Database (Optional)

```bash
# If you need sample data for testing
docker compose exec app php artisan db:seed --force
```

---

## Part 4: Verification and Testing

### 4.1 Check All Services Are Running

```bash
docker compose ps

# Expected output: All services should show "Up" status
# - workset-app
# - workset-nginx
# - workset-mysql
# - workset-redis
# - workset-meilisearch
# - workset-horizon
# - workset-reverb
# - workset-scheduler
```

### 4.2 Run Health Checks

```bash
# Run comprehensive health check script
./scripts/deploy/health-check.sh staging
```

### 4.3 Test Application Access

1. **Open browser**: https://staging.workset.kneebone.com.au
2. **Enter credentials**: jim / empirefitness (HTTP Basic Auth)
3. **Verify**:
   - Application loads
   - No SSL warnings
   - Can register/login
   - Database connectivity works

### 4.4 Test Horizon Dashboard

Visit: https://staging.workset.kneebone.com.au/horizon

(HTTP Basic Auth required)

### 4.5 Check Logs

```bash
# Application logs
docker compose logs -f app

# Nginx access/error logs
docker compose logs -f nginx

# All services
docker compose logs -f
```

---

## Part 5: Ongoing Maintenance

### 5.1 Deploy Updates

```bash
cd /var/www/workset

# Pull latest code
git pull origin main

# Rebuild containers if Dockerfile changed
docker compose build

# Stop services
docker compose down

# Start services
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force

# Clear and rebuild caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Restart queue workers
docker compose exec app php artisan horizon:terminate
```

### 5.2 Database Backups

```bash
# Manual backup
./scripts/backup/backup-database.sh

# Setup automated daily backups via cron
crontab -e

# Add this line:
0 2 * * * cd /var/www/workset && ./scripts/backup/backup-database.sh
```

### 5.3 SSL Certificate Renewal

**If using Let's Encrypt:**

```bash
# Certbot auto-renewal is enabled by default
# Test renewal
sudo certbot renew --dry-run

# Manual renewal (if needed)
sudo certbot renew
sudo cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/fullchain.pem \
  /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt
sudo cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/privkey.pem \
  /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.key
docker compose restart nginx
```

**If using Cloudflare Origin Certificate:**

No renewal needed - valid for 15 years.

### 5.4 Monitor Resource Usage

```bash
# Container resource usage
docker stats

# Disk usage
df -h
docker system df

# Clean up unused Docker resources
docker system prune -a --volumes
```

### 5.5 View Application Logs

```bash
# Laravel logs
docker compose exec app tail -f storage/logs/laravel.log

# Horizon logs
docker compose logs -f horizon

# Reverb logs
docker compose logs -f reverb
```

---

## Part 6: Troubleshooting

### Issue: Cannot connect to staging.workset.kneebone.com.au

**Check:**
1. DNS propagation: `dig staging.workset.kneebone.com.au`
2. Cloudflare proxy status (orange cloud enabled)
3. Firewall rules on DigitalOcean droplet
4. Nginx is running: `docker compose ps nginx`

### Issue: SSL certificate errors

**Fix:**
```bash
# Verify certificate files exist
ls -la docker/nginx/certs/

# Check Nginx configuration
docker compose exec nginx nginx -t

# Restart Nginx
docker compose restart nginx
```

### Issue: Application returns 500 errors

**Debug:**
```bash
# Check Laravel logs
docker compose exec app tail -50 storage/logs/laravel.log

# Check permissions
docker compose exec app ls -la storage/

# Clear caches
docker compose exec app php artisan optimize:clear
```

### Issue: Database connection failed

**Fix:**
```bash
# Check MySQL is running
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Verify database credentials in .env
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: Horizon/queues not processing

**Fix:**
```bash
# Restart Horizon
docker compose restart horizon

# Check Horizon logs
docker compose logs -f horizon

# Clear failed jobs
docker compose exec app php artisan queue:flush
```

---

## Part 7: Security Checklist

- [ ] HTTP Basic Auth enabled (`auth_basic` in nginx config)
- [ ] SSL/HTTPS enforced (HTTP redirects to HTTPS)
- [ ] Strong database passwords in `.env`
- [ ] `APP_DEBUG=false` in production
- [ ] Firewall configured (only ports 22, 80, 443 open)
- [ ] Regular security updates: `apt update && apt upgrade`
- [ ] SSH key authentication (disable password auth)
- [ ] Regular database backups configured
- [ ] Laravel security headers configured
- [ ] `.env` file not committed to git (in `.gitignore`)

---

## Part 8: Performance Optimisation

### 8.1 Enable OPcache

Already enabled in Dockerfile with optimised settings.

### 8.2 Configure MySQL

```bash
# Edit MySQL configuration
docker compose exec mysql bash
nano /etc/mysql/my.cnf

# Add optimisations (restart required):
[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 200
```

### 8.3 Redis Persistence

```bash
# Configure Redis persistence
docker compose exec redis redis-cli
> CONFIG SET save "900 1 300 10"
```

### 8.4 Enable Cloudflare Performance Features

In Cloudflare Dashboard:
1. **Speed** → **Optimisation**
   - Enable Auto Minify (HTML, CSS, JS)
   - Enable Brotli compression
2. **Caching** → **Configuration**
   - Set caching level to "Standard"
   - Enable "Always Online"

---

## Summary

Your staging environment is now deployed at:

**URL**: https://staging.workset.kneebone.com.au
**Auth**: jim / empirefitness
**Horizon**: https://staging.workset.kneebone.com.au/horizon

**Services Running:**
- Laravel 12.x (PHP 8.4)
- MySQL 8.0
- Redis 7
- Meilisearch
- Horizon (queues)
- Reverb (WebSockets)
- Scheduled tasks

**Next Steps:**
1. Test all application features
2. Set up automated backups
3. Configure monitoring/alerts
4. Document any custom configurations
5. Set up CI/CD pipeline for automated deployments (see DEPLOYMENT.md)

For production deployment, refer to the main [DEPLOYMENT.md](DEPLOYMENT.md) guide.
