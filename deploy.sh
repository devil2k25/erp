#!/bin/bash
# ─── Factory ERP — Oracle Cloud / Ubuntu 22.04 ARM64 Deployment Script ────────
# Run as: curl -fsSL <raw-url>/deploy.sh | bash
# Or:     bash deploy.sh
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()  { echo -e "${GREEN}[INFO]${NC}  $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error() { echo -e "${RED}[ERR]${NC}   $1"; exit 1; }

REPO_URL="https://github.com/devil2k25/erp.git"
BRANCH="claude/factory-management-saas-jnm96p"
INSTALL_DIR="$HOME/erp"

# ── 1. System packages ────────────────────────────────────────────────────────
info "Updating system packages..."
sudo apt-get update -qq
sudo apt-get install -y -qq git curl openssl ufw

# ── 2. Docker ─────────────────────────────────────────────────────────────────
if ! command -v docker &>/dev/null; then
    info "Installing Docker..."
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker "$USER"
    info "Docker installed. You may need to log out and back in for group changes."
fi

if ! docker compose version &>/dev/null 2>&1; then
    info "Installing Docker Compose plugin..."
    sudo apt-get install -y -qq docker-compose-plugin
fi

# ── 3. Firewall ───────────────────────────────────────────────────────────────
info "Configuring firewall..."
sudo ufw allow 22/tcp  2>/dev/null || true
sudo ufw allow 80/tcp  2>/dev/null || true
sudo ufw allow 443/tcp 2>/dev/null || true
sudo ufw --force enable 2>/dev/null || true

# ── 4. Clone repository ───────────────────────────────────────────────────────
if [ -d "$INSTALL_DIR/.git" ]; then
    info "Pulling latest code in $INSTALL_DIR..."
    cd "$INSTALL_DIR"
    git fetch origin
    git checkout "$BRANCH"
    git pull origin "$BRANCH"
else
    info "Cloning repository to $INSTALL_DIR..."
    git clone --branch "$BRANCH" "$REPO_URL" "$INSTALL_DIR"
    cd "$INSTALL_DIR"
fi

cd "$INSTALL_DIR"

# ── 5. Configure .env ─────────────────────────────────────────────────────────
if [ ! -f .env ]; then
    info "Setting up environment configuration..."
    cp .env.production.example .env

    # Auto-generate APP_KEY
    APP_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" .env

    # Get server IP
    SERVER_IP=$(curl -4 -s ifconfig.me 2>/dev/null || curl -4 -s icanhazip.com 2>/dev/null || echo "localhost")
    sed -i "s|YOUR_SERVER_IP|${SERVER_IP}|g" .env

    echo ""
    warn "Please set strong passwords in .env before continuing."
    echo "   File: $INSTALL_DIR/.env"
    echo ""
    echo "   Required:"
    echo "     MYSQL_ROOT_PASSWORD=<strong-password>"
    echo "     MYSQL_MASTER_PASSWORD=<strong-password>"
    echo "     REDIS_PASSWORD=<strong-password>"
    echo ""
    read -p "Press Enter after editing .env to continue... " _

    # Verify passwords were changed
    if grep -q "CHANGE_ME" .env; then
        error "Please replace all CHANGE_ME values in .env first."
    fi
else
    info ".env already exists, skipping configuration."
fi

# ── 6. Build and start services ───────────────────────────────────────────────
info "Building Docker images (this takes 3-5 minutes on first run)..."
sudo docker compose -f docker-compose.prod.yml build --parallel

info "Starting services..."
sudo docker compose -f docker-compose.prod.yml up -d

# ── 7. Wait for MySQL ─────────────────────────────────────────────────────────
info "Waiting for MySQL to be ready..."
for i in $(seq 1 30); do
    if sudo docker compose -f docker-compose.prod.yml exec -T mysql-master mysqladmin ping -h localhost -u root "-p$(grep MYSQL_ROOT_PASSWORD .env | cut -d= -f2)" --silent 2>/dev/null; then
        info "MySQL is ready."
        break
    fi
    [ "$i" -eq 30 ] && error "MySQL did not become ready in time."
    sleep 3
done

# ── 8. Run migrations and seeds ───────────────────────────────────────────────
info "Running master database migrations..."
sudo docker compose -f docker-compose.prod.yml exec -T backend \
    php artisan migrate --path=database/migrations/master --force

info "Running default migrations (Sanctum, jobs, cache)..."
sudo docker compose -f docker-compose.prod.yml exec -T backend \
    php artisan migrate --force

info "Seeding master data (plans + super admin)..."
sudo docker compose -f docker-compose.prod.yml exec -T backend \
    php artisan db:seed --class=DatabaseSeeder --force

# ── 9. Create storage symlink ─────────────────────────────────────────────────
info "Creating storage symlink..."
sudo docker compose -f docker-compose.prod.yml exec -T backend \
    php artisan storage:link --force 2>/dev/null || true

# ── 10. Done ──────────────────────────────────────────────────────────────────
SERVER_IP=$(grep APP_URL .env | cut -d= -f2 | sed 's|http[s]*://||')
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║        Factory ERP is now live! 🎉           ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║  App:     http://${SERVER_IP}                ${NC}"
echo -e "${GREEN}║  Admin:   http://${SERVER_IP}/admin/login     ${NC}"
echo -e "${GREEN}║  Admin credentials:                          ║${NC}"
echo -e "${GREEN}║    Email:    admin@erp.com                   ║${NC}"
echo -e "${GREEN}║    Password: admin@123                       ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""
info "To add HTTPS (SSL), point a domain at this IP then run: bash setup-ssl.sh"
info "To view logs: sudo docker compose -f docker-compose.prod.yml logs -f"
