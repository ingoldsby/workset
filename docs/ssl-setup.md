# SSL Certificate Setup for Staging Environment

## Problem

The staging site (`staging.workset.kneebone.com.au`) is experiencing SSL handshake failures because SSL certificates are not properly configured.

## Required Certificates

The nginx configuration (`docker/nginx/system-nginx-staging.conf`) expects:
- Certificate: `/var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt`
- Private Key: `/var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.key`

## Solution: Use Let's Encrypt (Recommended)

Let's Encrypt provides free, automated SSL certificates. Follow these steps on the server:

### Step 1: Install Certbot

```bash
# Install certbot and nginx plugin
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx
```

### Step 2: Create Certificate Directory

```bash
# Create the certs directory
sudo mkdir -p /var/www/workset/docker/nginx/certs
sudo chown www-data:www-data /var/www/workset/docker/nginx/certs
sudo chmod 755 /var/www/workset/docker/nginx/certs
```

### Step 3: Obtain Certificate

```bash
# Stop nginx temporarily to allow certbot to bind to port 80
sudo systemctl stop nginx

# Obtain certificate using standalone mode
sudo certbot certonly --standalone \
  -d staging.workset.kneebone.com.au \
  --email your-email@example.com \
  --agree-tos \
  --no-eff-email

# Start nginx again
sudo systemctl start nginx
```

### Step 4: Copy Certificates to Expected Location

```bash
# Copy the Let's Encrypt certificates to the expected location
sudo cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/fullchain.pem \
  /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt

sudo cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/privkey.pem \
  /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.key

# Set correct permissions
sudo chown www-data:www-data /var/www/workset/docker/nginx/certs/*
sudo chmod 644 /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt
sudo chmod 600 /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.key
```

### Step 5: Test and Reload Nginx

```bash
# Test nginx configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

### Step 6: Verify SSL is Working

```bash
# Test with curl
curl -I https://staging.workset.kneebone.com.au/health

# Or with openssl
openssl s_client -connect staging.workset.kneebone.com.au:443 -servername staging.workset.kneebone.com.au
```

## Alternative: Use Existing Certificates

If you already have SSL certificates for `staging.workset.kneebone.com.au`, simply copy them to:
- `/var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt` (full certificate chain)
- `/var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.key` (private key)

And set the correct permissions as shown in Step 4 above.

## Auto-Renewal

Let's Encrypt certificates expire after 90 days. Set up auto-renewal:

```bash
# Test renewal
sudo certbot renew --dry-run

# Add cron job to renew and copy certificates
sudo crontab -e
```

Add this line to renew twice daily and copy to the expected location:

```cron
0 0,12 * * * certbot renew --quiet --deploy-hook "cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/fullchain.pem /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt && cp /etc/letsencrypt/live/staging.workset.kneebone.com.au/privkey.pem /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.key && systemctl reload nginx"
```

## Troubleshooting

### Check if certificates exist on server

```bash
ls -la /var/www/workset/docker/nginx/certs/
ls -la /etc/letsencrypt/live/staging.workset.kneebone.com.au/
```

### Check certificate validity

```bash
openssl x509 -in /var/www/workset/docker/nginx/certs/staging.workset.kneebone.com.au.crt -text -noout
```

### Check nginx error logs

```bash
sudo tail -f /var/log/nginx/error.log
```

### Verify DNS is pointing correctly

```bash
dig staging.workset.kneebone.com.au
nslookup staging.workset.kneebone.com.au
```
