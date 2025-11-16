# Performance Optimization Guide

This document outlines the performance optimizations implemented in the Workset application.

## Table of Contents

1. [Database Query Optimization](#database-query-optimization)
2. [Caching Strategies](#caching-strategies)
3. [Asset Optimization](#asset-optimization)
4. [Search Index Tuning](#search-index-tuning)
5. [Performance Monitoring](#performance-monitoring)

---

## Database Query Optimization

### N+1 Query Prevention

**Problem**: N+1 queries occur when related models are loaded in a loop, causing excessive database queries.

**Solution**: Eager loading in Filament resources using `getEloquentQuery()` method.

#### Implemented Optimizations

**ProgramResource** (`app/Filament/Resources/ProgramResource.php:155-162`):
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->withoutGlobalScopes([
            SoftDeletingScope::class,
        ])
        ->with(['owner', 'versions' => fn ($query) => $query->where('is_active', true)]);
}
```
- Eager loads `owner` relationship to prevent N+1 when displaying owner names
- Pre-filters active versions to avoid loading all versions

**PtAssignmentResource** (`app/Filament/Resources/PtAssignmentResource.php:163-167`):
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['pt', 'member']);
}
```
- Eager loads PT and member relationships

**InviteResource** (`app/Filament/Resources/InviteResource.php:226-230`):
```php
public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->with(['inviter', 'personalTrainer']);
}
```
- Eager loads inviter and assigned PT relationships

### Database Indexes

**Migration**: `database/migrations/2025_11_17_071601_add_performance_indexes_to_tables.php`

#### Key Indexes Added

**Training Sessions**:
- `completed_at` - Query sessions by completion status
- `started_at` - Query sessions by start status
- `[user_id, completed_at]` - User's completed sessions
- `[user_id, started_at]` - User's in-progress sessions

**Program Versions**:
- `is_active` - Filter active versions

**Session Sets**:
- `[session_exercise_id, completed]` - Filter completed sets per exercise
- `completed` - Overall completion queries

**Invites**:
- `email` - Email lookup
- `accepted_at` - Pending invites
- `expires_at` - Expired invites
- `[email, accepted_at, expires_at]` - Combined active invite queries

**Exercises**:
- `level` - Filter by difficulty
- `[category, equipment]` - Common filter combination
- `[primary_muscle, level]` - Muscle group + difficulty filter

**PT Assignments**:
- `unassigned_at` - Active assignments
- `[member_id, unassigned_at]` - Member's active assignments

**Program Structure**:
- `[program_version_id, day_number]` - Order days within version
- `[program_day_id, order_index]` - Order exercises within day

**Analytics**:
- `[snapshot_type, snapshot_date]` - Query snapshots by type and date

**Audit Logs**:
- `[user_id, created_at]` - User activity timeline
- `action` - Filter by action type

---

## Caching Strategies

### Redis Configuration

**Default Cache Driver**: Redis (production)
- Configured in `config/cache.php`
- Environment variable: `CACHE_STORE=redis`

### Query Result Caching

**Program Active Version** (`app/Models/Program.php:37-44`):
```php
public function activeVersion(): ?ProgramVersion
{
    return cache()->remember(
        "program.{$this->id}.active_version",
        now()->addHour(),
        fn () => $this->versions()->where('is_active', true)->first()
    );
}
```
- Caches active version for 1 hour
- Key pattern: `program.{id}.active_version`
- **Cache Invalidation**: Should be cleared when program versions are updated

### Cache Invalidation Patterns

When implementing caching, follow these patterns:

1. **Model Events**: Clear cache on `created`, `updated`, `deleted` events
2. **Cache Tags**: Use tags for easy bulk invalidation (Redis only)
3. **TTL**: Set appropriate Time-To-Live based on data change frequency

**Example Model Observer Pattern**:
```php
// In AppServiceProvider or dedicated observer
ProgramVersion::updated(function ($version) {
    cache()->forget("program.{$version->program_id}.active_version");
});
```

### Route Caching

**Production Optimization**:
```bash
# Cache routes for faster routing
php artisan route:cache

# Cache configuration
php artisan config:cache

# Cache views
php artisan view:cache
```

**Development**:
These caches should be cleared during development:
```bash
php artisan optimize:clear
```

---

## Asset Optimization

### Vite Configuration

**File**: `vite.config.js`

#### Code Splitting

```javascript
build: {
    rollupOptions: {
        output: {
            manualChunks(id) {
                // Vendor chunk for node_modules
                if (id.includes('node_modules')) {
                    return 'vendor';
                }
            },
        },
    },
}
```

**Benefits**:
- Vendor code separated into `vendor.js`
- Better browser caching (vendor code changes less frequently)
- Parallel loading of chunks

#### Minification

```javascript
build: {
    minify: 'terser',
    terserOptions: {
        compress: {
            drop_console: true,  // Remove console.log in production
            drop_debugger: true,
        },
    },
}
```

**Benefits**:
- Smaller bundle sizes
- No console output in production
- Removed debugger statements

#### Build Commands

```bash
# Development build with source maps
npm run dev

# Production build (optimised)
npm run build

# Production build with analysis
npm run build -- --mode analyze
```

---

## Search Index Tuning

### Laravel Scout Configuration

**File**: `config/scout.php`

#### Driver Configuration

```php
'driver' => env('SCOUT_DRIVER', 'database'),
'queue' => env('SCOUT_QUEUE', true),
```

**Database Driver**: Suitable for small-to-medium datasets (< 10,000 records)
**Queue**: Index updates happen asynchronously for better performance

#### Searchable Models

**User Model** (`app/Models/User.php:125-132`):
```php
public function toSearchableArray(): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
    ];
}
```

**Exercise Model** (`app/Models/Exercise.php:63-75`):
```php
public function toSearchableArray(): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'description' => $this->description,
        'category' => $this->category?->value,
        'primary_muscle' => $this->primary_muscle?->value,
        'equipment' => $this->equipment?->value,
        'level' => $this->level?->value,
        'aliases' => $this->aliases,
    ];
}
```

**Program Model** (`app/Models/Program.php:52-60`):
```php
public function toSearchableArray(): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'description' => $this->description,
        'owner_name' => $this->owner->name ?? null,
    ];
}
```

#### Scout Commands

```bash
# Import all searchable models
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Exercise"
php artisan scout:import "App\Models\Program"

# Flush indexes
php artisan scout:flush "App\Models\User"

# Import with custom chunk size
php artisan scout:import "App\Models\Exercise" --chunk=100
```

#### Search Usage Examples

```php
// Basic search
$users = User::search('john')->get();

// Search with constraints
$exercises = Exercise::search('bench press')
    ->where('category', ExerciseCategory::Strength->value)
    ->get();

// Paginated search
$programs = Program::search('strength')
    ->paginate(15);
```

### Upgrading to Production Search

For large datasets (> 10,000 records), consider upgrading to:

- **Meilisearch**: Fast, typo-tolerant search engine
- **Algolia**: Cloud-hosted search with analytics
- **Typesense**: Open-source alternative to Algolia

Update `.env`:
```
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your_key_here
```

---

## Performance Monitoring

### Laravel Telescope (Development)

If Telescope is installed:
```bash
php artisan telescope:install
php artisan migrate
```

**Monitors**:
- Database queries and slow query detection
- Cache hits/misses
- Queue job performance
- HTTP requests

### Production Monitoring

**Recommended Tools**:
- Laravel Pulse (lightweight monitoring)
- New Relic APM
- Blackfire.io profiling
- Scout APM

### Database Query Logging

Enable query logging in development to identify N+1 issues:

```php
// In AppServiceProvider boot() method
if (app()->environment('local')) {
    DB::listen(function ($query) {
        if ($query->time > 100) { // Log queries > 100ms
            Log::warning('Slow query detected', [
                'sql' => $query->sql,
                'time' => $query->time,
                'bindings' => $query->bindings,
            ]);
        }
    });
}
```

### Performance Benchmarks

**Target Metrics**:
- Page load: < 200ms (server-side)
- Database queries per request: < 10
- Cache hit ratio: > 80%
- Asset bundle size: < 500KB (gzipped)

---

## Best Practices

### Query Optimization

1. **Always use eager loading** for relationships displayed in lists
2. **Use `select()` to limit columns** when you don't need all fields
3. **Implement pagination** for large datasets
4. **Use database indexes** for frequently queried columns
5. **Avoid `count()` queries** in loops - use `withCount()` instead

### Caching Best Practices

1. **Cache expensive computations**, not cheap ones
2. **Set appropriate TTLs** based on data change frequency
3. **Implement cache invalidation** to prevent stale data
4. **Use cache tags** for grouped invalidation (Redis only)
5. **Monitor cache hit ratios** to ensure effectiveness

### Asset Optimization

1. **Use code splitting** to reduce initial bundle size
2. **Lazy load components** that aren't immediately visible
3. **Optimise images** (WebP format, responsive sizes)
4. **Enable browser caching** with proper cache headers
5. **Use a CDN** for static assets in production

### Scout Optimization

1. **Index only searchable fields** to reduce index size
2. **Use queue workers** for asynchronous indexing
3. **Batch import** large datasets with appropriate chunk sizes
4. **Monitor search performance** and adjust as needed
5. **Consider upgrading** to Meilisearch/Algolia for > 10k records

---

## Troubleshooting

### Slow Queries

1. Check for missing indexes using `EXPLAIN` queries
2. Review query logs for N+1 issues
3. Consider adding composite indexes for complex queries
4. Use database query analysis tools

### Cache Issues

1. Verify Redis connection: `php artisan tinker` → `Cache::get('test')`
2. Check cache driver configuration in `.env`
3. Clear cache if experiencing stale data: `php artisan cache:clear`
4. Monitor cache hit/miss ratios

### Build Issues

1. Clear Vite cache: `rm -rf node_modules/.vite`
2. Rebuild node_modules: `npm ci`
3. Check for conflicting dependencies: `npm ls`

---

## Changelog

### 2025-11-17: Initial Performance Optimization

- Added eager loading to Filament resources
- Created comprehensive database indexes
- Configured Redis caching
- Optimized Vite build configuration
- Configured Laravel Scout with database driver
- Added Searchable trait to User, Exercise, Program models
- Implemented query result caching for active program versions

---

## Future Improvements

1. **Implement cache warming** for frequently accessed data
2. **Add full-page caching** for public pages
3. **Implement HTTP/2 Server Push** for critical assets
4. **Add image lazy loading** and responsive images
5. **Consider database query caching** for complex reports
6. **Implement CDN** for static assets
7. **Add database read replicas** for scaling
8. **Implement queue-based processing** for heavy operations
9. **Add response compression** (gzip/brotli)
10. **Consider upgrading to Laravel Octane** for application server

---

For questions or suggestions, please contact the development team.
