# Laravel & PHP Guidelines for AI Code Assistants

This file contains **Laravel and PHP coding standards** optimised for AI code assistants like Claude Code, GitHub Copilot, and Cursor.

- **Target runtime:** PHP **8.4**, Laravel **12.x**
- **Project type:** Standard **Laravel applications** (not packages)
- **Testing framework:** **Pest** (no new PHPUnit test classes)

> **Rule of precedence:**  
> **Follow Laravel conventions unless this document specifies otherwise; if there is a conflict, this document wins.**

---

## Core Laravel Principle

**Follow Laravel conventions first** unless overridden in this document.  
If Laravel has a documented way to do something, use it, unless there is a house rule here that says otherwise.

---

## PHP Standards

- Follow **PSR-1** and **PSR-12**
- Use **typed properties** wherever possible
- Use **short nullable notation**: `?string` not `string|null`
- Always specify **return types**, including `void` when methods return nothing
- Use **camelCase** for:
  - Variable and method names
  - Internal **array keys** and **JSON keys** that are **not** public API or config
- Use **snake_case** for:
  - Config keys (e.g. `chrome_path`)
- Use **kebab-case** for:
  - URLs (e.g. `/open-source`)
  - Translation keys segments (e.g. `auth.failed-login`)

---

## Code Quality

- Use **descriptive names** for variables, methods, classes, and components
- Prefer **iteration and modularisation** over duplication:
  - Extract private methods or Actions when logic becomes non-trivial or reused
- Keep functions / methods **small and focused**
- Avoid “clever” code that hides intent – prefer clear and explicit behaviour

---

## Framework & Architecture

### Data & Business Logic

- Use **database foreign keys only for core domain tables**:
  - Core domain tables model primary business entities (e.g. `users`, `customers`, `bookings`, `orders`, `payments`, `subscriptions`, `invoices`, etc.)
  - Technical/auxiliary tables (e.g. logs, jobs, audit tables, meta tables, tag pivots, cache tables) **must not** use foreign keys; enforce integrity via application logic and tests
- Prefer **application-level invariants** and **strong tests** for non-core tables
- Use **Repository** classes in `app/Repositories` **only when there is clear reuse or complexity**, for example:
  - Multiple callers depend on the same query logic
  - Query logic is non-trivial (branching, joins, filters, ~10+ lines)
- Use **Action** classes in `app/Actions` to encapsulate **complex operations** when:
  - There are multi-step workflows
  - There is clear reuse across controllers/jobs/listeners
  - The logic would otherwise make a controller/job too large

> Do **not** wrap every query or operation in a Repository/Action “just because the folder exists”.

### Request Handling & Validation

- Use **named routes** for all application endpoints and redirects
- Implement validation with **Form Request** classes where possible
- Use **middleware** for cross-cutting concerns:
  - Authentication / authorisation
  - Request normalisation (e.g. trimming, locale, tenant resolution)
  - Rate limiting

---

## Class Structure

- **Typed properties, not docblocks**

  ```php
  // ✅ Preferred
  class UserData
  {
      public string $name;
      public ?string $nickname = null;
  }

  // ❌ Avoid docblock-only types
  class UserData
  {
      /** @var string */
      public $name;
  }
  ```

- **Constructor property promotion** when all injected properties can be promoted:

  ```php
  // ✅ Preferred
  class CreateOrderAction
  {
      public function __construct(
          private OrderService $orderService,
          private LoggerInterface $logger,
      ) {}

      public function execute(OrderData $data): Order
      {
          // ...
      }
  }
  ```

- **One trait per line** when using multiple traits:

  ```php
  class SomeService
  {
      use LogsActivity;
      use DispatchesJobs;
      use AuthorisesRequests;

      // ...
  }
  ```

---

## Type Declarations & Docblocks

- Use **typed properties** rather than docblocks wherever possible
- Specify **return types**, including `void` for methods that return nothing
- Use short nullable syntax: `?Type` not `Type|null`

### When to Use Docblocks

- Do **not** add docblocks to fully type-hinted methods **unless**:
  - You need a **human-readable description**, or
  - You are documenting **generics/array-shapes** and already adding a description
- When you add a docblock for a method:
  - Document **all parameters** in that docblock
  - Do not redundantly restate simple scalar types if not needed

### Docblock Rules & Examples

- Always **import class names** for docblocks:

  ```php
  use Spatie\Url\Url;

  /** @return Url */
  public function url(): Url
  {
      // ...
  }
  ```

- Use one-line docblocks when possible:

  ```php
  /** @var string */
  public string $name = '';
  ```

- If multiple types are needed, **most common type first**:

  ```php
  /** @var Collection|SomeWeirdVendor\Collection */
  private Collection $items;
  ```

- For iterables, specify **key and value** types when you are already writing a docblock:

  ```php
  /**
   * @param array<int, MyObject> $myArray
   * @param int $typedArgument
   */
  function someFunction(array $myArray, int $typedArgument): void
  {
      // ...
  }
  ```

- Use **array shape notation** for fixed keys; put each key on its own line:

  ```php
  /**
   * @return array{
   *   first: SomeClass,
   *   second: SomeClass
   * }
   */
  public function data(): array
  {
      // ...
  }
  ```

---

## Control Flow

- Use **guard clauses** first, then a straight-line **happy path**:

  ```php
  if (! $user) {
      return null;
  }

  if (! $user->isActive()) {
      return null;
  }

  // Happy path: process active user
  $this->sendWelcomeNotification($user);

  return $user;
  ```

- **Avoid `else`** where reasonable – prefer early returns
- **Separate conditions** rather than complex compound expressions when it improves clarity
- Always use **curly braces** even for single-line statements:

  ```php
  if ($condition) {
      doSomething();
  }
  ```

- **Ternary operators**:
  - Keep short ternaries on one line
  - For longer expressions, split across multiple lines:

  ```php
  // Short ternary
  $name = $isFoo ? 'foo' : 'bar';

  // Multi-line ternary
  $result = $object instanceof Model
      ? $object->name
      : 'A default value';
  ```

---

## Laravel Conventions

### Routes

- **URLs:** kebab-case (`/open-source`, `/user-profile`)
- **Route names:** **dotted camelCase segments**, e.g.:
  - `->name('openSource.index')`
  - `->name('userProfile.store')`
- **Route parameters:** camelCase (`{userId}`, `{orderId}`)
- Always define routes using **tuple notation**:

  ```php
  Route::get('/open-source', [OpenSourceController::class, 'index'])
      ->name('openSource.index');
  ```

- Prefer **route names over hardcoded URLs everywhere**:
  - Controllers
  - Redirects
  - Blade templates
  - Tests

  ```php
  return to_route('posts.index');

  $url = route('posts.store');
  ```

### Controllers

- Controllers use **singular resource names**:
  - `PostController`, `UserController`, `OrderController`
- Use standard **CRUD methods**:
  - `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- For non-CRUD actions, create **separate controllers** or clearly named methods, e.g.:
  - `PostPublishController`
  - `UserAvatarController`

### Configuration

- **Config files:** kebab-case (`pdf-generator.php`)
- **Config keys:** snake_case (`chrome_path`)
- **Service configs:**
  - Small/simple external services → `config/services.php`
  - Large/complex modules → **dedicated config file** (e.g. `config/billing.php`, `config/search.php`)
- Use `config()` helper in application code; avoid `env()` outside config files.

---

## Artisan Commands

- Command names: **kebab-case** (`delete-old-records`, `send-daily-report`)
- Always provide feedback:

  ```php
  $this->comment('Starting cleanup...');
  ```

- For loops, show progress or at least a summary at the end
- Prefer logging **before** processing each item (easier debugging):

  ```php
  $items->each(function (Item $item): void {
      $this->info("Processing item id `{$item->id}`...");
      $this->processItem($item);
  });

  $this->comment("Processed {$items->count()} items.");
  ```

---

## Strings & Formatting

- Prefer **string interpolation** over concatenation:

  ```php
  // ✅ Preferred
  $message = "Hello {$user->name}, welcome back.";

  // ❌ Avoid
  $message = 'Hello ' . $user->name . ', welcome back.';
  ```

---

## Enums

- Use **PascalCase** for enum **case names**:

  ```php
  enum OrderStatus
  {
      case Pending;
      case InProgress;
      case Completed;
      case Cancelled;
  }
  ```

---

## Comments

- **Avoid comments** by writing expressive code
- When comments are needed, use proper formatting:

  ```php
  // Single line with a space after //

  /*
   * Multi-line blocks start with a single *
   */
  ```

- Prefer refactoring commented code into **descriptive function names** rather than relying on comments to explain behaviour

---

## Whitespace

- Add **blank lines between logical blocks** for readability
- Acceptable to omit blank lines where there is a clear, tight sequence of simple operations
- No unnecessary empty lines between opening and closing `{}` unless needed for readability
- Let your code **“breathe”** – avoid overly cramped formatting

---

## Validation

- Use array notation for multiple rules:

  ```php
  public function rules(): array
  {
      return [
          'email' => ['required', 'email'],
      ];
  }
  ```

- Custom validation rules use **snake_case** names:

  ```php
  Validator::extend('organisation_type', function ($attribute, $value): bool {
      return OrganisationType::isValid($value);
  });
  ```

---

## Blade Templates

- Indent with **4 spaces**
- **No spaces** between directive and expression:

  ```blade
  @if($condition)
      Something
  @endif
  ```

- Use route names in Blade templates:

  ```blade
  <a href="{{ route('posts.show', $post) }}">View</a>
  ```

- View filenames: **kebab-case**:

  ```text
  resources/views/open-source.blade.php
  resources/views/user-profile/show.blade.php
  ```

---

## Authorization

- Policy abilities use **camelCase**:

  ```php
  Gate::define('editPost', function (User $user, Post $post): bool {
      // ...
  });
  ```

- Use CRUD-like words for abilities, but **`view` instead of `show`**:
  - `view`, `create`, `update`, `delete`, `restore`, `forceDelete`

---

## Translations

- Use `__()` function over `@lang`:

  ```php
  __('auth.failed-login');
  ```

- Translation keys use **kebab-case segments** separated by dots:

  ```php
  __('auth.failed-login');
  __('validation.required-field');
  __('orders.payment-failed');
  ```

---

## API Routing

- Use **plural resource names** in URIs:
  - `/errors`, `/users`, `/orders`
- Use **kebab-case** in URL segments:
  - `/error-occurrences`, `/user-profile`
- Limit deep nesting for simplicity:

  ```text
  /error-occurrences/1
  /errors/1/occurrences
  ```

- **Public API JSON keys** use **camelCase**:

  ```json
  {
    "userId": 1,
    "firstName": "Jim",
    "createdAt": "2025-01-01T00:00:00Z"
  }
  ```

---

## Testing

> **Critical testing philosophy**  
> *“A test that never fails is not a test, it's a lie.”*  
> Every test MUST fail when the code it tests is broken.

### Testing Framework & Scope

- Use **Pest** exclusively for unit and feature tests
- For **new tests**, use **Pest closure-style** (`it()`, `test()`, `describe()`):
  - Do **not** create new `class ...Test extends TestCase` files
  - It is acceptable to use `$this` inside Pest tests for TestCase helpers (`actingAs`, `travel`, etc.)
- Always write or modify tests for any new or changed code
- Aim for **80%+ code coverage** with **efficient** (non-redundant) tests

### Forbidden Weak Test Patterns (Auto-Reject)

These assertions are almost always too weak:

```php
// ❌ WEAK - Type-only assertions (meaningless)
expect($user->company)->toBeInstanceOf(Company::class);

// ❌ WEAK - Generic array assertions (no value)
expect($response)->toBeArray();

// ❌ WEAK - Not-null assertions (tells us nothing)
expect($property->address)->not->toBeNull();

// ❌ WEAK - Bare booleans on raw flags
expect($result)->toBeTrue();

// ❌ WEAK - Testing framework code
expect($user->posts())->toBeInstanceOf(HasMany::class);

// ❌ WEAK - Over-mocking what you're testing
$service = Mockery::mock(OrderService::class)->makePartial();
```

> **Exception:**  
> `toBeTrue()` / `toBeFalse()` is allowed when the expression is a clearly named **business rule or status method**, e.g. `hasVerifiedEmail()`, `isAdmin()`, `isExpired()`.

### Required Strong Test Patterns

```php
// ✅ Test specific values and behaviour
expect($user->company->id)->toBe($expectedCompany->id)
    ->and($user->company->name)->toBe('Expected Company');

// ✅ Test structure and content
expect($response)->toBeArray()
    ->toHaveCount(3)
    ->toHaveKeys(['id', 'name', 'status'])
    ->and($response['status'])->toBe('active');

// ✅ Behaviour with domain context
$user = User::factory()->create(['email_verified_at' => null]);
$user->markEmailAsVerified();

expect($user->hasVerifiedEmail())->toBeTrue()
    ->and($user->email_verified_at)->toBeInstanceOf(Carbon::class);

// ✅ Edge cases and errors
$result = $handler->processData([]);
expect($result)->toBe([]);

expect(fn () => $handler->processData(null))
    ->toThrow(InvalidArgumentException::class, 'Data cannot be null');

// ✅ Both directions of relationships
expect($user->team->id)->toBe($team->id);
$team->load('users');
expect($team->users->pluck('id'))->toContain($user->id);
```

### Pest Features (Mandatory Usage)

Use Pest features for clarity and organisation:

```php
// Group tests with describe()
describe('Console Command', function () {
    beforeEach(function () {
        Process::fake();
        // Setup test data
    });

    describe('package installation', function () {
        it('installs packages when --composer flag is set', function () {
            // Test implementation
        });
    });
});

// Higher-order expectations
expect($user)
    ->name->toBe('John')
    ->email->toContain('@example.com')
    ->isAdmin()->toBeTrue();

// Datasets instead of duplicate tests
it('validates emails', function (string $email, bool $expected) {
    expect(Validator::isEmail($email))->toBe($expected);
})->with([
    'valid gmail'    => ['test@gmail.com', true],
    'invalid format' => ['invalid.email', false],
    'subdomain'      => ['test@sub.domain.com', true],
]);

// Hooks for setup/cleanup
it('processes files')
    ->before(function () {
        Storage::fake('local');
    })
    ->after(function () {
        Storage::disk('local')->deleteDirectory('test');
    });
```

### Laravel Testing Examples

Prefer **route names** instead of hardcoded URLs:

```php
// HTTP Testing
$response = $this->actingAs($user)
    ->postJson(route('posts.store'), $data);

$response
    ->assertCreated()
    ->assertJson([
        'data' => [
            'title' => 'Expected',
        ],
    ]);

// Database Testing
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
]);

$this->assertDatabaseCount('posts', 3);
```

Mock external dependencies with Laravel fakes:

```php
Process::fake([
    'ls *' => Process::result('file1.txt file2.txt'),
]);

Http::fake([
    'api.example.com/*' => Http::response(['status' => 'ok']),
]);

Storage::fake('local');
Mail::fake();
Queue::fake();

// Time manipulation
$this->travel(30)->minutes();
$this->freezeTime();

expect($token->isExpired())->toBeTrue();
```

Command testing:

```php
$this->artisan('import:data')
    ->expectsQuestion('Which source?', 'api')
    ->expectsOutput('Starting import...')
    ->expectsTable(['ID', 'Status'], [[1, 'Success']])
    ->assertSuccessful();
```

### Test Data Generation

```php
// ✅ Use fake() helper in Pest
$user = User::factory()->create([
    'name'  => fake()->name(),
    'email' => fake()->unique()->safeEmail(),
]);

// ❌ Never use $this->faker in Pest
$name = $this->faker->name(); // This does not work in Pest
```

### Mocking Best Practices

```php
// ✅ Mock external dependencies only
$mock = $this->mock(PaymentGateway::class, function ($mock): void {
    $mock->shouldReceive('charge')
        ->once()
        ->with(100, 'USD')
        ->andReturn(new PaymentResult(true));
});

// ✅ Use spies for post-execution assertions
$spy = $this->spy(Logger::class);
// ... execute code ...
$spy->shouldHaveReceived('log')
    ->once()
    ->with('Payment processed');

// ❌ Do not mock the class under test
$command = Mockery::mock(ConsoleCommand::class)->makePartial(); // Avoid
```

### Quick Quality Check (4 Questions)

Before finalising any test, ask:

1. **Does this test specific values or just types/existence?**
   - ❌ `toBeInstanceOf()`, `toBeArray()`, `not->toBeNull()` on their own
   - ✅ `toBe('specific')`, `toHaveCount(3)`, `toContain('expected')`

2. **Am I testing my code or the framework?**
   - ❌ Testing Laravel internals (relationship types, validation rule classes)
   - ✅ Testing **business logic** that uses the framework

3. **Would this test catch a real bug?**
   - ❌ Passes even if core logic is broken
   - ✅ Fails immediately when behaviour changes

4. **Are edge cases and errors covered?**
   - ❌ Only happy path
   - ✅ Empty data, null values, invalid input, error paths

### Auto-Reject Patterns (Re-stated)

If you see any of these, the test probably needs rewriting:

```php
expect($anything)->toBeInstanceOf(SomeClass::class);         // Type-only
expect($anything)->toBeArray();                              // Generic
expect($anything)->not->toBeNull();                          // Meaningless
expect($rawFlag)->toBeTrue();                                // No domain context
expect($model->relation())->toBeInstanceOf(HasMany::class);  // Framework
```

> ✅ `expect($user->isAdmin())->toBeTrue();` is acceptable  
> because `isAdmin()` is a domain rule, not just a raw flag.

### Test Optimisation Guidelines

- Avoid testing the same code paths multiple times with trivial variations
- Use **datasets** and **parametric tests** to cover many inputs efficiently
- Prefer **fewer, stronger tests** over many weak ones
- Use **helper functions** to de-duplicate setup and assertions
- Aim for:
  - High value coverage, not just high numeric coverage
  - Individual tests that **normally finish under ~3 seconds**

---

## Code Quality & Standards

- Enforce code style with **Laravel Pint** (`pint.json`)
- Use **PHPStan** for static analysis (`phpstan.neon`)
- Keep complexity manageable:
  - Extract methods or Actions when methods grow too large
  - Avoid deep nesting; use guard clauses and early returns

---

## Quick Reference

### Naming Conventions

- **Classes:** PascalCase (`UserController`, `OrderStatus`)
- **Methods/Variables:** camelCase (`getUserName`, `$firstName`)
- **Routes:**
  - URI: kebab-case (`/open-source`, `/user-profile`)
  - Names: dotted camelCase segments (`openSource.index`, `userProfile.store`)
- **Config files:** kebab-case (`pdf-generator.php`)
- **Config keys:** snake_case (`chrome_path`)
- **Artisan commands:** kebab-case (`php artisan delete-old-records`)

### File Structure

- Controllers: **singular** resource name + `Controller` (`PostController`, `UserController`)
- Views: **kebab-case** (`open-source.blade.php`, `user-profile/show.blade.php`)
- Jobs: action-based (`CreateUser`, `SendEmailNotification`)
- Events: tense-based (`UserRegistering`, `UserRegistered`)
- Listeners: action + `Listener` suffix (`SendInvitationMailListener`)
- Commands: action + `Command` suffix (`PublishScheduledPostsCommand`)
- Mailables: purpose + `Mail` suffix (`AccountActivatedMail`)
- API Resources / Transformers: **singular** + `Resource`/`Transformer` (`UserResource`, `OrderResource`)
- Enums: descriptive names with no prefix (`OrderStatus`, `BookingType`)

### Migrations

- **Do not implement `down()` methods** – migrations are **one-way only**
- Use foreign keys **only for core domain tables** as defined above
- Avoid foreign keys in technical/auxiliary tables

### PHP Code Quality Reminders

- Use typed properties over docblocks
- Prefer early returns over nested `if`/`else`
- Use constructor property promotion when all properties can be promoted
- Avoid `else` when guard clauses make code clearer
- Use string interpolation over concatenation
- Always use curly braces for control structures

---
