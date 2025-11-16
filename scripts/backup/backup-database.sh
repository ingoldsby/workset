#!/bin/bash

###############################################################################
# Workset Database Backup Script
#
# Creates encrypted MySQL backups and uploads to DigitalOcean Spaces
# Retention: 30 days
#
# Usage: ./backup-database.sh
#
# Required environment variables:
#   - MYSQL_ROOT_PASSWORD
#   - BACKUP_ENCRYPTION_KEY
#   - DO_SPACES_KEY
#   - DO_SPACES_SECRET
#   - DO_SPACES_BUCKET
#   - DO_SPACES_REGION
###############################################################################

set -euo pipefail

# Configuration
BACKUP_DIR="/var/backups/workset/database"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="workset_${DATE}.sql.gz"
ENCRYPTED_NAME="${BACKUP_NAME}.enc"
RETENTION_DAYS=30

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

log_info "Starting database backup at $(date)"

# Check required environment variables
if [ -z "${MYSQL_ROOT_PASSWORD:-}" ]; then
    log_error "MYSQL_ROOT_PASSWORD not set"
    exit 1
fi

if [ -z "${BACKUP_ENCRYPTION_KEY:-}" ]; then
    log_error "BACKUP_ENCRYPTION_KEY not set"
    exit 1
fi

# Create database backup
log_info "Creating database dump..."
docker-compose exec -T mysql mysqldump \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    --events \
    -u root \
    -p"${MYSQL_ROOT_PASSWORD}" \
    workset | gzip > "${BACKUP_DIR}/${BACKUP_NAME}"

if [ $? -ne 0 ]; then
    log_error "Database dump failed"
    exit 1
fi

# Get backup size
BACKUP_SIZE=$(du -h "${BACKUP_DIR}/${BACKUP_NAME}" | cut -f1)
log_info "Backup created: ${BACKUP_NAME} (${BACKUP_SIZE})"

# Encrypt backup
log_info "Encrypting backup..."
openssl enc -aes-256-cbc \
    -salt \
    -pbkdf2 \
    -in "${BACKUP_DIR}/${BACKUP_NAME}" \
    -out "${BACKUP_DIR}/${ENCRYPTED_NAME}" \
    -pass pass:"${BACKUP_ENCRYPTION_KEY}"

if [ $? -ne 0 ]; then
    log_error "Encryption failed"
    rm -f "${BACKUP_DIR}/${BACKUP_NAME}"
    exit 1
fi

# Remove unencrypted backup
rm -f "${BACKUP_DIR}/${BACKUP_NAME}"
log_info "Backup encrypted: ${ENCRYPTED_NAME}"

# Upload to DigitalOcean Spaces (if credentials provided)
if [ -n "${DO_SPACES_KEY:-}" ] && [ -n "${DO_SPACES_SECRET:-}" ]; then
    log_info "Uploading to DigitalOcean Spaces..."

    # Configure AWS CLI for DO Spaces
    export AWS_ACCESS_KEY_ID="${DO_SPACES_KEY}"
    export AWS_SECRET_ACCESS_KEY="${DO_SPACES_SECRET}"

    SPACES_ENDPOINT="https://${DO_SPACES_REGION}.digitaloceanspaces.com"
    SPACES_PATH="backups/database/${ENCRYPTED_NAME}"

    aws s3 cp \
        "${BACKUP_DIR}/${ENCRYPTED_NAME}" \
        "s3://${DO_SPACES_BUCKET}/${SPACES_PATH}" \
        --endpoint-url="${SPACES_ENDPOINT}" \
        --storage-class STANDARD

    if [ $? -eq 0 ]; then
        log_info "Upload successful: s3://${DO_SPACES_BUCKET}/${SPACES_PATH}"
    else
        log_error "Upload to Spaces failed"
        exit 1
    fi
else
    log_warn "DO Spaces credentials not configured, skipping upload"
fi

# Clean up old local backups
log_info "Cleaning up old backups (retention: ${RETENTION_DAYS} days)..."
find "${BACKUP_DIR}" -name "workset_*.sql.gz.enc" -type f -mtime +${RETENTION_DAYS} -delete
CLEANED=$(find "${BACKUP_DIR}" -name "workset_*.sql.gz.enc" -type f | wc -l)
log_info "Local backups remaining: ${CLEANED}"

# Clean up old Spaces backups (if configured)
if [ -n "${DO_SPACES_KEY:-}" ] && [ -n "${DO_SPACES_SECRET:-}" ]; then
    log_info "Cleaning up old Spaces backups..."

    CUTOFF_DATE=$(date -d "${RETENTION_DAYS} days ago" +%Y%m%d)

    aws s3 ls \
        "s3://${DO_SPACES_BUCKET}/backups/database/" \
        --endpoint-url="${SPACES_ENDPOINT}" \
        --recursive | while read -r line; do

        FILE_DATE=$(echo "$line" | awk '{print $4}' | grep -oP 'workset_\K\d{8}')
        FILE_PATH=$(echo "$line" | awk '{print $4}')

        if [ -n "$FILE_DATE" ] && [ "$FILE_DATE" -lt "$CUTOFF_DATE" ]; then
            log_info "Deleting old backup: ${FILE_PATH}"
            aws s3 rm \
                "s3://${DO_SPACES_BUCKET}/${FILE_PATH}" \
                --endpoint-url="${SPACES_ENDPOINT}"
        fi
    done
fi

log_info "Database backup completed successfully at $(date)"
log_info "Backup location: ${BACKUP_DIR}/${ENCRYPTED_NAME}"

# Summary
echo ""
echo "====================================="
echo "Backup Summary"
echo "====================================="
echo "Date: $(date)"
echo "File: ${ENCRYPTED_NAME}"
echo "Size: ${BACKUP_SIZE} (compressed)"
echo "Encryption: AES-256-CBC"
echo "Retention: ${RETENTION_DAYS} days"
echo "====================================="

exit 0
