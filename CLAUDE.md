# CLAUDE Project Instructions

This repository, **workset**, is a **Laravel 12.x application** targeting **PHP 8.4**, with the primary domain:

- **https://workset.kneebone.com.au**

Claude Code must treat this file, together with `docs/laravel-php-guidelines.md`, as the **authoritative source of truth** for how to generate and edit code in this project.

---

## 1. Core Assumptions

- This is a **standard Laravel application**, not a package.
- Runtime target: **PHP 8.4**, **Laravel 12.x**.
- Testing framework: **Pest** (no new PHPUnit test classes should be created).
- Use **Australian English** spelling in:
  - Comments
  - Docblocks
  - Test descriptions and names
  - User-facing strings and documentation (unless a spec/API requires otherwise)

---

## 2. Canonical Guidelines (MUST READ)

The canonical coding standards for this project live in:

- `docs/laravel-php-guidelines.md`

You must:

1. **Load and read** `docs/laravel-php-guidelines.md` at the start of any new task or session that involves code changes.
2. **Summarise and internalise** its rules in your working context.
3. Treat its contents as **hard rules**, not suggestions, whenever they apply.

> **Precedence rule:**  
> - If Laravel’s official documentation says one thing and `docs/laravel-php-guidelines.md` says something different,  
>   you must **follow `docs/laravel-php-guidelines.md`**.  
> - Only deviate if the user explicitly instructs you to, or if you ask and the user approves a deviation.

---

## 3. When the Guidelines Apply

`docs/laravel-php-guidelines.md` applies to all work on:

- **A. PHP / Laravel code**
  - Controllers, models, jobs, events, listeners
  - Services, Actions, Repositories
  - Console commands, API resources, policies
  - Migrations, factories, seeders

- **B. Blade templates**
  - View structure and naming
  - Blade directives, route usage, localisation

- **C. Tests (Pest)**
  - New tests must be **Pest closure-style**, not new PHPUnit classes
  - Test data generation, assertions, mocking, and structure

- **D. Front-end JS/TS**
  - When interacting with Laravel routes, APIs, or data structures, follow naming and JSON key conventions defined in the guidelines (e.g. camelCase for public JSON keys).

Whenever you **create or modify** anything in categories A–D, you must:

1. Assume the Laravel & PHP guidelines are in force.
2. Apply the relevant conventions from `docs/laravel-php-guidelines.md` (routing, naming, migrations, tests, enums, architecture, etc.).
3. Prefer route names over hard-coded URLs, follow Pest patterns, and respect all naming conventions described there.

---

## 4. File Treatment Rules

### 4.1 `docs/laravel-php-guidelines.md`

- Treat `docs/laravel-php-guidelines.md` as **read-only by default**.
- Do **not** modify or rewrite this file unless:
  - The user explicitly asks you to change it, **or**
  - You have a concrete suggestion to improve clarity and the user agrees.

If you believe a change would improve the guidelines:

1. **Do not modify the file automatically.**
2. Instead, explain the proposed change to the user (briefly) and ask for approval.
3. Only update `docs/laravel-php-guidelines.md` after explicit confirmation.

### 4.2 Other Files

- You **may** freely create and modify PHP, Blade, tests, and JS/TS files, as long as:
  - All changes comply with `docs/laravel-php-guidelines.md`.
  - You maintain consistency with the existing code style and project structure.

---

## 5. Behaviour and Style Expectations

When working in this repo, Claude Code should:

1. **Respect the guidelines strictly**
   - Migrations: no `down()` methods unless explicitly requested.
   - Use foreign keys only for core domain tables as defined in the guidelines.
   - Use route names instead of hard-coded URLs in controllers, tests, and views.
   - Use Pest for tests (closure-based, no new test classes).
   - Follow naming conventions for controllers, routes, config, enums, resources, etc.

2. **Explain significant deviations**
   - If you must deviate from the guidelines for a valid reason (e.g. framework limitation or user request), briefly explain why in your response.

3. **Ask when in doubt**
   - If a situation is not clearly covered by `docs/laravel-php-guidelines.md` and the choice could affect project-wide conventions, ask the user before introducing a new pattern.

4. **Maintain clarity**
   - Prefer explicit and readable code over “cleverness”.
   - Ensure tests are meaningful and would fail if the underlying behaviour breaks, following the testing philosophy in the guidelines.

---

## 6. Typical Workflow for Code Changes

When the user asks you to implement or modify functionality in this repo:

1. **Check context**
   - Confirm that this is the `workset` Laravel app repo (assume yes by default).
   - Load and apply `docs/laravel-php-guidelines.md`.

2. **Plan with the guidelines in mind**
   - Decide which parts of the guidelines are relevant (e.g. routes, controllers, Blade, tests, enums, migrations).
   - Ensure any new files or classes follow the documented naming and structure conventions.

3. **Generate code**
   - For PHP/Laravel: follow all conventions around routes, controllers, migrations, tests, and architecture.
   - For Blade: obey view naming, indentation, and route usage rules.
   - For tests: generate Pest tests that follow the “strong tests” patterns.
   - For JS/TS: respect JSON key and naming conventions (camelCase public JSON keys, etc.).

4. **Validate against the guidelines**
   - Before finalising a response, mentally check the output against `docs/laravel-php-guidelines.md`.
   - If something conflicts, adjust the code to match the guidelines (unless the user explicitly asked otherwise).

---

## 7. Language & Documentation

- Use **Australian English** spelling in:
  - Comments and docblocks
  - Test descriptions and names
  - User-facing strings generated in this repo
  - Markdown/docs created or edited here
- Aim for a **clear, concise, and neutral** tone in:
  - Explanations
  - In-code comments
  - Commit message suggestions (if asked)

---

By following this `CLAUDE.md` and always loading `docs/laravel-php-guidelines.md`, Claude Code should behave as a consistent Laravel 12 / PHP 8.4 assistant that respects the project’s conventions across PHP, Blade, tests, and front-end code for the **workset** application at **workset.kneebone.com.au**.
