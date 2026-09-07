# Operations

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Optional asset build:

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

Current coverage includes:

- Admin-only authorization.
- Exercise difficulty validation.
- Publish workflow.
- Draft and hidden-test protection.
- Run vs submit activity behavior.
- Submission idempotency.
- Streak same-day, consecutive-day, missed-day, and duplicate-event behavior.
- Activity calendar totals, intensity, and today marker.
- Local judge accepted, wrong answer, compile error, timeout, and JavaScript execution.

## Local Judge

The local compiler runner is configured with:

```bash
JUDGE_PYTHON_BINARY=C:\msys64\ucrt64\bin\python.exe
JUDGE_NODE_BINARY=node
JUDGE_TIMEOUT_SECONDS=2
JUDGE_MAX_OUTPUT_BYTES=100000
```

On Windows, this project defaults to `C:\msys64\ucrt64\bin\python.exe` when present because the plain `python` command may be a Microsoft Store shim.

## Docker Direction

The first Laravel slice runs locally with PHP and SQLite. For a production-like setup, use MySQL 8+, Redis, queue workers, object storage, and a separate Judge0 or sandbox-runner deployment. Laravel should keep only runner job IDs and normalized verdicts.
