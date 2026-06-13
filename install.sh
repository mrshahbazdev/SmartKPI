#!/bin/bash
#
# SmartKPI — One-Command Installer for cPanel / SiteGround
#
# Usage:
#   chmod +x install.sh
#   ./install.sh
#
# Automatically handles:
#   - Database creation (MySQL via cPanel UAPI)
#   - .env configuration (DB, URL, app key)
#   - Composer install
#   - npm install + build
#   - Migrations + seeding
#   - Storage symlink + permissions
#   - .htaccess for public directory
#
# Requirements:
#   - SSH access to cPanel server
#   - PHP 8.2+ (cli)
#   - Composer (usually pre-installed on SiteGround)
#   - Node.js 18+ (use SiteGround's SSH Node manager)
#

set -e

# ─── Colors ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# ─── Helpers ─────────────────────────────────────────────────────────────────
print_header() {
    echo ""
    echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║${NC}  ${BOLD}SmartKPI Installer${NC}  ${CYAN}v1.0${NC}                         ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}  ${CYAN}cPanel / SiteGround Edition${NC}                      ${BLUE}║${NC}"
    echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_step() {
    echo -e "\n${GREEN}▸ [$1/$TOTAL_STEPS]${NC} ${BOLD}$2${NC}"
}

print_ok() {
    echo -e "  ${GREEN}✓${NC} $1"
}

print_warn() {
    echo -e "  ${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "  ${RED}✗${NC} $1"
}

print_info() {
    echo -e "  ${CYAN}→${NC} $1"
}

TOTAL_STEPS=10
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

print_header

# ─── Step 1: Check Requirements ─────────────────────────────────────────────
print_step 1 "Checking requirements..."

# PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
    PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")
    if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
        print_ok "PHP $PHP_VERSION"
    else
        print_error "PHP 8.2+ required (found $PHP_VERSION)"
        echo -e "  ${CYAN}→${NC} SiteGround: Use SSH command: ${BOLD}php8.3 -v${NC}"
        echo -e "  ${CYAN}→${NC} Or set PHP version in cPanel → MultiPHP Manager"
        exit 1
    fi
else
    print_error "PHP not found"
    exit 1
fi

# Composer
if command -v composer &> /dev/null; then
    print_ok "Composer $(composer --version 2>/dev/null | grep -oP '[\d.]+'| head -1)"
elif [ -f "$HOME/bin/composer" ]; then
    alias composer="$HOME/bin/composer"
    print_ok "Composer (~/bin/composer)"
else
    print_warn "Composer not found — installing locally..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir="$SCRIPT_DIR" --filename=composer
    rm composer-setup.php
    COMPOSER_CMD="php $SCRIPT_DIR/composer"
    print_ok "Composer installed locally"
fi
COMPOSER_CMD="${COMPOSER_CMD:-composer}"

# Node.js
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    print_ok "Node.js $NODE_VERSION"
elif [ -f "$HOME/.nvm/nvm.sh" ]; then
    source "$HOME/.nvm/nvm.sh"
    print_ok "Node.js $(node -v) (via nvm)"
else
    print_warn "Node.js not found"
    echo -e "  ${CYAN}→${NC} SiteGround: Enable Node.js in Site Tools → Devs → Node.js Manager"
    echo -e "  ${CYAN}→${NC} Or install nvm: curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash"
    echo ""
    read -p "  Continue without Node.js? Assets won't be built. (y/n): " SKIP_NODE
    if [ "$SKIP_NODE" != "y" ]; then exit 1; fi
fi

# npm
if command -v npm &> /dev/null; then
    print_ok "npm $(npm -v)"
else
    HAS_NPM=false
fi

# MySQL
if command -v mysql &> /dev/null; then
    print_ok "MySQL client available"
else
    print_warn "MySQL CLI not found — will use cPanel UAPI for database creation"
fi

# ─── Step 2: Collect Configuration ──────────────────────────────────────────
print_step 2 "Configuration..."

# Auto-detect domain
DETECTED_DOMAIN=""
if [ -d "$HOME/public_html" ]; then
    # Try to get domain from cPanel
    if command -v uapi &> /dev/null; then
        DETECTED_DOMAIN=$(uapi DomainInfo list_domains 2>/dev/null | grep -m1 'main_domain' | awk '{print $2}' || true)
    fi
    if [ -z "$DETECTED_DOMAIN" ]; then
        DETECTED_DOMAIN=$(hostname -d 2>/dev/null || echo "")
    fi
fi

echo ""
echo -e "  ${BOLD}Enter your details (press Enter for defaults):${NC}"
echo ""

# App URL
read -p "  App URL [https://${DETECTED_DOMAIN:-yourdomain.com}]: " APP_URL
APP_URL="${APP_URL:-https://${DETECTED_DOMAIN:-yourdomain.com}}"

# Database config
read -p "  Database name [smartkpi]: " DB_NAME
DB_NAME="${DB_NAME:-smartkpi}"

# cPanel username detection
CPANEL_USER="${USER:-$(whoami)}"
DB_PREFIX="${CPANEL_USER}_"

read -p "  cPanel username [$CPANEL_USER]: " INPUT_CPANEL_USER
CPANEL_USER="${INPUT_CPANEL_USER:-$CPANEL_USER}"
DB_PREFIX="${CPANEL_USER}_"

# Full database name (cPanel prefixes with username_)
FULL_DB_NAME="${DB_PREFIX}${DB_NAME}"

read -p "  Database username [${DB_PREFIX}smartkpi]: " DB_USER
DB_USER="${DB_USER:-${DB_PREFIX}smartkpi}"

# Generate random password
RANDOM_PASS=$(php -r "echo substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$'), 0, 16);")
read -p "  Database password [auto-generated]: " DB_PASS
DB_PASS="${DB_PASS:-$RANDOM_PASS}"

# Install path
read -p "  Install subfolder (leave empty for root domain): " SUBFOLDER

# App locale
echo ""
echo -e "  ${CYAN}Default language:${NC}"
echo "    1) Deutsch (DE)"
echo "    2) English (EN)"
read -p "  Choose [1]: " LANG_CHOICE
if [ "$LANG_CHOICE" = "2" ]; then
    APP_LOCALE="en"
else
    APP_LOCALE="de"
fi

echo ""
print_ok "Configuration collected"
print_info "Database: $FULL_DB_NAME"
print_info "URL: $APP_URL"
print_info "Locale: $APP_LOCALE"

# ─── Step 3: Create Database (cPanel UAPI) ──────────────────────────────────
print_step 3 "Creating database..."

DB_CREATED=false

# Method 1: cPanel UAPI (SiteGround / most cPanel hosts)
if command -v uapi &> /dev/null; then
    print_info "Using cPanel UAPI..."

    # Create database
    uapi Mysql create_database name="$FULL_DB_NAME" 2>/dev/null && DB_CREATED=true || true

    if [ "$DB_CREATED" = true ]; then
        print_ok "Database '$FULL_DB_NAME' created"

        # Create user
        uapi Mysql create_user name="$DB_USER" password="$DB_PASS" 2>/dev/null || true
        print_ok "Database user '$DB_USER' created"

        # Grant privileges
        uapi Mysql set_privileges_on_database user="$DB_USER" database="$FULL_DB_NAME" privileges="ALL PRIVILEGES" 2>/dev/null || true
        print_ok "Privileges granted"
    else
        print_warn "Database may already exist — continuing..."
        DB_CREATED=true
    fi
fi

# Method 2: cpanel CLI (older cPanel versions)
if [ "$DB_CREATED" = false ] && command -v cpanel &> /dev/null; then
    print_info "Using cpanel CLI..."
    cpanel Mysql create_database domain="$FULL_DB_NAME" 2>/dev/null && DB_CREATED=true || true
fi

# Method 3: Direct MySQL
if [ "$DB_CREATED" = false ] && command -v mysql &> /dev/null; then
    print_info "Using MySQL CLI..."
    read -p "  MySQL root password (or press Enter if none): " -s MYSQL_ROOT_PASS
    echo ""

    if [ -z "$MYSQL_ROOT_PASS" ]; then
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`$FULL_DB_NAME\`; CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'; GRANT ALL PRIVILEGES ON \`$FULL_DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null && DB_CREATED=true || true
    else
        mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$FULL_DB_NAME\`; CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'; GRANT ALL PRIVILEGES ON \`$FULL_DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null && DB_CREATED=true || true
    fi

    if [ "$DB_CREATED" = true ]; then
        print_ok "Database created via MySQL CLI"
    fi
fi

if [ "$DB_CREATED" = false ]; then
    print_warn "Could not auto-create database"
    print_info "Please create it manually in cPanel → MySQL Databases:"
    print_info "  Database: $FULL_DB_NAME"
    print_info "  User: $DB_USER"
    print_info "  Password: $DB_PASS"
    echo ""
    read -p "  Press Enter after creating the database manually..." DUMMY
fi

# ─── Step 4: Configure .env ─────────────────────────────────────────────────
print_step 4 "Configuring environment (.env)..."

if [ ! -f ".env" ]; then
    cp .env.example .env
    print_ok "Created .env from .env.example"
else
    print_warn ".env already exists — backing up to .env.backup"
    cp .env .env.backup
    cp .env.example .env
fi

# Update .env values
sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|APP_LOCALE=.*|APP_LOCALE=$APP_LOCALE|" .env

# Switch to MySQL for shared hosting
sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|DB_PORT=.*|DB_PORT=3306|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$FULL_DB_NAME|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env

# Use file-based cache/session/queue for shared hosting (no Redis)
sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|" .env
sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env

print_ok ".env configured"
print_info "DB: MySQL → $FULL_DB_NAME"
print_info "Cache/Queue/Session: file-based (no Redis needed)"

# ─── Step 5: Install Composer Dependencies ──────────────────────────────────
print_step 5 "Installing Composer dependencies..."

$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5
print_ok "Composer dependencies installed"

# ─── Step 6: Generate App Key ───────────────────────────────────────────────
print_step 6 "Generating application key..."

php artisan key:generate --force --no-interaction
print_ok "App key generated"

# ─── Step 7: Install & Build Frontend ───────────────────────────────────────
print_step 7 "Building frontend assets..."

if command -v npm &> /dev/null; then
    npm install --no-audit --no-fund 2>&1 | tail -3
    print_ok "npm dependencies installed"

    npm run build 2>&1 | tail -3
    print_ok "Frontend assets built"
else
    print_warn "npm not available — skipping frontend build"
    print_info "Run 'npm install && npm run build' when Node.js is available"
fi

# ─── Step 8: Database Migration & Seeding ────────────────────────────────────
print_step 8 "Running migrations & seeding..."

php artisan migrate --force --no-interaction 2>&1 | tail -5
print_ok "Database migrated"

php artisan db:seed --force --no-interaction 2>&1 | tail -3
print_ok "Database seeded with demo data"

# ─── Step 9: Storage & Permissions ──────────────────────────────────────────
print_step 9 "Setting up storage & permissions..."

# Storage link
php artisan storage:link --force --no-interaction 2>/dev/null || true
print_ok "Storage symlink created"

# Set permissions (cPanel-safe)
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/app storage/framework storage/logs
print_ok "Permissions set"

# Clear & cache
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true
print_ok "Configuration cached"

# ─── Step 10: Setup Public Directory (cPanel) ───────────────────────────────
print_step 10 "Configuring web server..."

# Create/update .htaccess for public directory redirect
PUBLIC_HTML="$HOME/public_html"

if [ -n "$SUBFOLDER" ]; then
    TARGET_DIR="$PUBLIC_HTML/$SUBFOLDER"
    mkdir -p "$TARGET_DIR"
else
    TARGET_DIR="$PUBLIC_HTML"
fi

# Create .htaccess in document root to redirect to Laravel's public folder
cat > "$TARGET_DIR/.htaccess" << 'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect everything to the public folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L,QSA]
</IfModule>
HTACCESS

# If the project is NOT inside public_html, create a symlink or index.php bridge
if [ "$SCRIPT_DIR" != "$TARGET_DIR" ]; then
    # Create a bridge index.php that loads from the actual install location
    cat > "$TARGET_DIR/public/index.php" << PHP_BRIDGE 2>/dev/null || true
<?php
// SmartKPI — Bridge to actual install location
require __DIR__ . '/../../$(basename "$SCRIPT_DIR")/public/index.php';
PHP_BRIDGE

    # Alternative: Symlink the public directory
    if [ ! -L "$TARGET_DIR/public" ]; then
        ln -sf "$SCRIPT_DIR/public" "$TARGET_DIR/public" 2>/dev/null || true
    fi
fi

# Ensure the Laravel public/.htaccess exists
if [ ! -f "$SCRIPT_DIR/public/.htaccess" ]; then
    cat > "$SCRIPT_DIR/public/.htaccess" << 'HTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
fi

print_ok "Web server configured"

# ─── Done! ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║${NC}  ${BOLD}✓ SmartKPI installed successfully!${NC}              ${GREEN}║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BOLD}App URL:${NC}      $APP_URL"
echo -e "  ${BOLD}Database:${NC}     $FULL_DB_NAME (MySQL)"
echo -e "  ${BOLD}DB User:${NC}      $DB_USER"
echo -e "  ${BOLD}DB Password:${NC}  $DB_PASS"
echo -e "  ${BOLD}Language:${NC}     $APP_LOCALE"
echo ""
echo -e "  ${BOLD}Demo Logins:${NC}"
echo -e "  ┌─────────────────┬──────────────────────┬──────────┐"
echo -e "  │ Role            │ Email                │ Password │"
echo -e "  ├─────────────────┼──────────────────────┼──────────┤"
echo -e "  │ Super Admin     │ admin@smartkpi.com   │ password │"
echo -e "  │ Holding Admin   │ mueller@dentex.de    │ password │"
echo -e "  │ Company Admin   │ schmidt@dentex.de    │ password │"
echo -e "  │ Dept Manager    │ weber@dentex.de      │ password │"
echo -e "  └─────────────────┴──────────────────────┴──────────┘"
echo ""
echo -e "  ${YELLOW}⚠ IMPORTANT: Change demo passwords in production!${NC}"
echo -e "  ${YELLOW}⚠ Save your database password — it won't be shown again.${NC}"
echo ""
echo -e "  ${CYAN}Need help? See README.md for documentation.${NC}"
echo ""
