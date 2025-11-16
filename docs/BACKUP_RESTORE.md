# Workset Backup & Restore Documentation

## Overview

Workset uses a multi-layered backup strategy to ensure data safety:

1. **Daily Full Database Backups** (30-day retention)
2. **Hourly Binary Log Backups** (7-day retention for point-in-time recovery)
3. **Pre-deployment Backups** (automated before production deployments)

All backups are:
- Encrypted with AES-256-CBC
- Stored locally on the server
- Uploaded to DigitalOcean Spaces (S3-compatible object storage)
- Automatically cleaned up based on retention policies

## Backup Strategy

### Daily Full Backups

**Schedule**: 2:00 AM daily (via cron)
**Retention**: 30 days
**Location**:
- Local: `/var/backups/workset/database/`
- Spaces: `s3://{bucket}/backups/database/`

**Script**: `scripts/backup/backup-database.sh`

**What's Backed Up**:
- Complete MySQL database dump
- All routines, triggers, and events
- Compressed with gzip
- Encrypted with AES-256-CBC

**Naming Convention**: `workset_YYYYMMDD_HHMMSS.sql.gz.enc`

### Binary Log Backups

**Schedule**: Every 6 hours (via cron)
**Retention**: 7 days
**Location**:
- Local: `/var/backups/workset/binlogs/`
- Spaces: `s3://{bucket}/backups/binlogs/`

**Script**: `scripts/backup/backup-binlogs.sh`

**What's Backed Up**:
- MySQL binary logs for point-in-time recovery
- Allows restore to any point within 7-day window
- Compressed with gzip

**Naming Convention**: `mysql-bin.000001.YYYYMMDD_HHMMSS.gz`

### Pre-deployment Backups

**Trigger**: Automated before each production deployment
**Retention**: Manual cleanup
**Location**: `/var/backups/workset/pre-deployment/`

**What's Backed Up**:
- Complete database snapshot before code deployment
- Safety net for rollback scenarios

## Setting Up Automated Backups

### 1. Configure Environment Variables

Add to your `.env` or server environment:

```bash
# MySQL
MYSQL_ROOT_PASSWORD=your_secure_password

# Backup encryption
BACKUP_ENCRYPTION_KEY=your_32_char_encryption_key_here

# DigitalOcean Spaces
DO_SPACES_KEY=your_spaces_access_key
DO_SPACES_SECRET=your_spaces_secret_key
DO_SPACES_BUCKET=workset-backups
DO_SPACES_REGION=sgp1  # Singapore datacenter
```

### 2. Install AWS CLI

The backup scripts use AWS CLI (compatible with DigitalOcean Spaces):

```bash
# Ubuntu/Debian
apt-get update
apt-get install -y awscli

# Verify installation
aws --version
```

### 3. Make Scripts Executable

```bash
chmod +x scripts/backup/*.sh
```

### 4. Configure Cron Jobs

Edit crontab for the deployment user:

```bash
crontab -e
```

Add these entries:

```cron
# Daily full database backup at 2:00 AM
0 2 * * * cd /var/www/workset && ./scripts/backup/backup-database.sh >> /var/log/workset-backup.log 2>&1

# Binary log backup every 6 hours
0 */6 * * * cd /var/www/workset && ./scripts/backup/backup-binlogs.sh >> /var/log/workset-binlog-backup.log 2>&1
```

### 5. Test Backups

Run manually to verify setup:

```bash
cd /var/www/workset

# Test database backup
./scripts/backup/backup-database.sh

# Test binlog backup
./scripts/backup/backup-binlogs.sh

# Verify files were created
ls -lh /var/backups/workset/database/
ls -lh /var/backups/workset/binlogs/
```

## Restore Procedures

### Full Database Restore

#### From Local Backup

```bash
cd /var/www/workset

# List available backups
ls -lh /var/backups/workset/database/

# Restore specific backup
./scripts/backup/restore-database.sh /var/backups/workset/database/workset_20250116_020000.sql.gz.enc
```

#### From DigitalOcean Spaces

```bash
cd /var/www/workset

# List available backups in Spaces
aws s3 ls s3://workset-backups/backups/database/ \
  --endpoint-url=https://sgp1.digitaloceanspaces.com

# Restore from Spaces
./scripts/backup/restore-database.sh --from-spaces workset_20250116_020000.sql.gz.enc
```

### Point-in-Time Recovery

For recovering to a specific point in time (e.g., just before an accidental deletion):

#### 1. Identify the Recovery Point

```bash
# Determine when the incident occurred
# Example: Accidental data deletion at 2025-01-16 14:30:00
TARGET_TIME="2025-01-16 14:30:00"
```

#### 2. Find the Appropriate Full Backup

```bash
# Find the most recent backup BEFORE the incident
ls -lh /var/backups/workset/database/ | grep "20250116"
# Use backup from 2:00 AM: workset_20250116_020000.sql.gz.enc
```

#### 3. Restore the Full Backup

```bash
./scripts/backup/restore-database.sh /var/backups/workset/database/workset_20250116_020000.sql.gz.enc
```

#### 4. Apply Binary Logs Up to Target Time

```bash
# Download and decrypt all binlogs from 2:00 AM to 14:30
cd /var/backups/workset/binlogs/

# Decompress binlogs
gunzip mysql-bin.*.20250116*.gz

# Apply binlogs up to target time
docker-compose exec -T mysql mysqlbinlog \
  --stop-datetime="$TARGET_TIME" \
  mysql-bin.000100.20250116_020000 \
  mysql-bin.000101.20250116_080000 \
  mysql-bin.000102.20250116_140000 \
  | docker-compose exec -T mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD}" workset
```

#### 5. Verify Recovery

```bash
# Check data is restored correctly
docker-compose exec app php artisan tinker
# Run queries to verify data state
```

### Emergency Restore (Production Down)

If production is completely down and needs immediate restore:

#### 1. Download Latest Backup

```bash
cd /var/www/workset

# Download from Spaces
aws s3 cp \
  s3://workset-backups/backups/database/workset_20250116_020000.sql.gz.enc \
  /var/backups/workset/restore/ \
  --endpoint-url=https://sgp1.digitaloceanspaces.com
```

#### 2. Stop Services

```bash
docker-compose down
```

#### 3. Start Only Database

```bash
docker-compose up -d mysql redis
```

#### 4. Restore Database

```bash
./scripts/backup/restore-database.sh /var/backups/workset/restore/workset_20250116_020000.sql.gz.enc
```

#### 5. Start All Services

```bash
docker-compose up -d
```

#### 6. Verify Health

```bash
# Check all services are running
docker-compose ps

# Test application
curl -f https://tracker.kneebone.com.au/health
```

## Manual Backup

To create an ad-hoc backup before a risky operation:

```bash
cd /var/www/workset

# Create manual backup with descriptive name
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T mysql mysqldump \
  --single-transaction \
  -u root -p"${MYSQL_ROOT_PASSWORD}" \
  workset | gzip > "/var/backups/workset/manual/manual_backup_${DATE}.sql.gz"

echo "Manual backup created: manual_backup_${DATE}.sql.gz"
```

## Decrypting Backups Manually

If you need to inspect a backup without restoring:

```bash
# Decrypt backup
openssl enc -aes-256-cbc -d -pbkdf2 \
  -in workset_20250116_020000.sql.gz.enc \
  -out workset_20250116_020000.sql.gz \
  -pass pass:"${BACKUP_ENCRYPTION_KEY}"

# Decompress
gunzip workset_20250116_020000.sql.gz

# Now you have a plain SQL file
head workset_20250116_020000.sql
```

## Monitoring Backups

### Check Last Backup Status

```bash
# Check last database backup
ls -lth /var/backups/workset/database/ | head -5

# Check backup logs
tail -50 /var/log/workset-backup.log

# Verify backup uploaded to Spaces
aws s3 ls s3://workset-backups/backups/database/ \
  --endpoint-url=https://sgp1.digitaloceanspaces.com | tail -5
```

### Set Up Backup Monitoring

Add monitoring checks to ensure backups are running:

```bash
# Create monitoring script
cat > /usr/local/bin/check-workset-backups.sh <<'EOF'
#!/bin/bash

BACKUP_DIR="/var/backups/workset/database"
MAX_AGE_HOURS=26  # Alert if no backup in 26 hours (daily backups)

LATEST_BACKUP=$(find "$BACKUP_DIR" -name "workset_*.sql.gz.enc" -type f -printf '%T@ %p\n' | sort -n | tail -1 | cut -d' ' -f2-)

if [ -z "$LATEST_BACKUP" ]; then
    echo "ERROR: No backups found in $BACKUP_DIR"
    exit 2
fi

BACKUP_AGE_SECONDS=$(( $(date +%s) - $(stat -c %Y "$LATEST_BACKUP") ))
BACKUP_AGE_HOURS=$(( BACKUP_AGE_SECONDS / 3600 ))

if [ $BACKUP_AGE_HOURS -gt $MAX_AGE_HOURS ]; then
    echo "WARNING: Latest backup is $BACKUP_AGE_HOURS hours old"
    exit 1
fi

echo "OK: Latest backup is $BACKUP_AGE_HOURS hours old"
exit 0
EOF

chmod +x /usr/local/bin/check-workset-backups.sh

# Test
/usr/local/bin/check-workset-backups.sh
```

## Disaster Recovery Scenarios

### Scenario 1: Accidental Table Drop

1. Identify when the drop occurred
2. Restore from the most recent backup before the drop
3. Apply binary logs up to just before the DROP statement
4. Verify data integrity

### Scenario 2: Corrupted Database

1. Stop application (enable maintenance mode)
2. Restore from latest known good backup
3. Restart services
4. Verify application functionality

### Scenario 3: Ransomware/Compromise

1. Isolate the server immediately
2. Provision a clean server
3. Restore from backup created before compromise
4. Change all credentials
5. Investigate compromise vector

### Scenario 4: Regional Outage

1. Spin up new infrastructure in different region
2. Download backups from DigitalOcean Spaces (multi-region)
3. Restore database
4. Update DNS
5. Verify functionality

## Security Considerations

1. **Encryption Keys**: Store `BACKUP_ENCRYPTION_KEY` securely (password manager, secrets management service)
2. **Access Control**: Limit access to backup files and scripts
3. **Testing**: Regularly test restore procedures (monthly)
4. **Off-site Storage**: DigitalOcean Spaces provides geographic redundancy
5. **Audit Logs**: Review backup logs regularly for failures

## Troubleshooting

### Backup Script Fails

```bash
# Check logs
tail -100 /var/log/workset-backup.log

# Common issues:
# - Disk space full
df -h /var/backups

# - MySQL connection issues
docker-compose exec mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SELECT 1"

# - Spaces upload issues (check credentials)
aws s3 ls s3://workset-backups/ --endpoint-url=https://sgp1.digitaloceanspaces.com
```

### Restore Fails

```bash
# Check encryption key
echo $BACKUP_ENCRYPTION_KEY

# Verify backup file integrity
file /var/backups/workset/database/workset_20250116_020000.sql.gz.enc

# Test decryption manually
openssl enc -aes-256-cbc -d -pbkdf2 \
  -in workset_20250116_020000.sql.gz.enc \
  -out test.sql.gz \
  -pass pass:"${BACKUP_ENCRYPTION_KEY}"
```

## Compliance & Retention

- **Production Backups**: 30 days (compliance requirement)
- **Binary Logs**: 7 days (point-in-time recovery window)
- **Pre-deployment Backups**: Kept until next deployment succeeds
- **Manual Backups**: Cleanup as needed

## Support

For backup/restore issues:
1. Check logs: `/var/log/workset-backup.log`
2. Review this documentation
3. Contact infrastructure team
4. Escalate to senior engineer if data loss risk exists
