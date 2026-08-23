# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

CentraLearn Backend — a Laravel 13 API for a Learning Management System (LMS). Session-based auth
(not token API auth), UUID primary keys throughout, role-based authorization via
spatie/laravel-permission (roles: `superadmin`, `admin`, `student`, `instructor` — see `App\Models\Role`
constants). No frontend in this repo beyond the Vite/Tailwind scaffold that ships with Laravel.

## Commands

This project runs via **Laravel Sail** (`docker-compose.yml`: `laravel.test`, `queue`, `mysql`
services) — `.env` has `DB_HOST=mysql`, the Docker service name, so `php artisan`/`composer` will fail
to reach the database unless run *inside* the container. Use `./vendor/bin/sail` (or the `sail` shell
alias, if set up) as the prefix for essentially every artisan/composer command instead of running them
bare on the host:

```bash
./vendor/bin/sail up -d              # start containers (laravel.test, queue, mysql)
./vendor/bin/sail down                # stop containers

./vendor/bin/sail artisan serve       # rarely needed — laravel.test already serves on :80/APP_PORT
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker
./vendor/bin/sail artisan make:repository Widget --all   # custom generators, see below

./vendor/bin/sail composer install
./vendor/bin/sail composer test       # config:clear + php artisan test (full suite)
./vendor/bin/sail artisan test --filter=testMethodName
./vendor/bin/sail artisan test tests/Feature/SomeTest.php

./vendor/bin/sail bin pint            # Laravel Pint, code style
./vendor/bin/sail npm run dev         # vite
```

`composer dev` (serve + `queue:listen` + `pail` logs + vite, all concurrently) is meant to run inside
the container too, e.g. `./vendor/bin/sail composer dev`, not on the host.

Tests run against a MySQL database named `testing` (see `phpunit.xml` — `DB_DATABASE=testing`), not
SQLite; `docker/mysql/create-testing-database.sh` creates it automatically when the `mysql` container
first initializes.

## Custom code generators

This repo has custom Artisan generators (`app/Console/Commands/Make*.php`, stubs in
`app/Console/Commands/stubs/`) that scaffold the Repository → Service → Controller stack used
throughout the app. Prefer these over `php artisan make:model/controller` when adding a new resource:

```bash
php artisan make:repository Widget --all      # repository + service + resource + controller
php artisan make:repository Widget --service --resource
php artisan make:repository Widget --repo-only
php artisan make:service Widget                # service only (wires up existing WidgetRepository if present)
php artisan make:base-repository               # (re)creates BaseRepository, rarely needed directly
```

`--all`/`--service` infer the model name by stripping the `Repository`/`Service` suffix and require
`App\Models\{Model}` to already exist.

## Architecture

Every domain resource follows the same four-layer pattern; when working on any feature, expect to
touch all four:

```
routes/api.php → Controller → FormRequest (validation) → Service → Repository → Model → Resource
```

- **Controllers** (`app/Http/Controllers`) are thin: they resolve a `FormRequest`, call one Service
  method, and return the result (often a Resource or Resource collection directly — no extra
  wrapping).
- **FormRequests** (`app/Http/Requests/{Resource}/{Action}.php`) hold all validation; `authorize()` is
  generally hardcoded `true` since authorization is handled separately (route-level/policy or
  implicit via role).
- **Services** (`app/Http/Services`) hold business logic and orchestrate one or more Repositories.
  They return Resources/arrays, not raw models, to controllers.
- **Repositories** (`app/Http/Repositories`) extend/compose `BaseRepository`, which provides generic
  CRUD (`findById`, `getAll` with filter/pagination, `create`, `updateById`, `deleteById`,
  `deleteManyById`, `deleteMorph`, etc.). Custom repository methods go alongside these for anything
  the base class can't express. `getAll()`'s filters are only applied for columns that actually
  exist on the model's table (`Schema::hasColumn` check) — the recent shift is away from raw SQL
  toward Eloquent query builder for these dynamic filters.
- **Resources** (`app/Http/Resources`) shape API responses.
- **Models** use `HasUuids` (string UUID PKs, not auto-increment ints) almost universally.

### Domain model

`current_database_schema.md` documents the schema and an ERD in detail and is generally reliable, but
is **stale on one point**: it still shows `assessment_versions` sitting between `assessments` and
`student_assessment_attempts`. That table/model was removed (see the `feat/remove-assessment-versioning`
merge) — `StudentAssessmentAttempt` now `belongsTo(AssessmentResult)` directly, and
`AssessmentResult belongsTo Assessment` / `hasMany StudentAssessmentAttempt`. Trust the models over the
doc for that relationship chain; the rest of the doc (institutional structure, content polymorphism)
still matches the code.

High-level shape:
- `User` hasOne of `Admin` / `Student` / `Instructor` (role-specific profile tables), plus
  spatie-permission roles/permissions.
- Institutional hierarchy: `Department → Program → Student`, `Department ↔ Course` (many-to-many),
  `Course → CourseClass ← Semester/Section`, with `ClassInstructorAssignment` /
  `ClassStudentEnrollment` as pivot-style join models.
- Content tree: `CourseClass → Chapter → ChapterContent`, where `ChapterContent` polymorphically
  wraps either a `Lecture` or an `Assessment` (`contentable` morph). `Lecture → LectureMaterial`
  (polymorphic `file_attachment`/`text_attachment`). `Assessment → AssessmentMaterial` (polymorphic
  `option_based_item` / `essay_item` / `identification_item`, each optionally paired with a
  `file_attachment`/`text_attachment` via further morphs), each `AssessmentMaterial` hasOne
  `AssessmentMaterialQuestion`.
- Assessment grading: `Assessment.answer_key` (JSON) drives auto-grading in
  `StudentAssessmentAttemptService::submitAttempt` — option-based and identification items are
  scored immediately; essay items are left with `points_earned = null` until an instructor grades
  them, which also nulls out the parent `AssessmentResult.final_score` until resolved. Multi-attempt
  assessments combine attempt scores via `Assessment.multi_attempt_grading_type`
  (`avg_score` vs. best/max).
- Two independent access-control concerns on assessments — see
  `.mds/assessment_accessibility_and_submission_settings_doc.md` for the full rules:
  - `ChapterContent.accessibility_settings` (JSON): whether a student can see/open the content at
    all. Shape is `{ "visible": true|false|null, "custom": null }` or
    `{ "visible": null, "custom": { "access_from": "...", "access_until": "..."|null } }` —
    `visible` and `custom` are mutually exclusive (one set, the other `null`). `access_from` is
    required inside `custom`; `access_until` of `null` means open-ended access starting from
    `access_from`. `accessibility_settings` itself is required (not nullable) — every
    `ChapterContent` must specify either `visible` or `custom`.
  - `AssessmentSubmissionSettings` (dedicated table, `Assessment::submissionSettings()` hasOne,
    eager-loaded via `$with`): `time_limit_seconds` (required, per-attempt cap) plus `due_date` +
    `after_due_date_behavior` (`auto_submit` / `block_new_attempts` / `allow_all`) governing attempt
    behavior once inside. `due_date` and `after_due_date_behavior` are coupled — both `null` or both
    set, never one without the other. `auto_submit` force-submits ongoing attempts at the due date
    and blocks new ones; `block_new_attempts` lets ongoing attempts finish but blocks new ones;
    `allow_all` blocks nothing past the due date.
  - These interact but are independent — a student can be able to *open* an assessment past its
    submission due date (if `accessibility_settings.custom.access_until` is later than
    `due_date`, or access is unrestricted) — what happens once inside then depends on
    `after_due_date_behavior`. All timestamps in both settings are stored/interpreted as UTC.
- `AutoSubmitExpiredAttempt` (queued job, dispatched via the `AutoSubmitExpiredAttempts` console
  command) force-submits attempts that have exceeded their time limit or due-date behavior — this is
  why the `queue` worker/service matters even in local dev.

### Auth

Session-based (`config/auth.php` guard is `session`, not `sanctum`/token), using `Auth::attempt` +
`$request->session()->regenerate()` in `AuthController`. The `api` middleware group only adds
`StartSession` (see `bootstrap/app.php`) — there's no `sanctum` guard in play despite the package
being installed. `UserResource` takes a `with_permissions` flag to include spatie role/permission
data on login/`me` responses.
