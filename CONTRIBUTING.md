# Contributing to Workset

Thank you for your interest in contributing to Workset! This document provides guidelines and instructions for contributing to the project.

---

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Workflow](#development-workflow)
4. [Coding Standards](#coding-standards)
5. [Testing](#testing)
6. [Pull Request Process](#pull-request-process)
7. [Commit Messages](#commit-messages)
8. [Documentation](#documentation)
9. [Issue Reporting](#issue-reporting)

---

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all.

### Expected Behaviour

- Be respectful and inclusive
- Welcome newcomers and help them get started
- Be patient with questions
- Accept constructive criticism gracefully
- Focus on what is best for the community

### Unacceptable Behaviour

- Harassment or discrimination
- Trolling or insulting comments
- Public or private harassment
- Publishing others' private information
- Unprofessional conduct

### Enforcement

Project maintainers have the right to remove, edit, or reject comments, commits, code, and other contributions that do not align with this Code of Conduct.

Report violations to: conduct@workset.kneebone.com.au

---

## Getting Started

### Prerequisites

- **PHP 8.4+**
- **Composer 2.x**
- **Node.js 20+** and npm/yarn
- **Docker & Docker Compose** (recommended)
- **Git**

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:

```bash
git clone https://github.com/YOUR_USERNAME/workset.git
cd workset
```

3. Add the upstream repository:

```bash
git remote add upstream https://github.com/ingoldsby/workset.git
```

### Local Development Setup

#### Using Docker (Recommended)

```bash
# Copy environment file
cp .env.example .env

# Start containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database (optional)
docker-compose exec app php artisan db:seed

# Build frontend assets
docker-compose exec app npm run dev
```

Access the application at: http://localhost:8000

#### Without Docker

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=workset
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Build assets
npm run dev

# Start development server
php artisan serve
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/SessionLoggingTest.php

# Run with coverage
php artisan test --coverage

# Run Pest tests directly
./vendor/bin/pest

# Run with parallel execution
php artisan test --parallel
```

---

## Development Workflow

### Branch Naming

Use descriptive branch names following this pattern:

- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `hotfix/description` - Urgent production fixes
- `refactor/description` - Code refactoring
- `docs/description` - Documentation updates
- `test/description` - Test additions/updates

**Examples:**
```
feature/social-sharing
bugfix/session-completion-error
hotfix/auth-vulnerability
refactor/analytics-service
docs/api-documentation
test/progression-rules
```

### Creating a Feature Branch

```bash
# Update your local main branch
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/your-feature-name
```

### Keeping Your Branch Updated

```bash
# Fetch latest changes
git fetch upstream

# Rebase your branch
git rebase upstream/main

# Or merge (if you prefer)
git merge upstream/main
```

### Making Changes

1. **Make atomic commits** - Each commit should represent a single logical change
2. **Write tests first** (TDD approach preferred)
3. **Follow coding standards** (see below)
4. **Run tests locally** before pushing
5. **Update documentation** as needed

---

## Coding Standards

### PHP Standards

Workset follows strict Laravel and PHP 8.4 conventions as documented in `docs/laravel-php-guidelines.md`.

**Key Rules:**

1. **Read the Guidelines First**
   ```bash
   # MUST read before any code changes
   cat docs/laravel-php-guidelines.md
   ```

2. **Australian English**
   - Comments, docblocks, and user-facing strings use Australian spelling
   - Example: "Organise" not "Organize"

3. **Type Declarations**
   - Always use typed properties
   - Always use return type declarations
   - Use union types where appropriate

   ```php
   // Good
   public function processSession(TrainingSession $session): bool
   {
       // ...
   }

   // Bad
   public function processSession($session)
   {
       // ...
   }
   ```

4. **Constructor Property Promotion**
   ```php
   // Good
   public function __construct(
       private readonly SessionRepository $sessions,
       private readonly AnalyticsService $analytics,
   ) {}

   // Bad
   private $sessions;
   private $analytics;

   public function __construct(SessionRepository $sessions, AnalyticsService $analytics)
   {
       $this->sessions = $sessions;
       $this->analytics = $analytics;
   }
   ```

5. **Early Returns / Guard Clauses**
   ```php
   // Good
   public function completeSession(TrainingSession $session): void
   {
       if ($session->isCompleted()) {
           return;
       }

       if (! $session->hasRequiredSets()) {
           throw new IncompleteSessionException;
       }

       $session->markComplete();
   }

   // Bad
   public function completeSession(TrainingSession $session): void
   {
       if (! $session->isCompleted()) {
           if ($session->hasRequiredSets()) {
               $session->markComplete();
           } else {
               throw new IncompleteSessionException;
           }
       }
   }
   ```

6. **No Down Migrations**
   - Never add `down()` methods to migrations
   - Rollbacks are done via database restore

   ```php
   // Good
   public function up(): void
   {
       Schema::create('programmes', function (Blueprint $table) {
           $table->ulid('id')->primary();
           // ...
       });
   }

   // Bad - don't include down()
   public function down(): void
   {
       Schema::dropIfExists('programmes');
   }
   ```

7. **Use ULIDs for Primary Keys**
   ```php
   // In migrations
   $table->ulid('id')->primary();
   $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();

   // In models
   use Illuminate\Database\Eloquent\Concerns\HasUlids;

   class Program extends Model
   {
       use HasUlids;
   }
   ```

8. **Route Names, Not URLs**
   ```php
   // Good
   return redirect()->route('sessions.show', $session);

   // Bad
   return redirect("/sessions/{$session->id}");
   ```

### Code Formatting

```bash
# Run Pint (Laravel's PHP formatter)
./vendor/bin/pint

# Check only (dry-run)
./vendor/bin/pint --test
```

### PHPStan Analysis

```bash
# Run static analysis
./vendor/bin/phpstan analyse

# Run at max level
./vendor/bin/phpstan analyse --level=max
```

---

## Testing

### Writing Tests

Workset uses **Pest** for testing.

**Test File Structure:**
```
tests/
├── Feature/         # Feature/integration tests
│   ├── Auth/
│   ├── AnalyticsTest.php
│   └── SessionLoggingTest.php
└── Pest.php         # Pest configuration
```

**Example Test:**
```php
<?php

use App\Models\User;
use App\Models\TrainingSession;

test('user can complete a training session', function () {
    $user = User::factory()->create();
    $session = TrainingSession::factory()
        ->for($user)
        ->create();

    $response = $this
        ->actingAs($user)
        ->post(route('sessions.complete', $session), [
            'completed_at' => now(),
        ]);

    $response->assertRedirect(route('sessions.show', $session));
    expect($session->fresh()->completed_at)->not->toBeNull();
});

test('cannot complete another users session', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $session = TrainingSession::factory()->for($otherUser)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('sessions.complete', $session));

    $response->assertForbidden();
});
```

### Test Coverage Requirements

- **Minimum coverage**: 80% for new features
- **Critical paths**: 100% coverage required
- **Models**: Test all relationships and scopes
- **Controllers**: Test all actions and authorisation
- **Services/Actions**: Test business logic thoroughly

### Running Specific Tests

```bash
# Run specific test file
php artisan test tests/Feature/AnalyticsTest.php

# Run specific test
php artisan test --filter="it calculates total volume"

# Run with coverage
php artisan test --coverage --min=80
```

---

## Pull Request Process

### Before Submitting

1. **Ensure all tests pass**
   ```bash
   php artisan test
   ```

2. **Run code formatting**
   ```bash
   ./vendor/bin/pint
   ```

3. **Run static analysis**
   ```bash
   ./vendor/bin/phpstan analyse
   ```

4. **Update documentation** if needed

5. **Rebase on latest main**
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

### Creating the Pull Request

1. **Push your branch**
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Open PR on GitHub**
   - Use a clear, descriptive title
   - Reference any related issues (#123)
   - Fill out the PR template completely

3. **PR Description Should Include**:
   - What: Brief description of changes
   - Why: Motivation and context
   - How: Implementation approach
   - Testing: How changes were tested
   - Screenshots: For UI changes
   - Breaking changes: If any

### PR Template

```markdown
## Description
Brief description of what this PR does.

## Motivation
Why is this change needed? What problem does it solve?

## Changes Made
- List key changes
- Use bullet points
- Be specific

## Testing
- [ ] Added unit tests
- [ ] Added feature tests
- [ ] Manual testing completed
- [ ] All tests passing

## Screenshots (if applicable)
[Add screenshots for UI changes]

## Breaking Changes
List any breaking changes and migration path.

## Checklist
- [ ] Code follows project guidelines
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] Pint formatting applied
- [ ] PHPStan passes
- [ ] Commit messages follow convention

## Related Issues
Closes #123
```

### Review Process

1. **Automated Checks**
   - CI/CD pipeline runs
   - Tests must pass
   - Code coverage checked
   - Static analysis passes

2. **Code Review**
   - At least one approval required
   - Address all feedback
   - Re-request review after changes

3. **Merge**
   - Squash and merge (preferred)
   - Ensure linear history
   - Delete branch after merge

---

## Commit Messages

### Format

Follow the Conventional Commits specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `perf`: Performance improvements

### Examples

**Feature:**
```
feat(analytics): add muscle group distribution chart

- Add MuscleGroupDistribution Livewire component
- Create pie chart visualization
- Add period filter (week/month/year)
- Update analytics dashboard layout

Closes #145
```

**Bug Fix:**
```
fix(sessions): prevent duplicate set creation on rapid clicks

Add debounce to set creation button to prevent race condition
when user rapidly clicks "Add Set" button.

Fixes #167
```

**Breaking Change:**
```
feat(auth)!: migrate to Laravel Sanctum

BREAKING CHANGE: Session-based auth replaced with token-based auth.

Migration guide:
1. Run: php artisan migrate
2. Update API clients to use Bearer tokens
3. See docs/MIGRATION_GUIDE.md for details
```

### Commit Message Best Practices

1. **Subject line**:
   - Use imperative mood ("add" not "added")
   - Keep under 50 characters
   - No period at the end
   - Capitalise first letter

2. **Body** (if needed):
   - Wrap at 72 characters
   - Explain what and why, not how
   - Separate from subject with blank line

3. **Footer**:
   - Reference issues (Closes #123)
   - Note breaking changes
   - Credit co-authors

---

## Documentation

### Required Documentation

When adding features or making changes:

1. **Code Comments**
   - Document complex logic
   - Explain non-obvious decisions
   - Use PHPDoc for public methods

2. **User Documentation**
   - Update `docs/USER_GUIDE.md` for user-facing features
   - Add screenshots/examples
   - Include troubleshooting

3. **API Documentation**
   - Update `docs/API.md` for API changes
   - Document new endpoints
   - Update examples

4. **Code Documentation**
   - Update `docs/laravel-php-guidelines.md` if adding patterns
   - Document architectural decisions
   - Update README.md if needed

### Documentation Standards

**PHPDoc Example:**
```php
/**
 * Calculate total training volume for a session.
 *
 * Volume is calculated as the sum of (weight × reps) for all
 * completed sets in the session, excluding warm-up sets.
 *
 * @param  TrainingSession  $session  The session to calculate volume for
 * @return float The total volume in kilograms
 *
 * @throws IncompleteSessionException If session has no completed sets
 */
public function calculateSessionVolume(TrainingSession $session): float
{
    // Implementation
}
```

---

## Issue Reporting

### Before Creating an Issue

1. **Search existing issues** - It may already be reported
2. **Check closed issues** - It may have been fixed
3. **Reproduce the bug** - Ensure it's reproducible
4. **Collect information** - Browser, PHP version, etc.

### Bug Report Template

```markdown
## Bug Description
Clear description of the bug.

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

## Expected Behaviour
What should happen.

## Actual Behaviour
What actually happens.

## Environment
- PHP Version: 8.4.0
- Laravel Version: 12.0.0
- Browser: Chrome 120
- OS: macOS 14.0

## Screenshots
[Add screenshots if applicable]

## Additional Context
Any other relevant information.
```

### Feature Request Template

```markdown
## Feature Description
Clear description of the proposed feature.

## Motivation
Why is this feature needed? What problem does it solve?

## Proposed Solution
How should this feature work?

## Alternatives Considered
What other approaches were considered?

## Additional Context
Mockups, examples, etc.
```

---

## Development Best Practices

### Security

- Never commit secrets (.env files, API keys, etc.)
- Use parameterised queries (Laravel does this by default)
- Validate all inputs
- Sanitise all outputs
- Use CSRF protection
- Follow OWASP guidelines

### Performance

- Use eager loading to avoid N+1 queries
- Cache expensive queries
- Use database indexes appropriately
- Optimise asset builds
- Profile slow queries

### Accessibility

- Use semantic HTML
- Add ARIA labels where needed
- Ensure keyboard navigation works
- Test with screen readers
- Follow WCAG 2.1 AA standards

---

## Getting Help

### Communication Channels

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and ideas
- **Email**: dev@workset.kneebone.com.au

### Asking Good Questions

1. **Be specific**: Vague questions get vague answers
2. **Show your work**: What have you tried?
3. **Provide context**: Environment, versions, etc.
4. **Include code**: Use code blocks for readability
5. **Be patient**: Maintainers are volunteers

---

## License

By contributing to Workset, you agree that your contributions will be licensed under the same license as the project.

---

## Recognition

Contributors will be recognised in:

- CONTRIBUTORS.md file
- Release notes for significant contributions
- Project README

---

## Thank You!

Your contributions make Workset better for everyone. We appreciate your time and effort!

**Questions?** Contact dev@workset.kneebone.com.au

**Last Updated:** November 2025
