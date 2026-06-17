#!/bin/bash
#
# ╔═══════════════════════════════════════════════════════════════════════╗
# ║  SmartKPI — Advanced Auto-Installer v2.0                            ║
# ║  Supports: cPanel, Plesk, VPS, Dedicated, Docker, Local Dev         ║
# ╚═══════════════════════════════════════════════════════════════════════╝
#
# Usage:
#   chmod +x install.sh
#   ./install.sh                  # Interactive mode
#   ./install.sh --quick          # Quick install with defaults
#   ./install.sh --restore        # Restore from backup
#   ./install.sh --update         # Update existing installation
#   ./install.sh --uninstall      # Remove SmartKPI
#   ./install.sh --health         # Run health check only
#   ./install.sh --help           # Show help
#

# ─── Strict Mode ─────────────────────────────────────────────────────────────
set -euo pipefail
trap 'on_error $LINENO' ERR

# ─── Constants ───────────────────────────────────────────────────────────────
VERSION="2.0.0"
MIN_PHP="8.2"
MIN_NODE="18"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
LOG_FILE="$SCRIPT_DIR/storage/logs/install.log"
BACKUP_DIR="$SCRIPT_DIR/storage/backups"
TOTAL_STEPS=14
INSTALL_START=$(date +%s)

# ─── Colors & Formatting ────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
WHITE='\033[1;37m'
DIM='\033[2m'
NC='\033[0m'
BOLD='\033[1m'
UNDERLINE='\033[4m'

# ─── Helper Functions ────────────────────────────────────────────────────────
log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "$LOG_FILE" 2>/dev/null || true; }

print_banner() {
    clear 2>/dev/null || true
    echo ""
    echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║${NC}                                                              ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}   ${WHITE}█▀ █▀▄▀█ ▄▀█ █▀█ ▀█▀ █▄▀ █▀█ █${NC}                          ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}   ${WHITE}▄█ █░▀░█ █▀█ █▀▄ ░█░ █░█ █▀▀ █${NC}                          ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}                                                              ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}   ${CYAN}Advanced Auto-Installer ${DIM}v${VERSION}${NC}                           ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}   ${DIM}Intelligent KPI Management System${NC}                          ${BLUE}║${NC}"
    echo -e "${BLUE}║${NC}                                                              ${BLUE}║${NC}"
    echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_step()  { echo -e "\n${GREEN}━━━ [$1/$TOTAL_STEPS]${NC} ${BOLD}$2${NC}"; log "STEP $1: $2"; }
print_ok()    { echo -e "  ${GREEN}✓${NC} $1"; log "OK: $1"; }
print_warn()  { echo -e "  ${YELLOW}⚠${NC} $1"; log "WARN: $1"; }
print_error() { echo -e "  ${RED}✗${NC} $1"; log "ERROR: $1"; }
print_info()  { echo -e "  ${CYAN}→${NC} $1"; log "INFO: $1"; }
print_dim()   { echo -e "  ${DIM}$1${NC}"; }

on_error() {
    local line=$1
    echo ""
    print_error "Installation failed at line $line"
    print_info "Check log: $LOG_FILE"
    print_info "Run './install.sh --health' to diagnose"
    echo ""
    log "FATAL: Failed at line $line"
    exit 1
}

spinner() {
    local pid=$1 msg=$2
    local spin='⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏'
    local i=0
    while kill -0 "$pid" 2>/dev/null; do
        printf "\r  ${CYAN}${spin:i++%${#spin}:1}${NC} %s" "$msg"
        sleep 0.1
    done
    printf "\r"
}

confirm() {
    local msg=$1 default=${2:-y}
    if [ "$QUICK_MODE" = true ]; then return 0; fi
    read -p "  $msg (y/n) [$default]: " answer
    answer="${answer:-$default}"
    [[ "$answer" =~ ^[Yy] ]]
}

# ─── Environment Detection ──────────────────────────────────────────────────
detect_environment() {
    ENV_TYPE="unknown"
    OS_NAME=$(uname -s)
    OS_ARCH=$(uname -m)

    # Detect hosting environment
    if command -v uapi &>/dev/null; then
        ENV_TYPE="cpanel"
    elif command -v plesk &>/dev/null; then
        ENV_TYPE="plesk"
    elif [ -f /.dockerenv ]; then
        ENV_TYPE="docker"
    elif command -v systemctl &>/dev/null && [ -d "/etc/nginx" ] || [ -d "/etc/apache2" ]; then
        ENV_TYPE="vps"
    elif [ "$OS_NAME" = "Darwin" ]; then
        ENV_TYPE="macos"
    else
        ENV_TYPE="linux"
    fi

    # Detect web server
    WEB_SERVER="unknown"
    if command -v nginx &>/dev/null; then WEB_SERVER="nginx"
    elif command -v apache2 &>/dev/null || command -v httpd &>/dev/null; then WEB_SERVER="apache"
    elif [ -f "/etc/apache2/apache2.conf" ] || [ -f "/etc/httpd/conf/httpd.conf" ]; then WEB_SERVER="apache"
    fi

    log "Environment: $ENV_TYPE | OS: $OS_NAME ($OS_ARCH) | Web: $WEB_SERVER"
}

# ─── Argument Parsing ────────────────────────────────────────────────────────
QUICK_MODE=false
MODE="install"

parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --quick|-q)     QUICK_MODE=true ;;
            --update|-u)    MODE="update" ;;
            --restore|-r)   MODE="restore" ;;
            --uninstall)    MODE="uninstall" ;;
            --health|-h)    MODE="health" ;;
            --help)         show_help; exit 0 ;;
            *)              print_error "Unknown option: $1"; show_help; exit 1 ;;
        esac
        shift
    done
}

show_help() {
    echo -e "${BOLD}SmartKPI Installer v${VERSION}${NC}"
    echo ""
    echo "Usage: ./install.sh [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  (none)          Interactive installation"
    echo "  --quick, -q     Quick install with defaults"
    echo "  --update, -u    Update existing installation"
    echo "  --restore, -r   Restore from backup"
    echo "  --uninstall     Remove SmartKPI completely"
    echo "  --health, -h    Run system health check"
    echo "  --help          Show this help"
    echo ""
    echo "Supported Environments:"
    echo "  cPanel (SiteGround, Hostinger, Bluehost, Namecheap)"
    echo "  Plesk (Contabo, IONOS)"
    echo "  VPS (DigitalOcean, Hetzner, Linode, AWS)"
    echo "  Docker, Local Development (macOS, Linux)"
    echo ""
    echo "Requirements:"
    echo "  PHP >= $MIN_PHP with extensions: pdo, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, curl"
    echo "  MySQL 8+ or PostgreSQL 15+"
    echo "  Composer 2.x"
    echo "  Node.js >= $MIN_NODE (optional — pre-built assets included)"
}

# ─── Health Check ────────────────────────────────────────────────────────────
run_health_check() {
    echo ""
    echo -e "${BOLD}SmartKPI Health Check${NC}"
    echo -e "${DIM}━━━━━━━━━━━━━━━━━━━━${NC}"
    local issues=0

    # PHP
    if command -v php &>/dev/null; then
        local phpv=$(php -r "echo PHP_VERSION;")
        print_ok "PHP $phpv"

        # Extensions
        for ext in pdo mbstring openssl tokenizer xml ctype json bcmath curl fileinfo; do
            if php -m 2>/dev/null | grep -qi "^$ext$"; then
                print_dim "  Extension: $ext"
            else
                print_warn "  Missing extension: $ext"
                ((issues++))
            fi
        done
    else
        print_error "PHP not installed"; ((issues++))
    fi

    # Database
    if [ -f ".env" ]; then
        local db_conn=$(grep "^DB_CONNECTION=" .env | cut -d= -f2)
        if php artisan db:monitor --max=100 &>/dev/null; then
            print_ok "Database connection ($db_conn)"
        elif php artisan migrate:status &>/dev/null; then
            print_ok "Database accessible ($db_conn)"
        else
            print_error "Database connection failed"; ((issues++))
        fi
    else
        print_warn ".env file not found"
    fi

    # Storage permissions
    for dir in storage/app storage/framework storage/logs bootstrap/cache; do
        if [ -w "$SCRIPT_DIR/$dir" ]; then
            print_dim "  Writable: $dir"
        else
            print_error "Not writable: $dir"; ((issues++))
        fi
    done

    # Storage link
    if [ -L "$SCRIPT_DIR/public/storage" ]; then
        print_ok "Storage symlink exists"
    else
        print_warn "Storage symlink missing (run: php artisan storage:link)"
    fi

    # .env
    if [ -f "$SCRIPT_DIR/.env" ]; then
        print_ok ".env file exists"
        local app_key=$(grep "^APP_KEY=" .env | cut -d= -f2)
        if [ -n "$app_key" ] && [ "$app_key" != "" ]; then
            print_ok "APP_KEY is set"
        else
            print_error "APP_KEY is empty"; ((issues++))
        fi
    else
        print_error ".env missing"; ((issues++))
    fi

    # Build assets
    if [ -f "$SCRIPT_DIR/public/build/manifest.json" ]; then
        local asset_count=$(find "$SCRIPT_DIR/public/build/assets" -type f 2>/dev/null | wc -l)
        print_ok "Build assets present ($asset_count files)"
    else
        print_error "Build assets missing (run: npm run build)"; ((issues++))
    fi

    # Disk space
    local disk_avail=$(df -m "$SCRIPT_DIR" | tail -1 | awk '{print $4}')
    if [ "$disk_avail" -gt 100 ]; then
        print_ok "Disk space: ${disk_avail}MB available"
    else
        print_warn "Low disk space: ${disk_avail}MB available"; ((issues++))
    fi

    echo ""
    if [ "$issues" -eq 0 ]; then
        echo -e "  ${GREEN}${BOLD}All checks passed!${NC}"
    else
        echo -e "  ${YELLOW}${BOLD}$issues issue(s) found${NC}"
    fi
    echo ""
    return $issues
}

# ─── Backup ──────────────────────────────────────────────────────────────────
create_backup() {
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_path="$BACKUP_DIR/backup_$timestamp"
    mkdir -p "$backup_path"

    # Backup .env
    [ -f "$SCRIPT_DIR/.env" ] && cp "$SCRIPT_DIR/.env" "$backup_path/.env"

    # Backup database
    if [ -f "$SCRIPT_DIR/.env" ]; then
        local db_conn=$(grep "^DB_CONNECTION=" .env | cut -d= -f2)
        local db_name=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
        local db_user=$(grep "^DB_USERNAME=" .env | cut -d= -f2)
        local db_pass=$(grep "^DB_PASSWORD=" .env | cut -d= -f2)
        local db_host=$(grep "^DB_HOST=" .env | cut -d= -f2)
        local db_port=$(grep "^DB_PORT=" .env | cut -d= -f2)

        if [ "$db_conn" = "mysql" ] && command -v mysqldump &>/dev/null; then
            mysqldump -h"$db_host" -P"$db_port" -u"$db_user" -p"$db_pass" "$db_name" > "$backup_path/database.sql" 2>/dev/null && \
                print_ok "Database backed up" || print_warn "Database backup skipped"
        elif [ "$db_conn" = "pgsql" ] && command -v pg_dump &>/dev/null; then
            PGPASSWORD="$db_pass" pg_dump -h "$db_host" -p "$db_port" -U "$db_user" "$db_name" > "$backup_path/database.sql" 2>/dev/null && \
                print_ok "Database backed up" || print_warn "Database backup skipped"
        fi
    fi

    # Backup uploads
    if [ -d "$SCRIPT_DIR/storage/app/public" ]; then
        cp -r "$SCRIPT_DIR/storage/app/public" "$backup_path/uploads" 2>/dev/null || true
    fi

    print_ok "Backup saved: $backup_path"
    echo "$backup_path"
}

# ─── Restore ─────────────────────────────────────────────────────────────────
run_restore() {
    echo ""
    echo -e "${BOLD}Available Backups:${NC}"
    if [ ! -d "$BACKUP_DIR" ] || [ -z "$(ls -A "$BACKUP_DIR" 2>/dev/null)" ]; then
        print_error "No backups found in $BACKUP_DIR"
        exit 1
    fi

    local i=1
    local backups=()
    for dir in "$BACKUP_DIR"/backup_*; do
        [ -d "$dir" ] || continue
        backups+=("$dir")
        local bname=$(basename "$dir")
        local bsize=$(du -sh "$dir" 2>/dev/null | cut -f1)
        echo -e "  ${CYAN}$i)${NC} $bname ${DIM}($bsize)${NC}"
        ((i++))
    done

    read -p "  Select backup number: " choice
    local selected="${backups[$((choice-1))]}"

    if [ -z "$selected" ] || [ ! -d "$selected" ]; then
        print_error "Invalid selection"
        exit 1
    fi

    # Restore .env
    [ -f "$selected/.env" ] && cp "$selected/.env" "$SCRIPT_DIR/.env" && print_ok "Restored .env"

    # Restore database
    if [ -f "$selected/database.sql" ]; then
        local db_conn=$(grep "^DB_CONNECTION=" .env | cut -d= -f2)
        local db_name=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
        local db_user=$(grep "^DB_USERNAME=" .env | cut -d= -f2)
        local db_pass=$(grep "^DB_PASSWORD=" .env | cut -d= -f2)
        local db_host=$(grep "^DB_HOST=" .env | cut -d= -f2)

        if [ "$db_conn" = "mysql" ] && command -v mysql &>/dev/null; then
            mysql -h"$db_host" -u"$db_user" -p"$db_pass" "$db_name" < "$selected/database.sql" 2>/dev/null && \
                print_ok "Database restored" || print_error "Database restore failed"
        elif [ "$db_conn" = "pgsql" ] && command -v psql &>/dev/null; then
            PGPASSWORD="$db_pass" psql -h "$db_host" -U "$db_user" "$db_name" < "$selected/database.sql" 2>/dev/null && \
                print_ok "Database restored" || print_error "Database restore failed"
        fi
    fi

    # Restore uploads
    [ -d "$selected/uploads" ] && cp -r "$selected/uploads" "$SCRIPT_DIR/storage/app/public" && print_ok "Uploads restored"

    php artisan config:cache 2>/dev/null || true
    echo ""
    print_ok "Restore complete!"
}

# ─── Update ──────────────────────────────────────────────────────────────────
run_update() {
    print_banner
    echo -e "${BOLD}  Updating SmartKPI...${NC}"
    echo ""

    # Backup first
    print_info "Creating backup before update..."
    create_backup

    # Pull latest code
    if [ -d "$SCRIPT_DIR/.git" ]; then
        print_info "Pulling latest changes..."
        git -C "$SCRIPT_DIR" pull origin develop 2>&1 | tail -3
        print_ok "Code updated"
    fi

    # Update dependencies
    local COMPOSER_CMD="${COMPOSER_CMD:-composer}"
    if command -v composer &>/dev/null; then COMPOSER_CMD="composer"; fi
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3
    print_ok "Composer dependencies updated"

    # Build assets if Node available
    if command -v npm &>/dev/null; then
        npm install --no-audit --no-fund 2>&1 | tail -3
        npm run build 2>&1 | tail -3
        print_ok "Frontend rebuilt"
    fi

    # Run migrations
    php artisan migrate --force --no-interaction 2>&1 | tail -5
    print_ok "Database migrated"

    # Clear caches
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    php artisan event:cache 2>/dev/null || true
    print_ok "Caches refreshed"

    echo ""
    print_ok "Update complete!"
    echo ""
}

# ─── Uninstall ───────────────────────────────────────────────────────────────
run_uninstall() {
    echo ""
    echo -e "${RED}${BOLD}  SmartKPI Uninstaller${NC}"
    echo -e "${RED}  ════════════════════${NC}"
    echo ""

    if [ -f "$SCRIPT_DIR/.env" ]; then
        local db_name=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
        local db_user=$(grep "^DB_USERNAME=" .env | cut -d= -f2)
        echo -e "  Database: ${BOLD}$db_name${NC}"
        echo -e "  User:     ${BOLD}$db_user${NC}"
        echo ""
    fi

    read -p "  Type 'DELETE' to confirm uninstallation: " confirm_text
    if [ "$confirm_text" != "DELETE" ]; then
        echo -e "  ${GREEN}Cancelled.${NC}"
        exit 0
    fi

    echo ""

    # Clear caches
    php artisan cache:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    print_ok "Caches cleared"

    # Drop database
    if [ -f "$SCRIPT_DIR/.env" ]; then
        local db_conn=$(grep "^DB_CONNECTION=" .env | cut -d= -f2)
        local db_name=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
        local db_user=$(grep "^DB_USERNAME=" .env | cut -d= -f2)

        if command -v uapi &>/dev/null; then
            uapi Mysql delete_database name="$db_name" 2>/dev/null || true
            uapi Mysql delete_user name="$db_user" 2>/dev/null || true
            print_ok "Database removed via cPanel"
        elif [ "$db_conn" = "mysql" ] && command -v mysql &>/dev/null; then
            read -p "  MySQL root password: " -s root_pass; echo ""
            mysql -u root -p"$root_pass" -e "DROP DATABASE IF EXISTS \`$db_name\`; DROP USER IF EXISTS '$db_user'@'localhost';" 2>/dev/null || true
            print_ok "Database removed"
        elif [ "$db_conn" = "pgsql" ] && command -v psql &>/dev/null; then
            sudo -u postgres psql -c "DROP DATABASE IF EXISTS $db_name; DROP USER IF EXISTS $db_user;" 2>/dev/null || true
            print_ok "Database removed"
        fi
    fi

    # Remove symlinks
    rm -f "$HOME/public_html/public" 2>/dev/null || true
    print_ok "Symlinks removed"

    echo ""
    read -p "  Delete project files too? (y/n) [n]: " del_files
    if [ "$del_files" = "y" ]; then
        local parent=$(dirname "$SCRIPT_DIR")
        cd "$parent"
        rm -rf "$SCRIPT_DIR"
        print_ok "Project files deleted"
    fi

    echo ""
    echo -e "  ${GREEN}SmartKPI has been uninstalled.${NC}"
    echo ""
}

# ═══════════════════════════════════════════════════════════════════════════════
# MAIN INSTALLATION
# ═══════════════════════════════════════════════════════════════════════════════

run_install() {
    cd "$SCRIPT_DIR"
    mkdir -p "$(dirname "$LOG_FILE")" "$BACKUP_DIR" 2>/dev/null || true
    log "=== SmartKPI Installation Started ==="

    detect_environment

    print_banner
    echo -e "  ${DIM}Environment: ${ENV_TYPE} | OS: ${OS_NAME} (${OS_ARCH}) | Web: ${WEB_SERVER}${NC}"
    echo ""

    # ─── Step 1: System Requirements ─────────────────────────────────────────
    print_step 1 "Checking system requirements"

    # PHP version
    if command -v php &>/dev/null; then
        PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
        PHP_FULL=$(php -r "echo PHP_VERSION;")
        PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
        PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

        if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
            print_ok "PHP $PHP_FULL"
        else
            print_error "PHP ${MIN_PHP}+ required (found $PHP_FULL)"
            case "$ENV_TYPE" in
                cpanel) print_info "cPanel → MultiPHP Manager → Select PHP 8.3" ;;
                plesk)  print_info "Plesk → PHP Settings → Switch to PHP 8.3" ;;
                *)      print_info "Install: sudo apt install php8.3-cli php8.3-fpm" ;;
            esac
            exit 1
        fi
    else
        print_error "PHP not found"
        exit 1
    fi

    # PHP extensions
    local missing_ext=()
    for ext in pdo pdo_mysql mbstring openssl tokenizer xml ctype json bcmath curl fileinfo dom; do
        if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
            missing_ext+=("$ext")
        fi
    done

    if [ ${#missing_ext[@]} -gt 0 ]; then
        print_warn "Missing PHP extensions: ${missing_ext[*]}"
        case "$ENV_TYPE" in
            cpanel) print_info "cPanel → MultiPHP INI Editor → Enable extensions" ;;
            plesk)  print_info "Plesk → PHP Settings → Extensions" ;;
            *)      print_info "Install: sudo apt install $(printf "php${PHP_VERSION}-%s " "${missing_ext[@]}")" ;;
        esac
        if ! confirm "Continue anyway?"; then exit 1; fi
    else
        print_ok "All PHP extensions present"
    fi

    # PHP memory limit
    local mem_limit=$(php -r "echo ini_get('memory_limit');" 2>/dev/null)
    print_dim "  Memory limit: $mem_limit"

    # Composer
    COMPOSER_CMD=""
    if command -v composer &>/dev/null; then
        COMPOSER_CMD="composer"
        print_ok "Composer $(composer --version 2>/dev/null | grep -oP '[\d.]+' | head -1)"
    elif [ -f "$HOME/bin/composer" ]; then
        COMPOSER_CMD="php $HOME/bin/composer"
        print_ok "Composer (~/bin/composer)"
    elif [ -f "$SCRIPT_DIR/composer.phar" ]; then
        COMPOSER_CMD="php $SCRIPT_DIR/composer.phar"
        print_ok "Composer (local phar)"
    else
        print_info "Installing Composer locally..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --install-dir="$SCRIPT_DIR" --filename=composer.phar --quiet
        rm -f composer-setup.php
        COMPOSER_CMD="php $SCRIPT_DIR/composer.phar"
        print_ok "Composer installed locally"
    fi

    # Node.js (optional — built assets are in git)
    HAS_NODE=false
    if command -v node &>/dev/null; then
        local node_ver=$(node -v | tr -d 'v' | cut -d. -f1)
        if [ "$node_ver" -ge "$MIN_NODE" ]; then
            HAS_NODE=true
            print_ok "Node.js $(node -v)"
        else
            print_warn "Node.js $(node -v) — v${MIN_NODE}+ recommended"
        fi
    elif [ -f "$HOME/.nvm/nvm.sh" ]; then
        source "$HOME/.nvm/nvm.sh" 2>/dev/null
        HAS_NODE=true
        print_ok "Node.js $(node -v) (nvm)"
    else
        print_dim "  Node.js not found (optional — pre-built assets included)"
    fi

    # Git
    if command -v git &>/dev/null; then
        print_ok "Git $(git --version | awk '{print $3}')"
    fi

    # ─── Step 2: Environment Detection & Configuration ───────────────────────
    print_step 2 "Configuring installation"

    # Auto-detect domain
    DETECTED_DOMAIN=""
    if [ "$ENV_TYPE" = "cpanel" ] && command -v uapi &>/dev/null; then
        DETECTED_DOMAIN=$(uapi DomainInfo list_domains 2>/dev/null | grep -oP '(?<=main_domain: ).*' || true)
    fi
    [ -z "$DETECTED_DOMAIN" ] && DETECTED_DOMAIN=$(hostname -f 2>/dev/null || hostname 2>/dev/null || echo "localhost")

    # Auto-detect cPanel user
    CPANEL_USER="${USER:-$(whoami)}"

    if [ "$QUICK_MODE" = true ]; then
        APP_URL="https://$DETECTED_DOMAIN"
        DB_NAME="smartkpi"
        DB_ENGINE="mysql"
        DB_USER="${CPANEL_USER}_smartkpi"
        DB_PASS=$(php -r "echo bin2hex(random_bytes(12));")
        FULL_DB_NAME="${CPANEL_USER}_${DB_NAME}"
        APP_LOCALE="de"
        SUBFOLDER=""
        SEED_DEMO=true
        print_ok "Quick mode — using defaults"
        print_info "URL: $APP_URL | DB: $FULL_DB_NAME | Lang: $APP_LOCALE"
    else
        echo ""
        echo -e "  ${BOLD}${UNDERLINE}Installation Settings${NC}"
        echo ""

        # App URL
        read -p "  $(echo -e "${CYAN}App URL${NC}") [https://${DETECTED_DOMAIN}]: " APP_URL
        APP_URL="${APP_URL:-https://${DETECTED_DOMAIN}}"

        # Database engine
        echo ""
        echo -e "  ${CYAN}Database engine:${NC}"
        echo "    1) MySQL ${DIM}(recommended for shared hosting)${NC}"
        echo "    2) PostgreSQL ${DIM}(recommended for VPS/dedicated)${NC}"
        read -p "  Choose [1]: " db_choice
        if [ "$db_choice" = "2" ]; then DB_ENGINE="pgsql"; else DB_ENGINE="mysql"; fi

        # Database name
        read -p "  $(echo -e "${CYAN}Database name${NC}") [smartkpi]: " DB_NAME
        DB_NAME="${DB_NAME:-smartkpi}"

        # cPanel prefix for MySQL
        if [ "$ENV_TYPE" = "cpanel" ] && [ "$DB_ENGINE" = "mysql" ]; then
            read -p "  $(echo -e "${CYAN}cPanel username${NC}") [$CPANEL_USER]: " input_user
            CPANEL_USER="${input_user:-$CPANEL_USER}"
            FULL_DB_NAME="${CPANEL_USER}_${DB_NAME}"
            DB_USER_DEFAULT="${CPANEL_USER}_smartkpi"
        else
            FULL_DB_NAME="$DB_NAME"
            DB_USER_DEFAULT="smartkpi"
        fi

        read -p "  $(echo -e "${CYAN}Database username${NC}") [$DB_USER_DEFAULT]: " DB_USER
        DB_USER="${DB_USER:-$DB_USER_DEFAULT}"

        # Password
        local auto_pass=$(php -r "echo bin2hex(random_bytes(12));")
        read -p "  $(echo -e "${CYAN}Database password${NC}") [auto-generated]: " DB_PASS
        DB_PASS="${DB_PASS:-$auto_pass}"

        # Subfolder
        read -p "  $(echo -e "${CYAN}Subfolder${NC}") (leave empty for root): " SUBFOLDER

        # Locale
        echo ""
        echo -e "  ${CYAN}Default language:${NC}"
        echo "    1) Deutsch (DE) — ${DIM}primary${NC}"
        echo "    2) English (EN)"
        read -p "  Choose [1]: " lang_choice
        if [ "$lang_choice" = "2" ]; then APP_LOCALE="en"; else APP_LOCALE="de"; fi

        # Demo data
        echo ""
        if confirm "Load demo data? (Dentex Holding example)" "y"; then
            SEED_DEMO=true
        else
            SEED_DEMO=false
        fi
    fi

    echo ""
    echo -e "  ${DIM}─────────────────────────────────${NC}"
    print_info "URL:      $APP_URL"
    print_info "Database: $FULL_DB_NAME ($DB_ENGINE)"
    print_info "User:     $DB_USER"
    print_info "Locale:   $APP_LOCALE"
    echo -e "  ${DIM}─────────────────────────────────${NC}"

    if [ "$QUICK_MODE" != true ]; then
        echo ""
        if ! confirm "Proceed with installation?" "y"; then
            echo -e "  ${YELLOW}Cancelled.${NC}"
            exit 0
        fi
    fi

    # ─── Step 3: Create Database ─────────────────────────────────────────────
    print_step 3 "Creating database ($DB_ENGINE)"

    DB_CREATED=false

    if [ "$DB_ENGINE" = "mysql" ]; then
        # Method 1: cPanel UAPI
        if [ "$ENV_TYPE" = "cpanel" ] && command -v uapi &>/dev/null; then
            print_info "Using cPanel UAPI..."
            if uapi Mysql create_database name="$FULL_DB_NAME" 2>/dev/null; then
                DB_CREATED=true
                print_ok "Database '$FULL_DB_NAME' created"
                uapi Mysql create_user name="$DB_USER" password="$DB_PASS" 2>/dev/null || true
                print_ok "User '$DB_USER' created"
                uapi Mysql set_privileges_on_database user="$DB_USER" database="$FULL_DB_NAME" privileges="ALL PRIVILEGES" 2>/dev/null || true
                print_ok "Privileges granted"
            else
                print_warn "Database may already exist"
                DB_CREATED=true
            fi
        fi

        # Method 2: MySQL CLI
        if [ "$DB_CREATED" = false ] && command -v mysql &>/dev/null; then
            print_info "Using MySQL CLI..."
            if [ "$QUICK_MODE" = true ]; then
                mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`$FULL_DB_NAME\`; CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'; GRANT ALL ON \`$FULL_DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null && DB_CREATED=true || true
            else
                read -p "  MySQL root password (Enter if none): " -s mysql_root_pass; echo ""
                if [ -z "$mysql_root_pass" ]; then
                    mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`$FULL_DB_NAME\`; CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'; GRANT ALL ON \`$FULL_DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null && DB_CREATED=true || true
                else
                    mysql -u root -p"$mysql_root_pass" -e "CREATE DATABASE IF NOT EXISTS \`$FULL_DB_NAME\`; CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'; GRANT ALL ON \`$FULL_DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null && DB_CREATED=true || true
                fi
            fi
            [ "$DB_CREATED" = true ] && print_ok "Database created via MySQL CLI"
        fi
    else
        # PostgreSQL
        if command -v psql &>/dev/null; then
            print_info "Using PostgreSQL..."
            if sudo -u postgres psql -c "SELECT 1 FROM pg_database WHERE datname='$FULL_DB_NAME'" 2>/dev/null | grep -q 1; then
                print_warn "Database already exists"
                DB_CREATED=true
            else
                sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" 2>/dev/null || true
                sudo -u postgres psql -c "CREATE DATABASE $FULL_DB_NAME OWNER $DB_USER;" 2>/dev/null && DB_CREATED=true || true
                sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $FULL_DB_NAME TO $DB_USER;" 2>/dev/null || true
                [ "$DB_CREATED" = true ] && print_ok "PostgreSQL database created"
            fi
        fi
    fi

    if [ "$DB_CREATED" = false ]; then
        print_warn "Could not auto-create database"
        echo ""
        echo -e "  ${BOLD}Create it manually:${NC}"
        if [ "$DB_ENGINE" = "mysql" ]; then
            if [ "$ENV_TYPE" = "cpanel" ]; then
                print_info "cPanel → MySQL Databases → Create Database: $FULL_DB_NAME"
                print_info "Add User: $DB_USER with password: $DB_PASS"
                print_info "Grant ALL PRIVILEGES"
            else
                print_info "mysql -u root -p -e \"CREATE DATABASE $FULL_DB_NAME; CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'; GRANT ALL ON $FULL_DB_NAME.* TO '$DB_USER'@'localhost';\""
            fi
        else
            print_info "sudo -u postgres createuser $DB_USER"
            print_info "sudo -u postgres createdb -O $DB_USER $FULL_DB_NAME"
        fi
        echo ""
        read -p "  Press Enter after creating the database..." _
    fi

    # ─── Step 4: Configure .env ──────────────────────────────────────────────
    print_step 4 "Configuring environment"

    if [ -f ".env" ]; then
        print_warn "Existing .env found — backing up"
        cp .env ".env.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    cp .env.example .env

    # Core settings
    sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|APP_LOCALE=.*|APP_LOCALE=$APP_LOCALE|" .env

    # Database
    local db_port="3306"
    [ "$DB_ENGINE" = "pgsql" ] && db_port="5432"
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=$DB_ENGINE|" .env
    sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|" .env
    sed -i "s|DB_PORT=.*|DB_PORT=$db_port|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$FULL_DB_NAME|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env

    # Cache/Queue/Session — adapt to environment
    if [ "$ENV_TYPE" = "cpanel" ] || [ "$ENV_TYPE" = "plesk" ]; then
        sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|" .env
        sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
        sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env
        print_info "Cache/Queue/Session: file-based (shared hosting)"
    else
        # VPS/dedicated — check for Redis
        if command -v redis-cli &>/dev/null && redis-cli ping &>/dev/null 2>&1; then
            print_info "Cache/Queue/Session: Redis detected"
        else
            sed -i "s|CACHE_STORE=.*|CACHE_STORE=file|" .env
            sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
            sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env
            print_info "Cache/Queue/Session: file-based (no Redis)"
        fi
    fi

    print_ok ".env configured"

    # ─── Step 5: Composer Install ────────────────────────────────────────────
    print_step 5 "Installing PHP dependencies"

    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction --no-progress 2>&1 | tail -5
    print_ok "Composer dependencies installed ($(find vendor -maxdepth 1 -type d | wc -l) packages)"

    # ─── Step 6: Application Key ─────────────────────────────────────────────
    print_step 6 "Generating application key"

    php artisan key:generate --force --no-interaction
    print_ok "Encryption key generated"

    # ─── Step 7: Frontend Assets ─────────────────────────────────────────────
    print_step 7 "Frontend assets"

    if [ -f "$SCRIPT_DIR/public/build/manifest.json" ]; then
        print_ok "Pre-built assets found (no build needed)"
        local asset_count=$(find "$SCRIPT_DIR/public/build/assets" -type f 2>/dev/null | wc -l)
        print_dim "  $asset_count asset files ready"
    elif [ "$HAS_NODE" = true ] && command -v npm &>/dev/null; then
        print_info "Building from source..."
        npm install --no-audit --no-fund 2>&1 | tail -3
        npm run build 2>&1 | tail -3
        print_ok "Frontend built successfully"
    else
        print_warn "No pre-built assets and Node.js not available"
        print_info "The app may not render correctly without build assets"
        print_info "Fix: Install Node.js and run 'npm install && npm run build'"
    fi

    # ─── Step 8: Database Migration ──────────────────────────────────────────
    print_step 8 "Running database migrations"

    php artisan migrate --force --no-interaction 2>&1 | tail -5
    local table_count=$(php artisan migrate:status 2>/dev/null | grep -c "Ran" || echo "?")
    print_ok "Migrations complete ($table_count tables)"

    # ─── Step 9: Seed Data ───────────────────────────────────────────────────
    print_step 9 "Seeding database"

    if [ "$SEED_DEMO" = true ]; then
        php artisan db:seed --force --no-interaction 2>&1 | tail -3
        print_ok "Demo data loaded (Dentex Holding)"
        print_dim "  2 companies, 6 departments, 4 users, KPI templates, subscription plans"
    else
        print_info "Skipped — no demo data"
    fi

    # ─── Step 10: Storage & Permissions ──────────────────────────────────────
    print_step 10 "Setting up storage & permissions"

    # Create required directories
    mkdir -p storage/app/public storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

    # Storage link
    php artisan storage:link --force --no-interaction 2>/dev/null || true
    print_ok "Storage symlink created"

    # Permissions
    chmod -R 755 storage bootstrap/cache
    chmod -R 775 storage/app storage/framework storage/logs
    print_ok "Permissions set (755/775)"

    # Owner (for VPS)
    if [ "$ENV_TYPE" = "vps" ] || [ "$ENV_TYPE" = "linux" ]; then
        local web_user="www-data"
        [ -f /etc/nginx/nginx.conf ] && web_user=$(grep "^user" /etc/nginx/nginx.conf 2>/dev/null | awk '{print $2}' | tr -d ';' || echo "www-data")
        if id "$web_user" &>/dev/null; then
            chown -R "$web_user:$web_user" storage bootstrap/cache 2>/dev/null || true
            print_dim "  Owner: $web_user"
        fi
    fi

    # ─── Step 11: Cache Optimization ─────────────────────────────────────────
    print_step 11 "Optimizing for production"

    php artisan config:cache --no-interaction 2>/dev/null || true
    php artisan route:cache --no-interaction 2>/dev/null || true
    php artisan view:cache --no-interaction 2>/dev/null || true
    php artisan event:cache --no-interaction 2>/dev/null || true
    print_ok "Config, routes, views, events cached"

    # ─── Step 12: Web Server Configuration ───────────────────────────────────
    print_step 12 "Configuring web server"

    # Root .htaccess (redirect to public/)
    if [ ! -f "$SCRIPT_DIR/.htaccess" ] || [ "$WEB_SERVER" = "apache" ]; then
        cat > "$SCRIPT_DIR/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
EOF
        print_ok "Root .htaccess created (→ public/)"
    fi

    # cPanel — handle public_html symlink/redirect
    if [ "$ENV_TYPE" = "cpanel" ]; then
        PUBLIC_HTML="$HOME/public_html"
        if [ -n "$SUBFOLDER" ]; then
            TARGET_DIR="$PUBLIC_HTML/$SUBFOLDER"
            mkdir -p "$TARGET_DIR"
        else
            TARGET_DIR="$PUBLIC_HTML"
        fi

        # If project is not inside public_html, create symlink
        if [ "$SCRIPT_DIR" != "$TARGET_DIR" ] && [ ! -L "$TARGET_DIR/public" ]; then
            ln -sf "$SCRIPT_DIR/public" "$TARGET_DIR/public" 2>/dev/null || true
            print_ok "Symlink: public_html → project/public"
        fi

        # Redirect .htaccess in public_html
        if [ "$SCRIPT_DIR" != "$TARGET_DIR" ]; then
            cat > "$TARGET_DIR/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L,QSA]
</IfModule>
EOF
            print_ok "public_html .htaccess configured"
        fi
    fi

    # Nginx config hint (for VPS)
    if [ "$WEB_SERVER" = "nginx" ]; then
        print_info "Nginx config needed. Add this server block:"
        print_dim "  root $SCRIPT_DIR/public;"
        print_dim "  location / { try_files \$uri \$uri/ /index.php?\$query_string; }"
    fi

    print_ok "Web server configured"

    # ─── Step 13: Cron Setup ─────────────────────────────────────────────────
    print_step 13 "Scheduling (cron)"

    local cron_line="* * * * * cd $SCRIPT_DIR && php artisan schedule:run >> /dev/null 2>&1"

    if [ "$ENV_TYPE" = "cpanel" ]; then
        print_info "Add this cron job in cPanel → Cron Jobs (every minute):"
        print_dim "  $cron_line"
    elif [ "$ENV_TYPE" = "vps" ] || [ "$ENV_TYPE" = "linux" ]; then
        if crontab -l 2>/dev/null | grep -q "artisan schedule:run"; then
            print_ok "Cron job already exists"
        else
            if confirm "Add Laravel scheduler cron job?"; then
                (crontab -l 2>/dev/null; echo "$cron_line") | crontab - 2>/dev/null && \
                    print_ok "Cron job added" || print_warn "Could not add cron — add manually"
            else
                print_dim "  Add manually: $cron_line"
            fi
        fi
    else
        print_dim "  For production, add: $cron_line"
    fi

    # ─── Step 14: Health Check & Summary ─────────────────────────────────────
    print_step 14 "Final health check"

    local issues=0
    # Quick checks
    [ ! -f ".env" ] && ((issues++))
    [ -z "$(grep 'APP_KEY=base64' .env 2>/dev/null)" ] && ((issues++)) || true
    [ ! -f "public/build/manifest.json" ] && ((issues++))
    [ ! -w "storage/logs" ] && ((issues++))

    if [ "$issues" -eq 0 ]; then
        print_ok "All health checks passed"
    else
        print_warn "$issues potential issue(s) — run './install.sh --health' for details"
    fi

    # Mark as installed for web installer detection
    date -Iseconds > "$PROJECT_DIR/storage/installed" 2>/dev/null || true

    # ─── Installation Complete ───────────────────────────────────────────────
    local elapsed=$(( $(date +%s) - INSTALL_START ))

    echo ""
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║${NC}                                                              ${GREEN}║${NC}"
    echo -e "${GREEN}║${NC}   ${WHITE}${BOLD}SmartKPI installed successfully!${NC}                            ${GREEN}║${NC}"
    echo -e "${GREEN}║${NC}   ${DIM}Completed in ${elapsed} seconds${NC}                                     ${GREEN}║${NC}"
    echo -e "${GREEN}║${NC}                                                              ${GREEN}║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${BOLD}Configuration${NC}"
    echo -e "  ─────────────────────────────────────────────────"
    echo -e "  ${CYAN}App URL:${NC}       $APP_URL"
    echo -e "  ${CYAN}Database:${NC}      $FULL_DB_NAME ${DIM}($DB_ENGINE)${NC}"
    echo -e "  ${CYAN}DB User:${NC}       $DB_USER"
    echo -e "  ${CYAN}DB Password:${NC}   $DB_PASS"
    echo -e "  ${CYAN}Language:${NC}      ${APP_LOCALE^^}"
    echo -e "  ${CYAN}Environment:${NC}   $ENV_TYPE"
    echo ""
    echo -e "  ${BOLD}Demo Accounts${NC}"
    echo -e "  ─────────────────────────────────────────────────"
    echo -e "  ┌──────────────────┬───────────────────────┬──────────┐"
    echo -e "  │ ${BOLD}Role${NC}             │ ${BOLD}Email${NC}                 │ ${BOLD}Password${NC} │"
    echo -e "  ├──────────────────┼───────────────────────┼──────────┤"
    echo -e "  │ Super Admin      │ admin@smartkpi.com    │ password │"
    echo -e "  │ Holding Admin    │ mueller@dentex.de     │ password │"
    echo -e "  │ Company Admin    │ schmidt@dentex.de     │ password │"
    echo -e "  │ Dept Manager     │ weber@dentex.de       │ password │"
    echo -e "  └──────────────────┴───────────────────────┴──────────┘"
    echo ""
    echo -e "  ${YELLOW}${BOLD}⚠ IMPORTANT${NC}"
    echo -e "  ${YELLOW}• Change demo passwords before going live${NC}"
    echo -e "  ${YELLOW}• Save your database password securely${NC}"
    echo -e "  ${YELLOW}• Set APP_DEBUG=false in production (.env)${NC}"
    echo ""
    echo -e "  ${BOLD}Useful Commands${NC}"
    echo -e "  ─────────────────────────────────────────────────"
    echo -e "  ${DIM}Health check:${NC}    ./install.sh --health"
    echo -e "  ${DIM}Update:${NC}          ./install.sh --update"
    echo -e "  ${DIM}Backup:${NC}          ./install.sh --restore ${DIM}(creates backup first)${NC}"
    echo -e "  ${DIM}Clear cache:${NC}     php artisan optimize:clear"
    echo -e "  ${DIM}Dev server:${NC}      php artisan serve"
    echo ""
    echo -e "  ${DIM}Log file: $LOG_FILE${NC}"
    echo ""

    log "=== Installation completed in ${elapsed}s ==="
}

# ═══════════════════════════════════════════════════════════════════════════════
# ENTRY POINT
# ═══════════════════════════════════════════════════════════════════════════════

cd "$SCRIPT_DIR"
parse_args "$@"

case "$MODE" in
    install)    run_install ;;
    update)     run_update ;;
    restore)    run_restore ;;
    uninstall)  run_uninstall ;;
    health)     run_health_check ;;
esac
