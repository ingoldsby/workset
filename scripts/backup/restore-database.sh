#!/bin/bash

###############################################################################
# Workset Database Restore Script
#
# Restores an encrypted MySQL backup
#
# Usage: ./restore-database.sh <backup-file>
#   or: ./restore-database.sh --from-spaces <backup-name>
#
# Required environment variables:
#   - MYSQL_ROOT_PASSWORD
#   - BACKUP_ENCRYPTION_KEY
#   - DO_SPACES_KEY (if restoring from Spaces)
#   - DO_SPACES_SECRET (if restoring from Spaces)
#   - DO_SPACES_BUCKET (if restoring from Spaces)
#   - DO_SPACES_REGION (if restoring from Spaces)
###############################################################################

set -euo pipefail

# Configuration
RESTORE_DIR="/var/backups/workset/restore"

# Colours for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Colour

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

# Ensure restore directory exists
mkdir -p "$RESTORE_DIR"

# Check required environment variables
if [ -z "${MYSQL_ROOT_PASSWORD:-}" ]; then
    log_error "MYSQL_ROOT_PASSWORD not set"
    exit 1
fi

if [ -z "${BACKUP_ENCRYPTION_KEY:-}" ]; then
    log_error "BACKUP_ENCRYPTION_KEY not set"
    exit 1
fi

# Parse arguments
FROM_SPACES=false
BACKUP_FILE=""

if [ "$#" -lt 1 ]; then
    log_error "Usage: $0 <backup-file> or $0 --from-spaces <backup-name>"
    exit 1
fi

if [ "$1" == "--from-spaces" ]; then
    FROM_SPACES=true
    if [ "$#" -lt 2 ]; then
        log_error "Backup name required when using --from-spaces"
        exit 1
    fi
    BACKUP_NAME="$2"
else
    BACKUP_FILE="$1"
    if [ ! -f "$BACKUP_FILE" ]; then
        log_error "Backup file not found: $BACKUP_FILE"
        exit 1
    fi
fi

# Download from Spaces if requested
if [ "$FROM_SPACES" = true ]; then
    log_info "Downloading backup from DigitalOcean Spaces..."

    if [ -z "${DO_SPACES_KEY:-}" ] || [ -z "${DO_SPACES_SECRET:-}" ]; then
        log_error "DO Spaces credentials not set"
        exit 1
    fi

    export AWS_ACCESS_KEY_ID="${DO_SPACES_KEY}"
    export AWS_SECRET_ACCESS_KEY="${DO_SPACES_SECRET}"

    SPACES_ENDPOINT="https://${DO_SPACES_REGION}.digitaloceanspaces.com"
    SPACES_PATH="backups/database/${BACKUP_NAME}"
    BACKUP_FILE="${RESTORE_DIR}/${BACKUP_NAME}"

    aws s3 cp \
        "s3://${DO_SPACES_BUCKET}/${SPACES_PATH}" \
        "$BACKUP_FILE" \
        --endpoint-url="${SPACES_ENDPOINT}"

    if [ $? -ne 0 ]; then
        log_error "Failed to download backup from Spaces"
        exit 1
    fi

    log_info "Downloaded: ${BACKUP_NAME}"
fi

# Confirm restore
log_warn "=========================================="
log_warn "WARNING: This will REPLACE the current database!"
log_warn "Backup file: $(basename "$BACKUP_FILE")"
log_warn "=========================================="
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    log_info "Restore cancelled"
    exit 0
fi

# Decrypt backup
log_info "Decrypting backup..."
DECRYPTED_FILE="${RESTORE_DIR}/$(basename "$BACKUP_FILE" .enc)"

openssl enc -aes-256-cbc \
    -d \
    -pbkdf2 \
    -in "$BACKUP_FILE" \
    -out "$DECRYPTED_FILE" \
    -pass pass:"${BACKUP_ENCRYPTION_KEY}"

if [ $? -ne 0 ]; then
    log_error "Decryption failed - check BACKUP_ENCRYPTION_KEY"
    rm -f "$DECRYPTED_FILE"
    exit 1
fi

log_info "Backup decrypted successfully"

# Create pre-restore backup
log_info "Creating pre-restore backup of current database..."
PRE_RESTORE_BACKUP="${RESTORE_DIR}/pre_restore_$(date +%Y%m%d_%H%M%S).sql.gz"

docker-compose exec -T mysql mysqldump \
    --single-transaction \
    -u root \
    -p"${MYSQL_ROOT_PASSWORD}" \
    workset | gzip > "$PRE_RESTORE_BACKUP"

if [ $? -eq 0 ]; then
    log_info "Pre-restore backup created: $(basename "$PRE_RESTORE_BACKUP")"
else
    log_error "Failed to create pre-restore backup"
    rm -f "$DECRYPTED_FILE"
    exit 1
fi

# Restore database
log_info "Restoring database..."

gunzip -c "$DECRYPTED_FILE" | docker-compose exec -T mysql mysql \
    -u root \
    -p"${MYSQL_ROOT_PASSWORD}" \
    workset

if [ $? -ne 0 ]; then
    log_error "Database restore failed"
    log_error "Pre-restore backup available at: $PRE_RESTORE_BACKUP"
    rm -f "$DECRYPTED_FILE"
    exit 1
fi

log_info "Database restored successfully"

# Clean up
rm -f "$DECRYPTED_FILE"
if [ "$FROM_SPACES" = true ]; then
    rm -f "$BACKUP_FILE"
fi

# Run migrations to ensure schema is up-to-date
log_info "Running migrations..."
docker-compose exec -T app php artisan migrate --force

# Clear caches
log_info "Clearing caches..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan view:clear

# Restart Horizon
log_info "Restarting Horizon..."
docker-compose exec -T app php artisan horizon:terminate

log_info "Database restore completed successfully at $(date)"
log_info "Pre-restore backup saved at: $PRE_RESTORE_BACKUP"

# Summary
echo ""
echo "====================================="
echo "Restore Summary"
echo "====================================="
echo "Date: $(date)"
echo "Restored from: $(basename "$BACKUP_FILE")"
echo "Pre-restore backup: $(basename "$PRE_RESTORE_BACKUP")"
echo "====================================="

exit 0
