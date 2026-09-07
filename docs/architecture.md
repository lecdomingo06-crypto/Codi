# Architecture Notes

## Laravel Modules

The MVP uses a Laravel modular monolith:

- `app/Http/Controllers/AuthController.php` handles registration, login, logout.
- `app/Http/Middleware/EnsureRole.php` enforces server-side role checks.
- `app/Http/Controllers/Admin/*` handles course, lesson, exercise, import, preview, publish, and archive workflows.
- `app/Http/Controllers/ExerciseWorkspaceController.php` handles learner run/submit/result workflows.
- `app/Services/JudgeService.php` runs local Python/JavaScript code in separate OS processes and normalizes verdicts behind a replaceable adapter.
- `app/Services/StreakService.php` records idempotent qualifying answers and updates streaks.
- `app/Services/ActivityCalendarService.php` builds yearly contribution-style calendar data.

## Data Model

The migrations include the core entities from the brief:

- Users with `ADMIN` or `USER` role, status, timezone, and points.
- Courses, course versions, modules, lessons, lesson versions, activities.
- Concepts and concept prerequisites.
- Exercises, immutable exercise versions, exercise concepts, starter code, hints, official solutions.
- Test bundles and visible/hidden test cases.
- Enrollments, lesson progress, attempts, runs, submissions, test results.
- Daily exercise activity, user streaks, achievements, learning events, and audit logs.

Published exercise versions are treated as immutable. Editing an exercise creates a new draft version; publishing sets the exercise `active_version_id`.

## Security Posture

- Admin authorization is enforced by middleware, not by hiding buttons.
- Learner exercise access requires `publication_status = PUBLISHED` and an active version.
- Hidden test data stays in `test_cases` and is not sent to learner views.
- The MVP judge executes learner code only through short-lived local OS processes with timeouts and temporary workspaces.
- Submission idempotency is enforced by a unique `submissions(user_id, idempotency_key)` constraint and learning-event idempotency.
- Local activity dates are stored as immutable date strings to avoid timezone rewrites.

## Judge Adapter

`JudgeService` writes temporary files under `storage/app/judge`, runs Python or Node, compares outputs against server-side tests, cleans up the workspace, and returns normalized verdicts:

- `ACCEPTED`
- `WRONG_ANSWER`
- `COMPILE_ERROR`
- `RUNTIME_ERROR`
- `TIME_LIMIT_EXCEEDED`

This local runner is for development. It is not equivalent to Judge0, gVisor, Firecracker, or container isolation. The class remains the replacement point for a future production judge client. The future client should send job identifiers and published version/test-bundle identifiers to a separate worker, never hidden test data to the browser.
