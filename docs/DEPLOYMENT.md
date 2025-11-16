# Workset Deployment Guide

## Overview

Workset uses a fully automated CI/CD pipeline with GitHub Actions for building, testing, and deploying to staging and production environments.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         GitHub Repository                        │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 │ Push to main
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                      GitHub Actions CI/CD                        │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────┐   │
│  │ Build Docker │→ │ Push to GHCR │→ │ Deploy to Staging  │   │
│  │    Images    │  │  (Registry)  │  │   (Automatic)      │   │
│  └──────────────┘  └──────────────┘  └────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                                │
                                                ▼
                                    ┌───────────────────────┐
                                    │  Manual Approval for  │
                                    │   Production Deploy   │
                                    └───────────┬───────────┘
                                                │
                                                ▼
                                    ┌───────────────────────┐
                                    │ Production Deployment │
                                    │  (Manual Trigger)     │
                                    └───────────────────────┘
```

## Environments

### Staging
- **URL**: https://staging.tracker.kneebone.com.au
- **Auth**: HTTP Basic (jim/empirefitness)
- **Deploy**: Automatic on merge to `main`
- **Purpose**: Pre-production testing and validation

### Production
- **URL**: https://tracker.kneebone.com.au
- **Deploy**: Manual workflow dispatch
- **Purpose**: Live production environment

## Deployment Workflows

### Automatic Staging Deployment

**Trigger**: Push to `main` branch

**Steps**:
1. Build multi-arch Docker images (amd64/arm64)
2. Push images to GitHub Container Registry
3. SSH to staging server
4. Pull latest code and images
5. Run database migrations
6. Restart services
7. Clear caches
8. Rebuild Scout indexes
9. Health check verification

**Workflow File**: `.github/workflows/ci-cd.yml`

### Manual Production Deployment

**Trigger**: Manual workflow dispatch via GitHub Actions UI

**Steps**:
1. Select image tag to deploy (e.g., `main`, `main-abc1234`)
2. GitHub Actions approval gate (requires manual approval)
3. Pre-deployment database backup
4. Enable maintenance mode
5. Deploy code and images
6. Run migrations
7. Restart services
8. Cache optimization
9. Health verification
10. Disable maintenance mode

**Workflow File**: `.github/workflows/deploy-production.yml`

## Deploying to Production

### Via GitHub Actions UI

1. Go to **Actions** tab in GitHub repository
2. Select **"Deploy to Production"** workflow
3. Click **"Run workflow"**
4. Enter the Docker image tag (default: `main`)
5. Click **"Run workflow"** button
6. Wait for approval gate
7. Approve the deployment
8. Monitor progress in GitHub Actions logs

### Via GitHub CLI

```bash
# Install GitHub CLI
brew install gh  # macOS
# or
sudo apt install gh  # Ubuntu

# Authenticate
gh auth login

# Trigger production deployment
gh workflow run deploy-production.yml \
  -f image-tag=main

# Monitor deployment
gh run list --workflow=deploy-production.yml
gh run watch
```

## Required Secrets

Configure these secrets in GitHub repository settings (**Settings → Secrets and variables → Actions**):

### Staging Environment
```
STAGING_HOST          # Staging server IP/hostname
STAGING_USER          # SSH username
STAGING_SSH_KEY       # SSH private key
```

### Production Environment
```
PRODUCTION_HOST       # Production server IP/hostname
PRODUCTION_USER       # SSH username
PRODUCTION_SSH_KEY    # SSH private key
MAINTENANCE_SECRET    # Secret for accessing site during maintenance
MYSQL_ROOT_PASSWORD   # MySQL root password
```

### Shared Secrets
```
GITHUB_TOKEN          # Automatically provided by GitHub Actions
```

## Pre-deployment Checklist

Before deploying to production:

- [ ] Changes tested in staging environment
- [ ] Database migrations reviewed and tested
- [ ] Breaking changes documented
- [ ] Changelog updated
- [ ] Team notified of deployment window
- [ ] Backup strategy verified
- [ ] Rollback plan prepared

## Post-deployment Verification

After production deployment:

### 1. Automated Health Checks

The deployment workflow automatically runs health checks:
- Application responds (HTTP 200)
- Database connectivity
- Redis connectivity
- Horizon status
- Reverb (WebSockets) status
- Scheduler status

### 2. Manual Verification

```bash
# SSH to production server
ssh user@tracker.kneebone.com.au

# Run comprehensive health check
cd /var/www/workset
./scripts/deploy/health-check.sh production

# Check all services
docker-compose ps

# Check Horizon dashboard
# Visit: https://tracker.kneebone.com.au/horizon

# Check application logs
docker-compose logs -f --tail=100 app

# Check for errors
docker-compose logs app | grep -i error
```

### 3. Functional Testing

- [ ] Login functionality
- [ ] Create new session
- [ ] Log sets
- [ ] View analytics
- [ ] PT dashboard (if applicable)
- [ ] PWA installation (mobile)
- [ ] Offline functionality

## Rollback Procedures

### Quick Rollback (Redeploy Previous Version)

```bash
# Via GitHub Actions
gh workflow run deploy-production.yml \
  -f image-tag=main-<previous-commit-sha>

# Example:
gh workflow run deploy-production.yml \
  -f image-tag=main-abc1234
```

### Emergency Rollback (Database Restore Required)

If migrations caused issues:

```bash
# SSH to production
ssh user@tracker.kneebone.com.au
cd /var/www/workset

# Enable maintenance mode
docker-compose exec app php artisan down

# Restore pre-deployment backup
./scripts/backup/restore-database.sh \
  /var/backups/workset/pre-deployment/workset_pre_deploy_<timestamp>.sql.gz

# Rollback code
git checkout <previous-commit>
docker-compose down
docker-compose up -d

# Disable maintenance mode
docker-compose exec app php artisan up

# Verify
./scripts/deploy/health-check.sh production
```

## Maintenance Mode

### Enable Maintenance Mode

```bash
# With secret bypass
docker-compose exec app php artisan down \
  --retry=60 \
  --secret="your-maintenance-secret" \
  --render="errors::503"

# Access during maintenance:
# https://tracker.kneebone.com.au/your-maintenance-secret
```

### Disable Maintenance Mode

```bash
docker-compose exec app php artisan up
```

### Check Maintenance Status

```bash
docker-compose exec app php artisan tinker
>>> app()->isDownForMaintenance()
```

## Database Migrations

### Migration Strategy

Migrations run automatically during deployment:

```bash
docker-compose run --rm app php artisan migrate --force
```

### Testing Migrations

Always test migrations in staging first:

```bash
# Staging environment
ssh user@staging.tracker.kneebone.com.au
cd /var/www/workset

# Create backup before testing
./scripts/backup/backup-database.sh

# Run migrations
docker-compose exec app php artisan migrate

# Verify
docker-compose exec app php artisan migrate:status

# If issues, rollback
./scripts/backup/restore-database.sh <backup-file>
```

### Rollback Migrations

⚠️ **Warning**: This project does not use `down()` methods in migrations (per project guidelines).

To rollback:
1. Restore from pre-deployment backup
2. Redeploy previous code version

## Cache Management

### Clear All Caches

```bash
docker-compose exec app php artisan optimize:clear

# Or individually:
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan cache:clear
```

### Rebuild Caches

```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan event:cache
```

## Queue Management

### Restart Horizon

```bash
# Graceful termination (processes current jobs)
docker-compose exec app php artisan horizon:terminate

# Horizon will automatically restart via supervisor
```

### Clear Failed Jobs

```bash
# List failed jobs
docker-compose exec app php artisan queue:failed

# Retry all failed jobs
docker-compose exec app php artisan queue:retry all

# Clear failed jobs
docker-compose exec app php artisan queue:flush
```

### Monitor Queue

```bash
# Check queue size
docker-compose exec app php artisan queue:monitor redis:default,redis:notifications

# Watch Horizon dashboard
# https://tracker.kneebone.com.au/horizon
```

## Search Index Management

### Rebuild Scout Indexes

```bash
# Flush existing indexes
docker-compose exec app php artisan scout:flush "App\Models\Exercise"

# Import all records
docker-compose exec app php artisan scout:import "App\Models\Exercise"

# Check index status via Meilisearch
curl http://localhost:7700/indexes
```

## Monitoring & Logging

### Application Logs

```bash
# Follow application logs
docker-compose logs -f app

# Last 100 lines
docker-compose logs --tail=100 app

# Errors only
docker-compose logs app | grep -i error
```

### Access Logs

```bash
# Nginx access logs
docker-compose logs nginx | grep "GET\|POST"

# Filter by status code
docker-compose logs nginx | grep " 500 "
```

### Database Logs

```bash
# MySQL logs
docker-compose logs mysql

# Slow query log (if enabled)
docker-compose exec mysql tail -f /var/log/mysql/slow-query.log
```

## SSL Certificate Renewal

Certificates are managed by Certbot (auto-renewal enabled):

```bash
# Check certificate expiry
docker-compose exec nginx openssl x509 -in /etc/ssl/certs/tracker.kneebone.com.au.crt -noout -dates

# Manual renewal (if needed)
certbot renew --nginx

# Test renewal
certbot renew --dry-run
```

## Scaling

### Horizontal Scaling (Multiple Servers)

For load balancing across multiple app servers:

1. Set up load balancer (nginx/HAProxy)
2. Deploy to multiple servers using same workflow
3. Ensure shared Redis/MySQL instances
4. Configure session driver to use Redis
5. Use centralized file storage (S3) for uploads

### Vertical Scaling (Resource Limits)

Adjust Docker Compose resource limits:

```yaml
# docker-compose.yml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '1.0'
          memory: 1G
```

## Troubleshooting Deployments

### Deployment Fails at Migration Step

```bash
# SSH to server
ssh user@tracker.kneebone.com.au

# Check migration status
docker-compose exec app php artisan migrate:status

# Check for migration errors
docker-compose logs app | grep -i migration

# Fix: Restore database and investigate migration
./scripts/backup/restore-database.sh <backup-file>
```

### Services Won't Start After Deployment

```bash
# Check service status
docker-compose ps

# Check logs for errors
docker-compose logs

# Restart specific service
docker-compose restart app

# Full restart
docker-compose down && docker-compose up -d
```

### Application Returns 500 Errors

```bash
# Check application logs
docker-compose logs app | tail -100

# Check Laravel log files
docker-compose exec app tail -100 storage/logs/laravel.log

# Common causes:
# - Cache issues (clear with: php artisan optimize:clear)
# - Permission issues (check storage/ permissions)
# - Environment variables (.env file)
```

### Health Checks Fail

```bash
# Run comprehensive health check
./scripts/deploy/health-check.sh production

# Check specific service
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
>>> Redis::ping();
```

## Emergency Contacts

- **DevOps Lead**: [Contact info]
- **Database Administrator**: [Contact info]
- **On-call Engineer**: [Contact info]

## Additional Resources

- [Backup & Restore Documentation](BACKUP_RESTORE.md)
- [Server Setup Guide](SERVER_SETUP.md)
- [Monitoring Guide](MONITORING.md)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
