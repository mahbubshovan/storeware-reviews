#!/bin/bash

# Setup Cron Jobs for Shopify Reviews Per-Device Rate Limiting System
# This script sets up the necessary cron jobs for background scraping and cleanup

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Setting up Shopify Reviews Cron Jobs${NC}"
echo "========================================"

# Get the absolute path to the backend directory
BACKEND_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_PATH=$(which php)

if [ -z "$PHP_PATH" ]; then
    echo -e "${RED}Error: PHP not found in PATH${NC}"
    exit 1
fi

echo -e "${YELLOW}Backend directory: $BACKEND_DIR${NC}"
echo -e "${YELLOW}PHP path: $PHP_PATH${NC}"

# Create logs directory if it doesn't exist
LOGS_DIR="$BACKEND_DIR/../logs"
mkdir -p "$LOGS_DIR"
chmod 755 "$LOGS_DIR"

echo -e "${GREEN}Created logs directory: $LOGS_DIR${NC}"

# Backup existing crontab
echo -e "${YELLOW}Backing up existing crontab...${NC}"
crontab -l > "$BACKEND_DIR/cron/crontab_backup_$(date +%Y%m%d_%H%M%S).txt" 2>/dev/null || echo "No existing crontab found"

# Create new cron entries
CRON_ENTRIES="
# Shopify Reviews Per-Device Rate Limiting System
# Background scraper - runs every 5 minutes
*/5 * * * * $PHP_PATH $BACKEND_DIR/cron/background_scraper.php >> $LOGS_DIR/cron.log 2>&1

# Cleanup old logs - runs daily at 2 AM
0 2 * * * find $LOGS_DIR -name '*.log' -mtime +7 -delete

# Health check - runs every hour
0 * * * * $PHP_PATH $BACKEND_DIR/cron/health_check.php >> $LOGS_DIR/health.log 2>&1
"

# Add cron entries
echo -e "${YELLOW}Adding cron entries...${NC}"
(crontab -l 2>/dev/null; echo "$CRON_ENTRIES") | crontab -

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Cron jobs added successfully!${NC}"
else
    echo -e "${RED}❌ Failed to add cron jobs${NC}"
    exit 1
fi

# Display current crontab
echo -e "${YELLOW}Current crontab:${NC}"
crontab -l

echo ""
echo -e "${GREEN}Setup completed!${NC}"
echo ""
echo "Cron jobs configured:"
echo "  • Background scraper: Every 5 minutes"
echo "  • Log cleanup: Daily at 2 AM"
echo "  • Health check: Every hour"
echo ""
echo "Log files:"
echo "  • Background scraper: $LOGS_DIR/background_scraper.log"
echo "  • Cron output: $LOGS_DIR/cron.log"
echo "  • Health checks: $LOGS_DIR/health.log"
echo ""
echo -e "${YELLOW}To remove these cron jobs later, run:${NC}"
echo "  crontab -e"
echo "  # Then delete the Shopify Reviews section"
echo ""
echo -e "${YELLOW}To monitor logs:${NC}"
echo "  tail -f $LOGS_DIR/background_scraper.log"
echo "  tail -f $LOGS_DIR/cron.log"
