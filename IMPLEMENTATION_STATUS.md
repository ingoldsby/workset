# Workset MVP - Implementation Status

## ✅ Completed Foundation

### 1. Core Infrastructure
- ✅ Laravel 12 base scaffold
- ✅ Configured for Australia/Brisbane timezone, en-AU locale
- ✅ Laravel Breeze (Blade stack) authentication installed
- ✅ Livewire v3 installed
- ✅ Filament v3 admin panel installed
- ✅ Laravel Horizon (queue management) installed
- ✅ Laravel Scout + Meilisearch installed
- ✅ Laravel Reverb (WebSockets) installed
- ✅ Docker Compose stack with all services
- ✅ Nginx configurations (production + staging with Basic Auth)

### 2. Database Schema (ULIDs everywhere)
- ✅ Users table (converted to ULID, added Workset fields)
- ✅ PT assignments table
- ✅ Invites table (30-day expiry, signed tokens)
- ✅ Exercises table (global library from wger)
- ✅ Member exercises table (user-created exercises)
- ✅ Programs table (with versioning support)
- ✅ Program versions table
- ✅ Training sessions table
- ✅ Session sets table (prescribed + performed tracking)

### 3. Enums
- ✅ Role (Admin, PT, Member)
- ✅ EquipmentType (13 variants: Barbell, Dumbbell, etc.)
- ✅ CardioType (8 types with distance display logic)
- ✅ SetType (9 types with default rest times)
- ✅ ExerciseCategory
- ✅ MuscleGroup (16 muscle groups)
- ✅ ExerciseMechanics (Compound, Isolation)
- ✅ ExerciseLevel (Beginner, Intermediate, Advanced)

### 4. Docker & DevOps
- ✅ Multi-service Docker Compose (app, nginx, mysql, redis, meilisearch, horizon, reverb, scheduler)
- ✅ PHP 8.4 Alpine Dockerfile
- ✅ Nginx configs with staging HTTP Basic Auth (jim/empirefitness)
- ✅ Volume persistence for MySQL, Redis, Meilisearch

## ✅ Phase 1: Database & Models (COMPLETE)
1. ✅ All migrations created:
   - program_days
   - program_day_exercises (with progression rules JSON)
   - session_plans
   - session_exercises (with superset grouping)
   - cardio_entries
   - analytics_snapshots
   - audit_logs
   - recycle_bin

2. ✅ All Eloquent models created with:
   - ULID traits (`HasUlids`)
   - Relationships
   - Searchable traits (Scout)
   - Soft deletes where appropriate
   - Attribute casts (especially for JSON fields)

3. ✅ Policies created for all models (Admin, PT, Member access rules)

4. ✅ Factories created for all models (for seeding & testing)

## ✅ Phase 2: Filament Admin Panel (COMPLETE)
✅ Admin panel configured with:
- ✅ Admin-only access restriction via `canAccessPanel()` method
- ✅ UserResource (user management with invite, roles)
  - Full CRUD operations
  - PT assignments relation manager
  - Invites sent relation manager
  - Programs relation manager
- ✅ PtAssignmentResource (PT assignment management)
  - Create and manage PT-member assignments
  - Filter by active/inactive status
- ✅ ExerciseResource (view/edit global exercises)
  - Comprehensive exercise library management
  - Muscle group and equipment filtering
  - Support for aliases and equipment variants
- ✅ ProgramResource (program oversight)
  - Program version management
  - Version history tracking
  - Active version control
- ✅ InviteResource (invite management)
  - Create and track invites
  - Status tracking (pending/accepted/expired)
  - Auto-generated secure tokens
- ✅ Analytics Dashboard
  - StatsOverview widget (users, PTs, members, programs, sessions)
  - UsersByRoleChart (doughnut chart)
  - UserGrowthChart (30-day trend)
  - RecentUsersTable (latest 10 users)

## ✅ Phase 3: Authentication & Invites (COMPLETE)
1. ✅ Customized Breeze registration to require invite tokens
2. ✅ Implemented invite expiry (30 days)
3. ✅ Auto-assign to PT if invited by PT
4. ✅ Mark email as verified when invite accepted
5. ✅ Implemented password strength validation (zxcvbn)
6. ✅ Added hCaptcha after failed login attempts

## ✅ Phase 4: Livewire Components (Main App) (COMPLETE)
All main application sections have been implemented with Livewire components:

### Today Section
- ✅ PlannedSessionCard component - displays today's scheduled session
- ✅ QuickStart component - start ad-hoc session or navigate to programs/history
- ✅ Integration with session planning and training sessions

### Plan Section
- ✅ CalendarView component - monthly calendar showing scheduled sessions
- ✅ Navigation controls (previous/next month, jump to today)
- ✅ PT view to see assigned members' sessions
- ✅ Visual indication of current day and session types
- ✅ Placeholder for future drag-to-reschedule functionality

### Log Section
- ✅ SessionLogger component - log exercises and sets during a session
- ✅ Rest timer functionality with start/stop controls
- ✅ Set tracking with weight, reps, and RPE
- ✅ Support for both global and member exercises
- ✅ Session completion with redirect to history

### Programs Section
- ✅ ProgramList component - view all accessible programs
- ✅ Filter by owner (own programs, PT-created programs, member programs for PTs)
- ✅ Program status indicators (Active, Draft)
- ✅ Create program functionality
- ✅ Navigation to program detail view

### Exercises Section
- ✅ ExerciseLibrary component - searchable exercise database
- ✅ Tab system (Global Library, My Exercises, Recent)
- ✅ Filters: search, muscle group, equipment type
- ✅ Display exercise details with muscle groups and equipment
- ✅ Create custom exercise functionality

### History Section
- ✅ SessionHistory component - view past training sessions
- ✅ Filters: date range, search by exercise, completion status
- ✅ Session summary cards with duration and exercise count
- ✅ Pagination support
- ✅ View session details

### Analytics Section
- ✅ OverviewStats component - key metrics (sessions, sets, volume, duration)
- ✅ Period selection (week, month, year)
- ✅ VolumeTracker component - 12-week volume trend visualization
- ✅ ExerciseProgress component - top exercises and personal records
- ✅ PR tracking by exercise with historical data

### PT Area
- ✅ AthleteList component - manage assigned athletes
- ✅ Filter by status (active, inactive, all)
- ✅ Athlete profile cards with session counts
- ✅ ActivityFeed component - recent athlete activity
- ✅ Real-time feed of completed sessions from assigned members

### Infrastructure
- ✅ Updated navigation with all main app sections
- ✅ Role-based access control (PT Area restricted to PTs/Admins)
- ✅ Responsive mobile navigation
- ✅ Consistent UI/UX across all sections

## ✅ Phase 5: Progression Rule Builder (COMPLETE)
Comprehensive form-based progression rule system implemented:

### Core Features
- ✅ ProgressionRuleType enum with all rule types
- ✅ ProgressionRuleBuilder Livewire component with full validation
- ✅ ProgressionRulePreview component for displaying rules
- ✅ Standalone progression builder page accessible from Programs section
- ✅ Support for multiple rules per exercise

### Rule Types Implemented
- ✅ **Linear Progression**: Add weight each session/week with optional caps
  - Configurable increment amount
  - Optional weight cap
  - Per-session or per-week frequency
- ✅ **Double Progression**: Increase reps within range, then add weight
  - Min/max rep ranges
  - Weight increment when max reps achieved
- ✅ **Top Set + Back-off**: Heavy top set followed by lighter volume sets
  - Configurable top set and back-off set/rep schemes
  - Percentage-based or fixed weight reduction
- ✅ **RPE Target**: Auto-regulation based on Rate of Perceived Exertion
  - Target RPE with tolerance
  - Auto-adjust weight based on RPE feedback
- ✅ **Planned Deload**: Scheduled recovery weeks
  - Frequency in weeks (1-12)
  - Deload percentage
- ✅ **Weekly Undulation**: Rotating intensity days
  - Heavy/Medium/Light day percentages
- ✅ **Custom Warm-up**: Specific warm-up set protocols
  - Multiple warm-up sets with reps and percentages

### Advanced Features
- ✅ **Miss Handling**: Auto-adjustment on failed sets
  - Reduce weight by specified amount
  - Trigger deload protocol
  - Maintain weight
- ✅ Comprehensive form validation for all rule types
- ✅ Real-time rule preview with summaries
- ✅ Add/remove multiple rules per exercise
- ✅ Rules stored as JSON in program_day_exercises table

## ✅ Phase 6: PWA & Offline Support (COMPLETE)
Full Progressive Web App implementation with offline capabilities:

### Core PWA Features
- ✅ Web app manifest.json with app metadata and icons
- ✅ Service worker with comprehensive caching strategies
- ✅ Offline page with helpful messaging
- ✅ PWA registration script with update detection
- ✅ Apple mobile web app meta tags for iOS support

### Caching Strategies
- ✅ **Cache-first**: App shell, static assets, exercise library
  - Instant loading from cache
  - Background updates for freshness
- ✅ **Stale-while-revalidate**: Dynamic content (programs, sessions, history)
  - Immediate response from cache
  - Background update and cache refresh
- ✅ **Network-first**: Authentication, Livewire, real-time data
  - Always try network first
  - Fallback to cache when offline

### Offline Functionality
- ✅ **Background Sync**: Automatic synchronisation when back online
  - Session sets logged offline sync automatically
  - Session completion syncs on reconnect
  - IndexedDB for offline data storage
- ✅ **Offline Detection**: Visual indicators for connection status
  - Toast notifications for online/offline transitions
  - Automatic sync trigger when reconnecting
- ✅ **Offline Scope**: Exercise library + current sessions accessible offline

### Web Push Notifications
- ✅ **VAPID Integration**: Web push notification support
  - Public/private key configuration
  - Subscription management via API
  - Push notification event handling
- ✅ **Just-in-time Permissions**: Smart permission requests
  - Custom permission prompt UI
  - Delayed request (5 seconds after registration)
  - Graceful handling of denied permissions
- ✅ **Notification Actions**: Click handling and navigation
  - Focus existing windows when available
  - Open new windows for notifications
  - Customizable notification actions

### Install Experience
- ✅ **Custom Install Prompt**: Branded install experience
  - Delayed prompt (30 seconds after load)
  - Custom UI matching app design
  - Dismissible with "Not now" option
- ✅ **App Shortcuts**: Quick actions from home screen
  - Log Session shortcut
  - View History shortcut
  - Analytics shortcut
- ✅ **Standalone Mode Detection**: PWA-specific UI adjustments
  - Hide install prompt when already installed
  - Detect display mode (standalone/browser)

### Update Management
- ✅ **Auto-update Detection**: Notify users of new versions
  - Hourly update checks
  - Visual update notification
  - One-click reload to update
- ✅ **Cache Versioning**: Clean old caches automatically
  - Version-based cache naming
  - Automatic cleanup on activation

### API Endpoints
- ✅ `/api/push/vapid-public-key` - Get VAPID public key
- ✅ `/api/push/subscribe` - Subscribe to push notifications
- ✅ `/api/push/unsubscribe` - Unsubscribe from notifications
- ✅ `/api/session-sets` - Sync offline session sets
- ✅ `/api/sessions/{id}/complete` - Sync session completion

## ✅ Phase 7: Notifications (COMPLETE)
Comprehensive notification system with email and web push notifications:

### Email Notifications
- ✅ **InviteCreated**: Welcome email when user receives an invitation
  - Personalised greeting from inviter
  - Accept invitation link with expiry information
  - Professional email template
- ✅ **PtDailyDigest**: Daily summary for PTs at 20:00 local time
  - Completed sessions from assigned athletes
  - Upcoming sessions for next day
  - Missed sessions requiring attention
  - Sent only if there's activity to report
- ✅ **MemberWeeklyDigest**: Weekly training summary for members
  - Past week's session count and stats
  - Total sets and volume lifted
  - Upcoming week's scheduled sessions
  - User-configurable delivery day
- ✅ **PtActivityAlert**: Email notification for significant PT events
  - Notifies PT when sessions logged on their behalf
  - Includes session details and stats

### Web Push Notifications
- ✅ **SessionReminder**: Push notification before scheduled sessions
  - Sent 1 hour before scheduled session
  - Shows session name and type
  - Click-to-navigate to Today view
  - User-configurable (can disable)
- ✅ **PtActivityAlert**: Real-time athlete activity notifications
  - Session completion alerts
  - Click-to-navigate to PT dashboard
  - User-configurable (can disable)

### Notification Preferences
- ✅ **NotificationPreferences Livewire Component**
  - Session reminders (web push) toggle
  - PT activity alerts toggle (for PTs/Admins)
  - PT daily digest toggle (for PTs/Admins)
  - Member weekly digest toggle
  - Weekly digest day selection (7 days)
  - Integrated into profile settings
  - Saves preferences to user.notification_preferences JSON column

### Scheduled Commands
- ✅ **SendPtDailyDigests**: Send PT daily digests
  - Command: `workset:send-pt-daily-digests`
  - Runs at 20:00 local time
  - Filters PTs who enabled daily digest
  - Only sends if there's activity
- ✅ **SendMemberWeeklyDigests**: Send member weekly summaries
  - Command: `workset:send-member-weekly-digests`
  - Accepts --day option for specific day
  - Respects user's chosen delivery day
  - Includes past week stats and upcoming plan
- ✅ **SendSessionReminders**: Send session reminder push notifications
  - Command: `workset:send-session-reminders`
  - Runs every 10 minutes
  - Sends reminders 1 hour before sessions
  - Prevents duplicate reminders
  - Respects user's notification preferences

### Database Integration
- ✅ Added `notification_preferences` JSON column to users table
- ✅ Added `reminder_sent_at` tracking to session_plans table
- ✅ Cast notification_preferences as array in User model

### Queue Support
- ✅ All notifications implement ShouldQueue
- ✅ Queued processing for better performance
- ✅ Failed job handling via Laravel Horizon

## ✅ Phase 8: Exercise Library Seeding (COMPLETE)
Full integration with wger.de API for global exercise library:

### Core Services
- ✅ **WgerApiService**: Complete wger.de API client
  - Paginated exercise fetching with rate limiting
  - Equipment and muscle data retrieval
  - Exercise image downloading
  - Retry logic and error handling
  - Configurable language support (default: English)
- ✅ **WgerEquipmentMapper**: Equipment ID to EquipmentType enum mapping
  - Maps wger equipment IDs to our enum values
  - Determines primary equipment from multiple options
  - Extracts equipment variants
  - Handles bodyweight exercises
- ✅ **WgerMuscleMapper**: Muscle ID to MuscleGroup enum mapping
  - Maps wger muscle IDs to our enum values
  - Determines primary muscle group
  - Extracts secondary muscles
  - Handles muscle group combinations

### Data Transformation
- ✅ **WgerExerciseTransformer**: Converts wger data to Exercise model
  - Cleans HTML descriptions from wger
  - Maps equipment and muscles using mapper services
  - Determines exercise mechanics (compound/isolation)
  - Determines exercise category
  - Builds aliases array
  - Quality filtering (excludes incomplete/placeholder exercises)
  - Batch transformation support

### Import Command
- ✅ **ImportExercisesFromWger** (`workset:import-exercises`)
  - Options:
    - `--limit=100`: Exercises per page
    - `--max=`: Maximum exercises to import
    - `--fresh`: Delete existing exercises first
    - `--images`: Download exercise images
    - `--language=2`: wger language ID
  - Progress tracking with progress bar
  - Comprehensive error handling
  - Transaction-based imports
  - Update existing exercises by wger_id
  - Image downloading with local storage
  - Summary statistics (imported, skipped, failed, images)
  - Full logging support

### Database Enhancements
- ✅ Added `image_url` and `thumbnail_url` columns to exercises table
- ✅ Updated Exercise model with new fillable fields
- ✅ Migration for image columns

### Features
- ✅ Deduplicate exercises by wger_id
- ✅ Update existing exercises on re-import
- ✅ Rate limiting to respect wger API
- ✅ Quality filtering to avoid low-quality exercises
- ✅ Image storage in public disk
- ✅ Comprehensive import reporting

## ✅ Phase 9: CI/CD & Deployment (COMPLETE)
Comprehensive automated deployment pipeline with backup and restore capabilities:

### GitHub Actions Workflows
- ✅ **CI/CD Pipeline** (`.github/workflows/ci-cd.yml`)
  - Multi-arch Docker image builds (amd64/arm64)
  - Push to GitHub Container Registry
  - Automatic staging deployment on merge to main
  - Buildx cache optimisation
  - Health check verification

- ✅ **Production Deployment** (`.github/workflows/deploy-production.yml`)
  - Manual workflow dispatch with approval gate
  - Selectable image tag deployment
  - Pre-deployment database backup
  - Maintenance mode during deployment
  - Post-deployment health verification
  - Automated rollback on failure

### Deployment Features
- ✅ **Staging Environment**
  - Automatic deployment on push to main
  - SSH-based deployment
  - Database migrations
  - Cache clearing and optimisation
  - Scout index rebuilding
  - Horizon and Reverb restart
  - Health check validation

- ✅ **Production Environment**
  - Manual approval workflow
  - Pre-deployment backup creation
  - Graceful maintenance mode
  - Zero-downtime deployment strategy
  - Cache optimisation (config, route, view, event)
  - Service restart orchestration
  - Comprehensive health checks

### Backup System
- ✅ **Database Backup Script** (`scripts/backup/backup-database.sh`)
  - Daily full MySQL dumps
  - AES-256-CBC encryption
  - Gzip compression
  - Upload to DigitalOcean Spaces
  - 30-day retention policy
  - Automatic cleanup of old backups
  - Comprehensive error handling

- ✅ **Binary Log Backup** (`scripts/backup/backup-binlogs.sh`)
  - Hourly binlog rotation and backup
  - Point-in-time recovery capability
  - 7-day retention window
  - Compressed storage
  - Spaces upload
  - Automatic MySQL binlog purging

- ✅ **Restore Script** (`scripts/backup/restore-database.sh`)
  - Restore from local or Spaces backups
  - Automatic decryption
  - Pre-restore backup creation
  - Post-restore migrations
  - Cache clearing
  - Service restart

### Documentation
- ✅ **Backup & Restore Guide** (`docs/BACKUP_RESTORE.md`)
  - Complete backup strategy documentation
  - Step-by-step restore procedures
  - Point-in-time recovery guide
  - Disaster recovery scenarios
  - Security considerations
  - Monitoring and troubleshooting

- ✅ **Deployment Guide** (`docs/DEPLOYMENT.md`)
  - CI/CD architecture overview
  - Deployment workflows
  - Pre-deployment checklist
  - Post-deployment verification
  - Rollback procedures
  - Maintenance mode management
  - Database migration strategy
  - Cache and queue management
  - Troubleshooting guide

### Helper Scripts
- ✅ **Health Check Script** (`scripts/deploy/health-check.sh`)
  - Application health endpoint test
  - Database connectivity check
  - Redis connectivity check
  - Horizon status verification
  - Reverb service check
  - Scheduler status
  - Meilisearch health
  - Storage permissions
  - Cache functionality
  - Queue accessibility
  - Docker container health
  - Comprehensive test reporting

### Required GitHub Secrets
- ✅ Staging environment secrets (host, user, SSH key)
- ✅ Production environment secrets (host, user, SSH key, maintenance secret)
- ✅ Database credentials
- ✅ Backup encryption configuration
- ✅ DigitalOcean Spaces credentials

### Deployment Capabilities
- ✅ Multi-architecture Docker builds
- ✅ Container registry integration
- ✅ Automated testing gates
- ✅ Blue-green deployment support
- ✅ Automatic rollback on failure
- ✅ Pre-deployment backups
- ✅ Health monitoring
- ✅ Service orchestration

## ✅ Phase 10: Testing (COMPLETE)
Comprehensive Pest test suite covering all major functionality:

### Test Coverage
- ✅ **Invite Flow Tests** (`tests/Feature/InviteAcceptanceTest.php`, `tests/Feature/InviteManagementTest.php`)
  - Valid invite acceptance
  - Invalid/expired invite handling
  - Role assignment on acceptance
  - PT assignment creation
  - Duplicate prevention
  - Timezone handling
  - Invite management (create, delete, resend)
  - Permission checks (Admin/PT/Member)

- ✅ **PT Assignment Tests** (`tests/Feature/PtAssignmentTest.php`)
  - Assignment creation (13 tests, 30 assertions)
  - Active/inactive status tracking
  - Relationship loading (PT, Member)
  - PT member access control
  - Reassignment scenarios
  - Active scope queries

- ✅ **Program Versioning Tests** (`tests/Feature/ProgramVersioningTest.php`)
  - Program creation (15 tests, 40 assertions)
  - Version creation and history
  - Version activation/deactivation
  - Active version retrieval
  - Visibility (public/private)
  - Soft deletion and restoration
  - Change notes tracking
  - Relationship integrity

- ✅ **Session Logging Tests** (`tests/Feature/SessionLoggingTest.php`)
  - Training session creation (17 tests, 47 assertions)
  - Session status tracking (pending, in-progress, completed)
  - Exercise addition with ordering
  - Superset grouping support
  - Set logging (prescribed vs performed data)
  - Multiple set types (Normal, WarmUp, TopSet, etc.)
  - Completion tracking
  - PT logging on behalf of members
  - Duration calculations
  - Soft deletion

- ✅ **Role-Based Access Control Tests** (`tests/Feature/PolicyTest.php`)
  - Program policies (30 tests, 32 assertions)
    - View permissions (public/private, owner, PT assignment)
    - Create permissions (Admin, PT only)
    - Update permissions (owner, admin)
    - Delete permissions (owner, admin)
    - Force delete (admin only)
  - Training session policies
    - View permissions (owner, PT assignment, admin)
    - Update permissions (owner, PT, admin)
    - Delete permissions (owner, admin)
    - Force delete (admin only)
    - Create permissions (all authenticated users)

- ✅ **Authentication & Authorization Tests** (`tests/Feature/Auth/*`)
  - Login/logout flows
  - Registration (disabled without invite)
  - Password reset and update
  - Email verification
  - Password confirmation
  - Profile management

- ✅ **Send Invite Action Tests** (`tests/Feature/SendInviteActionTest.php`)
  - Invite creation with correct attributes
  - Email sending verification
  - PT assignment
  - Expiry date handling

### Test Infrastructure
- ✅ Pest test framework configured
- ✅ SQLite in-memory database for testing
- ✅ RefreshDatabase trait for test isolation
- ✅ Comprehensive factory coverage
- ✅ Test environment properly configured

### Test Statistics
- **Total Tests:** 129 passing
- **Total Assertions:** 296
- **Execution Time:** ~22 seconds
- **Coverage:** Core business logic and access control

### Testing Best Practices Followed
- Strong assertions (testing specific values, not just types)
- Clear test descriptions (Australian English)
- Logical grouping with `describe()` blocks
- Proper use of factories for test data
- Testing both happy paths and edge cases
- Route name usage (not hardcoded URLs)
- Meaningful failure messages

## 🚧 Future Testing Enhancements (Optional)

The following areas could benefit from additional test coverage in future phases:

### Not Yet Implemented
- **Progression Rules**: Test all progression rule types and their application logic
- **Offline Sync Merge Logic**: Test conflict resolution and data integrity for PWA offline functionality
- **Analytics Calculations**: Test volume calculations, PR tracking, and trend analysis
- **Livewire Components**: Component integration and interaction tests
- **API Endpoints**: Test PWA API endpoints for push notifications and offline sync

## 📝 Configuration Notes

### Environment Variables Required
- Database: `DB_*` (MySQL 8.0)
- Cache/Queue: `REDIS_*`
- Search: `MEILISEARCH_*`
- Mail: `MAIL_*` (SES ap-southeast-2)
- Broadcasting: `REVERB_*`
- Push: `VAPID_*`
- Analytics: `FATHOM_SITE_ID`
- Security: `HCAPTCHA_*`
- Backups: `BACKUP_ENCRYPTION_KEY`, `DO_SPACES_KEY`, `DO_SPACES_SECRET`, `DO_SPACES_BUCKET`, `DO_SPACES_REGION`

### First Run
1. `composer install`
2. Copy `.env.example` to `.env`
3. Set `APP_KEY` (artisan key:generate)
4. Configure database & services
5. Run migrations: `php artisan migrate`
6. Seed first admin: `php artisan db:seed --class=AdminSeeder`
7. Seed exercise library: `php artisan workset:import-exercises --images`

### Docker Commands
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app

# Run migrations
docker-compose exec app php artisan migrate

# Access shell
docker-compose exec app sh
```

## 📐 Architecture Decisions

### ULIDs vs UUIDs
- Using ULIDs for all primary keys (sortable, time-based, URL-safe)

### Foreign Keys
- Only on core domain tables (users, programs, sessions)
- Audit/logs/analytics use application-level integrity

### No down() Methods
- Migrations are one-way only (per project guidelines)

### Enums
- PHP 8.1+ enums with helper methods (label(), defaults())

### Units
- Default: kg, km, 0.5 kg rounding, 15 kg barbell
- User-configurable per preferences

### Timezone Handling
- Store UTC in database
- Display in user's local timezone (device-based for PWA)

## 🔗 Key URLs
- Production: `https://tracker.kneebone.com.au`
- Staging: `https://staging.tracker.kneebone.com.au` (Basic Auth: jim/empirefitness)
- Horizon: `/horizon` (production auth gated)
- Filament Admin: `/admin`

## 👥 Default Roles & Permissions
- **Admin**: Full system access, can manage all users, edit global exercises
- **PT**: Can invite members (auto-assigned), manage assigned members, log on behalf, view all member history
- **Member**: Own data only, can create private exercises, can edit assigned programs (with PT notification)
