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

## 🚧 Next Steps (Not Yet Implemented)

### Phase 3: Authentication & Invites
1. Customize Breeze registration to require invite tokens
2. Implement invite expiry (30 days)
3. Auto-assign to PT if invited by PT
4. Mark email as verified when invite accepted
5. Implement password strength validation (zxcvbn)
6. Add hCaptcha after failed login attempts

### Phase 4: Livewire Components (Main App)
Create components for:
- **Today**: planned session card, start session, start ad-hoc
- **Plan**: calendar view, drag-to-reschedule (PT only)
- **Log**: session logger with superset support, rest timers
- **Programs**: hybrid builder (weeks → days → exercises)
- **Exercises**: picker with recents/favourites, muscle/equipment filters
- **History**: filters, PR tracking, text search
- **Analytics**: per-exercise charts, volume tracking, weekly/monthly summaries
- **PT Area**: athlete list, planner, program library, activity feed

### Phase 5: Progression Rule Builder
Form-based builder for:
- Linear progression (with caps)
- Double progression
- Top-set + back-off (% or kg)
- RPE targets with tolerance
- Miss handling (auto-reduce/deload)
- Planned deloads (every N weeks by Z%)
- Weekly undulation (H/M/L)
- Per-exercise custom warm-ups

### Phase 6: PWA & Offline Support
1. Create service worker with:
   - Cache-first for shell + exercise library
   - Stale-while-revalidate for lists
   - Background sync for set saves/completions
   - Smart merge on conflicts

2. Create manifest.json
3. Implement Web Push (VAPID)
4. Just-in-time permission requests
5. Offline scope: exercise library + current week + last 30 days

### Phase 7: Notifications
1. Email notifications (SES):
   - Invite emails
   - PT daily digest (20:00 local)
   - Member weekly digest (user-selected)
   - PT logs on behalf notice

2. Web Push notifications:
   - Session reminders (user-configurable)
   - PT activity alerts

### Phase 8: Exercise Library Seeding
1. Create wger API integration
2. One-time snapshot import (en-AU preferred)
3. Map equipment variants
4. Store wger_id for reference
5. Admin-only editing of global exercises

### Phase 9: CI/CD & Deployment
1. GitHub Actions workflow:
   - Build multi-arch Docker images
   - Deploy to staging on merge to main
   - Manual promotion to production
   - Run migrations
   - Restart Horizon
   - Clear caches
   - Rebuild Scout indexes

2. Backup scripts:
   - Daily encrypted MySQL dumps → DO Spaces (30-day retention)
   - MySQL binlog backup (7 days)
   - Restore documentation

### Phase 10: Testing
Write Pest tests for:
- Invite flow
- PT assignment logic
- Program versioning
- Session logging
- Progression rules
- Offline sync merge logic
- Role-based access control
- Analytics calculations

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

### First Run
1. `composer install`
2. Copy `.env.example` to `.env`
3. Set `APP_KEY` (artisan key:generate)
4. Configure database & services
5. Run migrations: `php artisan migrate`
6. Seed first admin: `php artisan db:seed --class=AdminSeeder`
7. Seed exercise library: `php artisan workset:seed-exercises`

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
