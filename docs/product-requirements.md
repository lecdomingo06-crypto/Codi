# Product Requirements

## Scope

This repository implements the unified coding-learning platform brief as a Laravel MVP. The first slice focuses on a secure content-to-submission loop:

- Admin signs in, creates course/lesson/exercise content, versions exercise drafts, previews, publishes, archives, and imports exercise JSON.
- User registers, signs in, browses published catalog content, enrolls, reads lessons, runs visible tests, submits against hidden tests, views results, and tracks progress.
- The exercise workspace uses a split problem/editor layout with non-persistent auto-check against visible tests.
- The system supports exactly two roles: `ADMIN` and `USER`.

## MVP Assumptions

- Laravel Blade is used for functional scaffolding. UI polish is intentionally deferred.
- The default local judge executes Python and JavaScript in separate OS processes with a timeout. It keeps hidden tests server-side and keeps the workflow replaceable by Judge0 later.
- The MVP editor is a styled textarea with auto-check. A full editor engine such as Monaco or CodeMirror is deferred.
- SQLite works for local/test development. The schema is written with MySQL-compatible table structure, constraints, indexes, and JSON fields.
- Password auth uses Laravel session authentication and hashed passwords.

## Required Rules Covered

- Every exercise requires one non-null difficulty: `EASY`, `MEDIUM`, or `HARD`.
- Users can only access published exercises with an active published version.
- Hidden test inputs and expected outputs are not rendered in learner workspace or submission pages.
- Admin routes are protected by server-side role middleware.
- Submissions use idempotency keys to avoid duplicate answer/streak inflation.
- Daily activity is stored as one row per user and immutable local activity date.
- Current and longest streaks update from qualifying non-empty submissions, not from runs.
- The yearly activity calendar returns a GitHub-style week grid with five intensity levels.

## Deferred

- Full Monaco editor integration.
- Real isolated code execution through Judge0 or disposable containers.
- WebSockets/SSE live status updates.
- Rich analytics dashboards.
- Account deletion/export workflows.
- Full OpenAPI generation.
- Polished responsive UI.
