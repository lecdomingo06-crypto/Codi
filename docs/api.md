# API And Route Contract

The current Laravel MVP uses session auth for both web pages and JSON responses.

## Auth

- `GET /register`
- `POST /register`
- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /dashboard`
- `GET /profile`
- `PATCH /profile`

## Learner

- `GET /catalog`
- `GET /courses/{course:slug}`
- `POST /courses/{course:slug}/enroll`
- `GET /lessons/{lesson:slug}`
- `POST /lessons/{lesson:slug}/complete`
- `GET /exercises/{exercise:slug}`
- `POST /exercises/{exercise:slug}/run`
- `POST /exercises/{exercise:slug}/submit`
- `GET /submissions/{submission}`
- `GET /progress`
- `GET /api/v1/me/streak`
- `GET /api/v1/me/activity-calendar?from=YYYY-MM-DD&to=YYYY-MM-DD`
- `GET /api/v1/courses`

## Admin

All admin routes require an authenticated `ADMIN` user.

- `GET /admin`
- `GET /admin/courses`
- `POST /admin/courses`
- `GET /admin/courses/create`
- `GET /admin/courses/{course}/edit`
- `PATCH /admin/courses/{course}`
- `GET /admin/lessons`
- `POST /admin/lessons`
- `GET /admin/lessons/create`
- `GET /admin/lessons/{lesson}/edit`
- `PATCH /admin/lessons/{lesson}`
- `GET /admin/exercises`
- `POST /admin/exercises`
- `GET /admin/exercises/create`
- `GET /admin/exercises/{exercise}/edit`
- `PATCH /admin/exercises/{exercise}`
- `GET /admin/exercises/{exercise}/versions/{version}/preview`
- `POST /admin/exercises/{exercise}/versions/{version}/publish`
- `POST /admin/exercises/{exercise}/archive`
- `GET /admin/exercises/import`
- `POST /admin/exercises/import`
- `GET /admin/users`
- `GET /admin/analytics`
