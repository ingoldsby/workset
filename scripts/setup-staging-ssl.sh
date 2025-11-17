#!/bin/bash

# Setup SSL certificates for staging.workset.kneebone.com.au
# This script should be run on the server as root or with sudo

set -e

DOMAIN="staging.workset.kneebone.com.au"
CERT_DIR="/var/www/workset/docker/nginx/certs"
EMAIL="${SSL_EMAIL:-admin@kneebone.com.au}"

echo "=========================================="
echo "SSL Certificate Setup for $DOMAIN"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run this script as root or with sudo"
    exit 1
fi

# Check if certbot is installed
if ! command -v certbot &> /dev/null; then
    echo "Certbot is not installed. Installing..."
    apt-get update
    apt-get install -y certbot python3-certbot-nginx
else
    echo "✓ Certbot is already installed"
fi

# Create certificate directory
echo ""
echo "Creating certificate directory..."
mkdir -p "$CERT_DIR"
chown www-data:www-data "$CERT_DIR"
chmod 755 "$CERT_DIR"
echo "✓ Certificate directory created: $CERT_DIR"

# Check if certificates already exist
if [ -f "$CERT_DIR/$DOMAIN.crt" ] && [ -f "$CERT_DIR/$DOMAIN.key" ]; then
    echo ""
    echo "⚠️  Certificates already exist at $CERT_DIR"
    echo "Checking expiry..."
    openssl x509 -in "$CERT_DIR/$DOMAIN.crt" -noout -enddate
    echo ""
    read -p "Do you want to renew/replace them? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Exiting without changes."
        exit 0
    fi
fi

# Stop nginx to allow certbot to bind to port 80
echo ""
echo "Stopping nginx temporarily..."
systemctl stop nginx
echo "✓ Nginx stopped"

# Obtain certificate
echo ""
echo "Obtaining SSL certificate from Let's Encrypt..."
echo "Domain: $DOMAIN"
echo "Email: $EMAIL"
echo ""

certbot certonly --standalone \
    -d "$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    --non-interactive

echo "✓ Certificate obtained"

# Copy certificates to expected location
echo ""
echo "Copying certificates to nginx directory..."
cp "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" "$CERT_DIR/$DOMAIN.crt"
cp "/etc/letsencrypt/live/$DOMAIN/privkey.pem" "$CERT_DIR/$DOMAIN.key"
echo "✓ Certificates copied"

# Set correct permissions
echo ""
echo "Setting permissions..."
chown www-data:www-data "$CERT_DIR"/*
chmod 644 "$CERT_DIR/$DOMAIN.crt"
chmod 600 "$CERT_DIR/$DOMAIN.key"
echo "✓ Permissions set"

# Test nginx configuration
echo ""
echo "Testing nginx configuration..."
if nginx -t; then
    echo "✓ Nginx configuration is valid"
else
    echo "✗ Nginx configuration test failed!"
    echo "Please check your nginx configuration before starting nginx"
    exit 1
fi

# Start nginx
echo ""
echo "Starting nginx..."
systemctl start nginx
echo "✓ Nginx started"

# Test SSL connection
echo ""
echo "Testing SSL connection..."
sleep 2
if curl -sSf -k -I "https://$DOMAIN/health" > /dev/null 2>&1; then
    echo "✓ SSL connection successful!"
else
    echo "⚠️  Could not connect to https://$DOMAIN/health"
    echo "Please check nginx logs and DNS configuration"
fi

# Setup auto-renewal
echo ""
echo "Setting up automatic certificate renewal..."

# Create renewal hook script
cat > /etc/letsencrypt/renewal-hooks/deploy/copy-workset-certs.sh <<EOF
#!/bin/bash
# Copy renewed certificates to workset nginx directory
cp "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" "$CERT_DIR/$DOMAIN.crt"
cp "/etc/letsencrypt/live/$DOMAIN/privkey.pem" "$CERT_DIR/$DOMAIN.key"
chown www-data:www-data "$CERT_DIR"/*
chmod 644 "$CERT_DIR/$DOMAIN.crt"
chmod 600 "$CERT_DIR/$DOMAIN.key"
systemctl reload nginx
EOF

chmod +x /etc/letsencrypt/renewal-hooks/deploy/copy-workset-certs.sh
echo "✓ Renewal hook created"

# Test renewal (dry run)
echo ""
echo "Testing certificate renewal (dry run)..."
if certbot renew --dry-run; then
    echo "✓ Auto-renewal is configured correctly"
else
    echo "⚠️  Auto-renewal test failed. Please check certbot configuration"
fi

echo ""
echo "=========================================="
echo "SSL Setup Complete!"
echo "=========================================="
echo ""
echo "Certificate details:"
openssl x509 -in "$CERT_DIR/$DOMAIN.crt" -noout -subject -issuer -dates
echo ""
echo "Test your site:"
echo "  curl -I https://$DOMAIN/health"
echo ""
echo "Certificates will auto-renew before expiry."
echo "Monitor renewal with: journalctl -u certbot.timer"
echo ""
