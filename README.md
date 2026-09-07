# Coddy Laravel MVP

This is a Laravel conversion of the unified interactive coding-learning platform brief. The current slice emphasizes backend structure over UI polish.

## What Is Implemented

- Session authentication with exactly two roles: `ADMIN` and `USER`.
- Admin course, lesson, and exercise authoring.
- Exercise draft versions, preview, publish, archive, and JSON import.
- Required `EASY`, `MEDIUM`, or `HARD` difficulty on exercises and versions.
- Visible and hidden test-case storage.
- Learner catalog, enrollment, lesson completion, run, submit, and submission history.
- LeetCode-style split exercise workspace with editor-like textarea and visible-test auto-check.
- Built-in local compiler runner for Python and JavaScript using separate OS processes.
- Idempotent submission workflow.
- Daily answer activity, current streak, longest streak, and yearly heatmap data.
- Seeded admin/user accounts and one complete published exercise slice.
- Feature/unit tests for authorization, publishing, privacy, streaks, and calendar data.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Optional assets:

```bash
npm install
npm run build
```

## Seeded Accounts

- Admin: `admin@example.com` / `password`
- User: `user@example.com` / `password`

## Tests

```bash
php artisan test
```

## Docs

- [Product requirements](docs/product-requirements.md)
- [Architecture notes](docs/architecture.md)
- [API and route contract](docs/api.md)
- [Operations](docs/operations.md)

## Local Compiler

The MVP now runs Python and JavaScript locally through `App\Services\JudgeService`. It writes temporary runner files under `storage/app/judge`, executes code in a separate process, enforces a timeout, compares visible or hidden tests on the server, and cleans up afterward.

Useful settings:

```bash
JUDGE_PYTHON_BINARY=C:\msys64\ucrt64\bin\python.exe
JUDGE_NODE_BINARY=node
JUDGE_TIMEOUT_SECONDS=2
JUDGE_MAX_OUTPUT_BYTES=100000
```

This is good for local development, but it is not a production sandbox. For deployment, move execution to Judge0 or a locked-down container runner with CPU, memory, filesystem, process, and network isolation. The current editor is dependency-free; Monaco or CodeMirror can be added next.
