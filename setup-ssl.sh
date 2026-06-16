#!/bin/bash
# ─── Let's Encrypt SSL Setup ──────────────────────────────────────────────────
# Run AFTER deploy.sh, once you have a domain pointed at this server.
# Usage: bash setup-ssl.sh yourdomain.com admin@yourdomain.com
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()  { echo -e "${GREEN}[INFO]${NC}  $1"; }
error() { echo -e "${RED}[ERR]${NC}   $1"; exit 1; }

DOMAIN="${1:-}"
EMAIL="${2:-}"
INSTALL_DIR="$HOME/erp"

[ -z "$DOMAIN" ] && read -p "Enter your domain (e.g. erp.yourdomain.com): " DOMAIN
[ -z "$EMAIL"  ] && read -p "Enter your email (for Let's Encrypt notices): " EMAIL

[ -z "$DOMAIN" ] && error "Domain is required."
[ -z "$EMAIL"  ] && error "Email is required."

cd "$INSTALL_DIR"

# ── 1. Install Certbot ────────────────────────────────────────────────────────
info "Installing Certbot..."
sudo apt-get install -y -qq certbot

# ── 2. Stop nginx (free port 80 for certbot standalone) ─────────────────────
info "Temporarily stopping nginx..."
sudo docker compose -f docker-compose.prod.yml stop nginx

# ── 3. Obtain certificate ────────────────────────────────────────────────────
info "Obtaining SSL certificate for ${DOMAIN}..."
sudo certbot certonly --standalone \
    --non-interactive \
    --agree-tos \
    --email "$EMAIL" \
    -d "$DOMAIN"

# ── 4. Update nginx prod.conf for HTTPS ──────────────────────────────────────
info "Updating Nginx config for HTTPS..."
cat > docker/nginx/prod.conf << NGINXCONF
gzip on;
gzip_types text/plain text/css application/javascript application/json application/xml image/svg+xml;
gzip_min_length 256;

server {
    listen 80;
    server_name ${DOMAIN};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ${DOMAIN};

    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;

    client_max_body_size 100M;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=63072000" always;

    location / {
        proxy_pass http://frontend:80;
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }

    location /api/ {
        try_files \$uri \$uri/ @laravel;
    }

    location @laravel {
        fastcgi_pass backend:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/backend/public/index.php;
        fastcgi_param REQUEST_URI \$request_uri;
        fastcgi_param HTTPS on;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location /socket.io/ {
        proxy_pass http://realtime:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Forwarded-Proto https;
        proxy_cache_bypass \$http_upgrade;
        proxy_read_timeout 86400;
    }
}
NGINXCONF

# ── 5. Update APP_URL and VITE vars ──────────────────────────────────────────
info "Updating .env with HTTPS URLs..."
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
sed -i "s|VITE_API_URL=.*|VITE_API_URL=https://${DOMAIN}|" .env
sed -i "s|VITE_SOCKET_URL=.*|VITE_SOCKET_URL=https://${DOMAIN}|" .env

# ── 6. Mount certs into nginx and restart ────────────────────────────────────
info "Restarting services with SSL..."
# Update docker-compose.prod.yml to mount real letsencrypt certs
sudo docker compose -f docker-compose.prod.yml up -d --no-deps nginx

# ── 7. Rebuild frontend with new HTTPS URL ───────────────────────────────────
info "Rebuilding frontend bundle with HTTPS URL..."
sudo docker compose -f docker-compose.prod.yml build frontend
sudo docker compose -f docker-compose.prod.yml up -d --no-deps frontend

# ── 8. Setup auto-renewal cron ───────────────────────────────────────────────
info "Setting up certificate auto-renewal..."
(crontab -l 2>/dev/null; echo "0 3 * * * certbot renew --quiet && docker compose -f $INSTALL_DIR/docker-compose.prod.yml restart nginx") | sort -u | crontab -

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  SSL configured! Your app is now at:         ║${NC}"
echo -e "${GREEN}║  https://${DOMAIN}                           ${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
