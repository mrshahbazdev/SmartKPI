#!/bin/bash
#
# SmartKPI — Uninstaller
# Removes database, cached files, and optionally the project folder.
#

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BOLD='\033[1m'
NC='\033[0m'

echo ""
echo -e "${RED}${BOLD}SmartKPI Uninstaller${NC}"
echo -e "${RED}════════════════════${NC}"
echo ""

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Read DB info from .env
if [ -f ".env" ]; then
    DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
    DB_USER=$(grep "^DB_USERNAME=" .env | cut -d= -f2)
    echo -e "  Database: ${BOLD}$DB_NAME${NC}"
    echo -e "  User:     ${BOLD}$DB_USER${NC}"
    echo ""
fi

read -p "  Are you sure you want to uninstall SmartKPI? (type 'yes'): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo -e "  ${GREEN}Cancelled.${NC}"
    exit 0
fi

echo ""

# Clear Laravel caches
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
echo -e "  ${GREEN}✓${NC} Caches cleared"

# Drop database
if command -v uapi &> /dev/null; then
    uapi Mysql delete_database name="$DB_NAME" 2>/dev/null || true
    uapi Mysql delete_user name="$DB_USER" 2>/dev/null || true
    echo -e "  ${GREEN}✓${NC} Database removed via cPanel"
elif command -v mysql &> /dev/null; then
    read -p "  MySQL root password: " -s ROOT_PASS
    echo ""
    mysql -u root -p"$ROOT_PASS" -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; DROP USER IF EXISTS '$DB_USER'@'localhost';" 2>/dev/null || true
    echo -e "  ${GREEN}✓${NC} Database removed via MySQL"
fi

# Remove symlinks
rm -f "$HOME/public_html/public" 2>/dev/null || true
echo -e "  ${GREEN}✓${NC} Symlinks removed"

echo ""
read -p "  Delete project files too? (y/n): " DEL_FILES
if [ "$DEL_FILES" = "y" ]; then
    cd "$HOME"
    rm -rf "$SCRIPT_DIR"
    echo -e "  ${GREEN}✓${NC} Project files deleted"
fi

echo ""
echo -e "  ${GREEN}SmartKPI has been uninstalled.${NC}"
echo ""
