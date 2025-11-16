#!/bin/bash

###############################################################################
# Workset MySQL Binary Log Backup Script
#
# Backs up MySQL binary logs for point-in-time recovery
# Retention: 7 days
#
# Usage: ./backup-binlogs.sh
#
# Required environment variables:
#   - MYSQL_ROOT_PASSWORD
#   - DO_SPACES_KEY (optional)
#   - DO_SPACES_SECRET (optional)
#   - DO_SPACES_BUCKET (optional)
#   - DO_SPACES_REGION (optional)
###############################################################################

set -euo pipefail

# Configuration
BACKUP_DIR="/var/backups/workset/binlogs"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

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

# Ensure backup directory exists
mkdir -p "$BACKUP_DIR"

log_info "Starting binlog backup at $(date)"

# Check required environment variables
if [ -z "${MYSQL_ROOT_PASSWORD:-}" ]; then
    log_error "MYSQL_ROOT_PASSWORD not set"
    exit 1
fi

# Get current binlog file
CURRENT_BINLOG=$(docker-compose exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SHOW MASTER STATUS\G" | grep File | awk '{print $2}')

if [ -z "$CURRENT_BINLOG" ]; then
    log_error "Failed to get current binlog file"
    exit 1
fi

log_info "Current binlog: ${CURRENT_BINLOG}"

# Flush logs to rotate binlog
log_info "Flushing logs to rotate binlog..."
docker-compose exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "FLUSH BINARY LOGS"

# Get list of binlog files (excluding the current one)
BINLOG_FILES=$(docker-compose exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SHOW BINARY LOGS" | grep -v File | awk '{print $1}' | grep -v "^$CURRENT_BINLOG$" || true)

if [ -z "$BINLOG_FILES" ]; then
    log_warn "No binlog files to backup"
    exit 0
fi

# Copy binlog files to backup directory
BACKUP_COUNT=0
for BINLOG in $BINLOG_FILES; do
    log_info "Backing up binlog: ${BINLOG}"

    # Copy from container to host
    docker-compose exec -T mysql cat "/var/lib/mysql/${BINLOG}" > "${BACKUP_DIR}/${BINLOG}.${DATE}"

    if [ $? -eq 0 ]; then
        # Compress
        gzip "${BACKUP_DIR}/${BINLOG}.${DATE}"
        ((BACKUP_COUNT++))
        log_info "Backed up: ${BINLOG}.${DATE}.gz"
    else
        log_error "Failed to backup: ${BINLOG}"
    fi
done

log_info "Backed up ${BACKUP_COUNT} binlog files"

# Upload to DigitalOcean Spaces (if credentials provided)
if [ -n "${DO_SPACES_KEY:-}" ] && [ -n "${DO_SPACES_SECRET:-}" ] && [ $BACKUP_COUNT -gt 0 ]; then
    log_info "Uploading binlogs to DigitalOcean Spaces..."

    # Configure AWS CLI for DO Spaces
    export AWS_ACCESS_KEY_ID="${DO_SPACES_KEY}"
    export AWS_SECRET_ACCESS_KEY="${DO_SPACES_SECRET}"

    SPACES_ENDPOINT="https://${DO_SPACES_REGION}.digitaloceanspaces.com"

    for BINLOG_FILE in ${BACKUP_DIR}/*.gz; do
        if [ -f "$BINLOG_FILE" ]; then
            FILENAME=$(basename "$BINLOG_FILE")
            SPACES_PATH="backups/binlogs/${FILENAME}"

            aws s3 cp \
                "$BINLOG_FILE" \
                "s3://${DO_SPACES_BUCKET}/${SPACES_PATH}" \
                --endpoint-url="${SPACES_ENDPOINT}" \
                --storage-class STANDARD

            if [ $? -eq 0 ]; then
                log_info "Uploaded: ${FILENAME}"
            else
                log_error "Failed to upload: ${FILENAME}"
            fi
        fi
    done
else
    if [ $BACKUP_COUNT -gt 0 ]; then
        log_warn "DO Spaces credentials not configured, skipping upload"
    fi
fi

# Clean up old local binlog backups
log_info "Cleaning up old binlog backups (retention: ${RETENTION_DAYS} days)..."
find "${BACKUP_DIR}" -name "*.gz" -type f -mtime +${RETENTION_DAYS} -delete
REMAINING=$(find "${BACKUP_DIR}" -name "*.gz" -type f | wc -l)
log_info "Local binlog backups remaining: ${REMAINING}"

# Clean up old Spaces backups (if configured)
if [ -n "${DO_SPACES_KEY:-}" ] && [ -n "${DO_SPACES_SECRET:-}" ]; then
    log_info "Cleaning up old Spaces binlog backups..."

    CUTOFF_DATE=$(date -d "${RETENTION_DAYS} days ago" +%s)

    aws s3 ls \
        "s3://${DO_SPACES_BUCKET}/backups/binlogs/" \
        --endpoint-url="${SPACES_ENDPOINT}" \
        --recursive | while read -r line; do

        FILE_DATE=$(echo "$line" | awk '{print $1" "$2}')
        FILE_PATH=$(echo "$line" | awk '{print $4}')
        FILE_TIMESTAMP=$(date -d "$FILE_DATE" +%s)

        if [ "$FILE_TIMESTAMP" -lt "$CUTOFF_DATE" ]; then
            log_info "Deleting old binlog backup: ${FILE_PATH}"
            aws s3 rm \
                "s3://${DO_SPACES_BUCKET}/${FILE_PATH}" \
                --endpoint-url="${SPACES_ENDPOINT}"
        fi
    done
fi

# Purge old binlogs from MySQL (keep 7 days)
log_info "Purging old binlogs from MySQL..."
PURGE_DATE=$(date -d "7 days ago" "+%Y-%m-%d %H:%M:%S")
docker-compose exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "PURGE BINARY LOGS BEFORE '${PURGE_DATE}'"

log_info "Binlog backup completed successfully at $(date)"

# Summary
echo ""
echo "====================================="
echo "Binlog Backup Summary"
echo "====================================="
echo "Date: $(date)"
echo "Files backed up: ${BACKUP_COUNT}"
echo "Retention: ${RETENTION_DAYS} days"
echo "Location: ${BACKUP_DIR}"
echo "====================================="

exit 0
