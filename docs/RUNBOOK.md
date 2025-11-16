# Workset Operational Runbook

**Purpose:** Operational procedures for deploying, managing, and troubleshooting Workset in production.

**Audience:** DevOps engineers, SREs, on-call engineers

**Last Updated:** November 2025

---

## Table of Contents

1. [Emergency Procedures](#emergency-procedures)
2. [Standard Deployment](#standard-deployment)
3. [Database Operations](#database-operations)
4. [Monitoring & Alerts](#monitoring--alerts)
5. [Incident Response](#incident-response)
6. [Routine Maintenance](#routine-maintenance)
7. [Performance Tuning](#performance-tuning)
8. [Disaster Recovery](#disaster-recovery)

---

## Emergency Procedures

### ⚠️ Site Down - Critical

**Symptoms:** Site returns 502/503 errors, users cannot access application

**Immediate Actions:**

```bash
# 1. Check service status
ssh user@tracker.kneebone.com.au
cd /var/www/workset
docker-compose ps

# 2. Check for recent deployments
git log -5 --oneline
docker-compose logs --tail=100

# 3. Quick restart
docker-compose restart

# 4. If still down, enable maintenance and investigate
docker-compose exec app php artisan down --secret=emergency-access
```

**Investigation:**

```bash
# Check application logs
docker-compose logs app | tail -200

# Check database connectivity
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Check disk space
df -h

# Check memory
free -h

# Check running processes
docker stats
```

**Resolution Steps:**

1. **Application won't start**:
   ```bash
   # Check for syntax errors
   docker-compose exec app php artisan config:clear
   docker-compose exec app composer dump-autoload
   docker-compose restart app
   ```

2. **Database connectivity issues**:
   ```bash
   # Check MySQL status
   docker-compose ps mysql
   docker-compose restart mysql

   # Wait 30 seconds
   sleep 30

   # Restart app
   docker-compose restart app
   ```

3. **Out of memory**:
   ```bash
   # Clear caches
   docker-compose exec app php artisan optimize:clear
   docker-compose exec redis redis-cli FLUSHALL
   docker-compose restart
   ```

4. **Disk full**:
   ```bash
   # Clear logs
   docker-compose exec app php artisan log:clear

   # Remove old backups (keep last 7 days)
   find /var/backups/workset -mtime +7 -delete
   ```

**Escalation:**
- If not resolved in 15 minutes, page DevOps lead
- Post status update to #incidents Slack channel
- Update status page

---

### 🔥 Database Corruption

**Symptoms:** Data inconsistencies, foreign key errors, cannot write to database

**Immediate Actions:**

```bash
# 1. Enable maintenance mode immediately
docker-compose exec app php artisan down

# 2. Stop writes to database
docker-compose stop app horizon scheduler

# 3. Backup current state
./scripts/backup/backup-database.sh emergency

# 4. Check database integrity
docker-compose exec mysql mysqlcheck -u root -p workset --check --all-databases
```

**DO NOT:**
- Run migrations
- Attempt auto-repair without backup
- Delete data manually

**DO:**
- Contact DBA immediately
- Preserve all logs
- Document exact error messages
- Restore from backup if necessary

**Recovery:**

```bash
# Restore from last known good backup
./scripts/backup/restore-database.sh /var/backups/workset/daily/workset_20251116_0200.sql.gz

# Verify integrity
docker-compose exec mysql mysqlcheck -u root -p workset --all-databases

# Restart services
docker-compose start app horizon scheduler
docker-compose exec app php artisan up
```

---

### 🚨 Security Breach Detected

**Immediate Actions:**

1. **Isolate the system**:
   ```bash
   # Block all external traffic (requires firewall access)
   sudo ufw deny from any to any
   ```

2. **Preserve evidence**:
   ```bash
   # Create forensic backup
   tar -czf /tmp/forensics_$(date +%Y%m%d_%H%M%S).tar.gz \
     /var/www/workset/storage/logs \
     /var/log/nginx \
     /var/log/mysql
   ```

3. **Notify security team**:
   - Email: security@workset.kneebone.com.au
   - Slack: #security-incidents
   - Include: time, symptoms, affected systems

4. **Do NOT**:
   - Reboot servers (loses memory forensics)
   - Delete logs
   - "Fix" issues before investigation
   - Communicate externally until cleared

---

## Standard Deployment

### Pre-Deployment Checklist

**24 Hours Before:**

- [ ] Review changelog and breaking changes
- [ ] Check staging deployment was successful
- [ ] Verify all tests passing
- [ ] Review database migrations
- [ ] Notify team of deployment window
- [ ] Schedule post-deployment monitoring

**1 Hour Before:**

- [ ] Verify backup completed successfully
- [ ] Check system resources (disk, memory, CPU)
- [ ] Ensure on-call engineer available
- [ ] Review rollback procedure
- [ ] Prepare deployment commands

### Deployment Procedure

**Step 1: Pre-Deployment Backup**

```bash
# SSH to production
ssh user@tracker.kneebone.com.au
cd /var/www/workset

# Create pre-deployment backup
./scripts/backup/backup-database.sh pre-deployment

# Verify backup created
ls -lh /var/backups/workset/pre-deployment/
```

**Step 2: Enable Maintenance Mode**

```bash
# Enable maintenance with bypass secret
docker-compose exec app php artisan down \
  --retry=60 \
  --secret="$(openssl rand -hex 16)" \
  --render="errors::503"

# Save the secret for team access
echo "Maintenance secret: [secret-here]"

# Verify maintenance mode active
curl -I https://tracker.kneebone.com.au
# Should return 503 Service Unavailable
```

**Step 3: Pull Latest Code**

```bash
# Fetch latest changes
git fetch origin

# Checkout specific tag or branch
git checkout tags/v1.2.3  # or main

# Verify correct version
git log -1
```

**Step 4: Update Dependencies**

```bash
# Update PHP dependencies
docker-compose exec app composer install --no-dev --optimize-autoloader

# Update Node dependencies
docker-compose exec app npm ci --production

# Build production assets
docker-compose exec app npm run build
```

**Step 5: Run Migrations**

```bash
# Check migration status
docker-compose exec app php artisan migrate:status

# Run migrations
docker-compose exec app php artisan migrate --force

# Verify migrations completed
docker-compose exec app php artisan migrate:status
```

**Step 6: Clear and Rebuild Caches**

```bash
# Clear all caches
docker-compose exec app php artisan optimize:clear

# Rebuild optimised caches
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan event:cache
```

**Step 7: Restart Services**

```bash
# Restart application
docker-compose restart app

# Restart queue workers (graceful)
docker-compose exec app php artisan horizon:terminate

# Wait for Horizon to restart
sleep 10

# Verify Horizon running
docker-compose exec app php artisan horizon:status
```

**Step 8: Disable Maintenance Mode**

```bash
# Disable maintenance mode
docker-compose exec app php artisan up

# Verify site accessible
curl -I https://tracker.kneebone.com.au
# Should return 200 OK
```

**Step 9: Post-Deployment Verification**

```bash
# Run health checks
./scripts/deploy/health-check.sh production

# Check application logs for errors
docker-compose logs app --tail=100 | grep -i error

# Verify key functionality
# - Login
# - Create session
# - Complete session
# - View analytics
```

**Step 10: Monitor for 15 Minutes**

```bash
# Watch logs
docker-compose logs -f app

# Monitor queue
# Visit: https://tracker.kneebone.com.au/horizon

# Check error rates in monitoring dashboard
```

### Post-Deployment

- [ ] Update deployment log
- [ ] Notify team deployment completed
- [ ] Monitor error rates for 1 hour
- [ ] Update changelog/release notes
- [ ] Close deployment ticket

---

## Database Operations

### Creating Manual Backup

```bash
# On-demand backup
cd /var/www/workset
./scripts/backup/backup-database.sh manual

# Verify backup
ls -lh /var/backups/workset/manual/

# Test backup integrity
gunzip -t /var/backups/workset/manual/workset_*.sql.gz
```

### Restoring from Backup

```bash
# 1. Enable maintenance mode
docker-compose exec app php artisan down

# 2. Stop application
docker-compose stop app horizon scheduler

# 3. List available backups
ls -lh /var/backups/workset/daily/

# 4. Restore backup
./scripts/backup/restore-database.sh /var/backups/workset/daily/workset_20251116_0200.sql.gz

# 5. Verify restoration
docker-compose exec mysql mysql -u root -p workset -e "SELECT COUNT(*) FROM users;"

# 6. Restart services
docker-compose start app horizon scheduler
docker-compose exec app php artisan up
```

### Running Manual Migrations

```bash
# Check migration status
docker-compose exec app php artisan migrate:status

# Run specific migration
docker-compose exec app php artisan migrate --path=database/migrations/2025_11_17_create_new_table.php --force

# Rollback last migration (DANGEROUS - requires database restore)
# DO NOT RUN without backup
docker-compose exec app php artisan migrate:rollback --step=1
```

### Database Performance

```bash
# Check slow queries
docker-compose exec mysql mysql -u root -p -e "
  SELECT query_time, lock_time, rows_sent, rows_examined, sql_text
  FROM mysql.slow_log
  ORDER BY query_time DESC
  LIMIT 10;
"

# Analyse table
docker-compose exec mysql mysql -u root -p workset -e "ANALYZE TABLE training_sessions;"

# Optimise table
docker-compose exec mysql mysql -u root -p workset -e "OPTIMIZE TABLE training_sessions;"

# Check index usage
docker-compose exec app php artisan db:show
```

---

## Monitoring & Alerts

### Key Metrics to Monitor

**Application:**
- Response time (target: < 200ms)
- Error rate (target: < 0.1%)
- Request rate
- Queue length
- Failed jobs

**Infrastructure:**
- CPU usage (alert: > 80%)
- Memory usage (alert: > 85%)
- Disk usage (alert: > 80%)
- Network throughput

**Database:**
- Query time (alert: > 1s)
- Connection pool usage
- Slow queries
- Deadlocks

**Custom Metrics:**
- Active training sessions
- Completed sessions per hour
- User signups
- PRs achieved per day

### Checking Metrics

```bash
# Application metrics
docker-compose exec app php artisan horizon:status
docker-compose exec app php artisan queue:monitor

# System metrics
docker stats --no-stream

# Database metrics
docker-compose exec mysql mysqladmin -u root -p processlist
docker-compose exec mysql mysqladmin -u root -p status

# Disk usage
df -h

# Logs for errors
docker-compose logs app --since=1h | grep -i error | wc -l
```

### Alert Response

**High Error Rate:**

```bash
# 1. Check recent deployments
git log -5

# 2. Check error logs
docker-compose logs app --since=30m | grep ERROR

# 3. Check Sentry/error tracking
# Visit: https://sentry.io/workset

# 4. If deployment-related, rollback
git checkout [previous-tag]
docker-compose restart app
```

**High CPU:**

```bash
# 1. Check which service
docker stats

# 2. Check slow queries
docker-compose exec mysql mysqladmin -u root -p processlist

# 3. Check queue backlog
docker-compose exec app php artisan queue:monitor

# 4. Scale if needed (temporary)
docker-compose up --scale app=2 -d
```

**Disk Full:**

```bash
# 1. Find large files
du -sh /var/www/workset/* | sort -h

# 2. Clear old logs
find /var/www/workset/storage/logs -name "*.log" -mtime +7 -delete

# 3. Clear old backups
find /var/backups/workset -type f -mtime +30 -delete

# 4. Restart services
docker-compose restart
```

---

## Incident Response

### Severity Levels

**P0 - Critical:**
- Site completely down
- Data loss occurring
- Security breach

**Response:** Immediate, page on-call

**P1 - High:**
- Major functionality broken
- Significant performance degradation
- Affecting > 50% of users

**Response:** Within 15 minutes

**P2 - Medium:**
- Non-critical feature broken
- Affecting < 50% of users
- Workaround available

**Response:** Within 2 hours

**P3 - Low:**
- Minor bug
- Cosmetic issue
- Feature request

**Response:** Next business day

### Incident Response Procedure

1. **Acknowledge**
   - Update status page
   - Post to #incidents channel
   - Notify stakeholders

2. **Assess**
   - Determine severity
   - Identify scope of impact
   - Estimate time to resolution

3. **Respond**
   - Follow appropriate runbook
   - Keep stakeholders updated every 15-30 min
   - Document actions taken

4. **Resolve**
   - Implement fix
   - Verify resolution
   - Monitor for recurrence

5. **Post-Mortem** (for P0/P1)
   - Timeline of events
   - Root cause analysis
   - Action items to prevent recurrence
   - Share learnings with team

---

## Routine Maintenance

### Daily Tasks

**Automated:**
- Database backups (02:00 UTC)
- Log rotation
- SSL certificate check
- Health checks

**Manual Check:**
```bash
# Check backup completed
ls -lh /var/backups/workset/daily/

# Check for errors
docker-compose logs app --since=24h | grep -i error

# Monitor queue
# Visit: https://tracker.kneebone.com.au/horizon
```

### Weekly Tasks

**Every Monday 09:00:**

```bash
# Check disk usage
df -h

# Clear old sessions
docker-compose exec app php artisan session:clear

# Optimise database tables
docker-compose exec mysql mysqlcheck -u root -p workset --optimize --all-databases

# Review failed jobs
docker-compose exec app php artisan queue:failed

# Update dependencies (if security updates)
docker-compose exec app composer update --with-dependencies
```

### Monthly Tasks

**First Sunday of Month:**

```bash
# Test backup restore
./scripts/backup/test-restore.sh

# Review and delete old backups
find /var/backups/workset -type f -mtime +90 -delete

# Check SSL expiry
openssl s_client -connect tracker.kneebone.com.au:443 -servername tracker.kneebone.com.au 2>/dev/null | openssl x509 -noout -dates

# Review access logs for anomalies
docker-compose exec nginx tail -10000 /var/log/nginx/access.log | analyze-logs

# Update documentation
# Review and update runbooks, docs
```

---

## Performance Tuning

### Application Performance

**Enable Opcache:**

```bash
# Verify opcache enabled
docker-compose exec app php -i | grep opcache

# Clear opcache
docker-compose exec app php artisan opcache:clear
```

**Optimise Composer:**

```bash
# Generate optimised autoloader
docker-compose exec app composer dump-autoload --optimize --classmap-authoritative
```

**Cache Configuration:**

```bash
# Verify caches active
docker-compose exec app php artisan config:show cache
docker-compose exec app php artisan route:list --compact
```

### Database Performance

**Add Indexes:**

```bash
# Check missing indexes
docker-compose exec mysql mysql -u root -p workset -e "
  SELECT * FROM sys.schema_unused_indexes;
"

# Check duplicate indexes
docker-compose exec mysql mysql -u root -p workset -e "
  SELECT * FROM sys.schema_redundant_indexes;
"
```

**Query Optimisation:**

```bash
# Enable slow query log
docker-compose exec mysql mysql -u root -p -e "
  SET GLOBAL slow_query_log = 'ON';
  SET GLOBAL long_query_time = 1;
"

# Review slow queries daily
docker-compose exec mysql mysqldumpslow /var/log/mysql/slow-query.log
```

### Queue Performance

```bash
# Monitor queue performance
docker-compose exec app php artisan horizon:status

# Adjust workers in config/horizon.php
# Then restart
docker-compose exec app php artisan horizon:terminate
```

---

## Disaster Recovery

### Recovery Time Objectives (RTO)

- **RTO**: 4 hours (maximum downtime)
- **RPO**: 24 hours (maximum data loss)

### Full System Restore

**Scenario:** Complete server failure

**Prerequisites:**
- Backup server provisioned
- Database backup available
- Code repository accessible

**Procedure:**

```bash
# 1. Provision new server (via Infrastructure as Code)
terraform apply -var="environment=production-recovery"

# 2. SSH to new server
ssh user@new-server-ip

# 3. Clone repository
git clone https://github.com/ingoldsby/workset.git /var/www/workset
cd /var/www/workset

# 4. Copy .env from backup
scp backup-server:/backups/production.env .env

# 5. Install dependencies
docker-compose exec app composer install --no-dev
docker-compose exec app npm ci --production
docker-compose exec app npm run build

# 6. Restore database
./scripts/backup/restore-database.sh /path/to/latest-backup.sql.gz

# 7. Generate application key
docker-compose exec app php artisan key:generate

# 8. Run migrations (if needed)
docker-compose exec app php artisan migrate --force

# 9. Optimise application
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# 10. Start services
docker-compose up -d

# 11. Update DNS to point to new server
# (Time for DNS propagation: 5-60 minutes)

# 12. Verify functionality
./scripts/deploy/health-check.sh production
```

**Time Estimate:** 2-4 hours

---

## Contact Information

### On-Call Escalation

1. **Primary On-Call**: [Contact]
2. **Secondary On-Call**: [Contact]
3. **DevOps Lead**: [Contact]
4. **CTO**: [Contact]

### External Contacts

- **Hosting Provider**: [Contact]
- **DNS Provider**: [Contact]
- **SSL Certificate Provider**: [Contact]

---

## Appendices

### Useful Commands Reference

```bash
# Quick health check
./scripts/deploy/health-check.sh production

# Restart all services
docker-compose restart

# View all logs
docker-compose logs -f

# Clear all caches
docker-compose exec app php artisan optimize:clear

# Check queue
docker-compose exec app php artisan horizon:status

# Enable maintenance
docker-compose exec app php artisan down --secret=bypass

# Disable maintenance
docker-compose exec app php artisan up
```

### Log Locations

- Application: `storage/logs/laravel.log`
- Nginx: `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- MySQL: `/var/log/mysql/error.log`, `/var/log/mysql/slow-query.log`
- Horizon: `storage/logs/horizon.log`

---

**Document Version:** 1.0
**Last Reviewed:** November 2025
**Next Review:** February 2026

**Feedback:** devops@workset.kneebone.com.au
