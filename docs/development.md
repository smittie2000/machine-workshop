# Local development

## Services

Docker Desktop with Linux containers/WSL 2 is the only required host runtime. PowerShell wrappers locate Docker by its installed path if the current shell PATH has not refreshed.

- web: Node 24, pnpm 11.19.0, Vite / TanStack Start, localhost:3000 only.
- api: PHP 8.4 and Composer 2; internal port 8000.
- db: PostgreSQL 17, internal port 5432, persistent named volume.

Source files bind-mount from Windows for editing. Dependency folders and database files use Docker volumes. Vite polling supports Windows file change detection. These are development containers, not production images.

The database password in Compose is a development-only credential and the database has no published host port. Laravel .env and generated application keys are ignored by Git. Do not reuse these settings in production.

## Commands

Run from the repository directory:

```powershell
.\dev.ps1 setup
.\dev.ps1 up
.\dev.ps1 check
.\dev.ps1 logs
.\dev.ps1 down
```

Setup builds images, installs dependencies, creates a Laravel key only for a new .env, starts PostgreSQL, and runs migrations. Check builds the web app before TypeScript validation so route types are generated, then runs simulation checks, the verifier fixture and Laravel tests. Down preserves data volumes. Never run compose down --volumes unless intentionally deleting local database and dependency data.

Only http://localhost:3000 is browser-facing. Vite proxies /api and /sanctum to Laravel during development. Production SSR hosting and the production reverse proxy are not implemented.

Laravel serve uses --no-reload so its child process preserves Docker-provided environment variables. PHP source edits remain visible per request. After changing environment values, recreate the API container with dev.ps1 up (use Docker Compose restart api for .env-only changes).

## Baseline scope

The development page displays API/database connectivity and runs a browser physics smoke check. The simulation package supplies a 120-tick fixture; the Node test compares 20 fresh runs at every tick. This is not yet a cross-browser determinism certification or a submission verifier.

The editor Store factory establishes request-safe ownership but does not implement editing or undo. PixiJS is installed for the editor milestone. Charts will be evaluated and pinned when actual leaderboard views are built.

Laravel skeleton source: official laravel/laravel branch 13.x, commit aa0cf127fc365a56ee016867144ddffabc2290ae. Its MIT license declaration is retained in apps/api/composer.json. The upstream skeleton's Git metadata is not nested in the application.

## Docker first-start troubleshooting

If Docker remains in Starting and requests return engine HTTP 500, use Docker Desktop's normal Restart action and wait for Running before rerunning setup. Do not reset Docker data or unregister its WSL distribution. The initial installation here required one normal restart; no existing Docker data was deleted.

The PowerShell wrapper adds Docker's helper directory to the current process PATH so freshly installed credential helpers work without restarting the terminal. It does not modify the machine/user PATH or install application language runtimes on Windows.

## Verification status

Verified on 2026-09-05:

- Docker Desktop 4.89.0 installed and running with the WSL 2 Linux engine.
- Both application images built; dependencies installed inside containers with pnpm and Composer lockfiles retained.
- PHP 8.4.25, Node 24.20.0 in the containers; Laravel 13.30.1 and Rapier 0.20.0.
- PostgreSQL initial users, cache and jobs migrations completed.
- Compose reports db, api and web healthy.
- HTTP GET http://localhost:3000 returns 200 and the application title.
- HTTP GET http://localhost:3000/api/health returns status=ok and database=connected through the Vite proxy.
- Container client/SSR build and TypeScript checks passed.
- Rapier per-tick repeatability test passed over 20 fresh worlds in the Linux container and separately on Windows.
- Windows and Linux Node fixture digests match: 2cfca09497b519757c35c68ca767ec6e59d5ba6d9c22fbce1eecac4c562148f7.
- Laravel tests: 4 passed, 8 assertions. Health success/failure behaviour is covered with database mocks; the live health endpoint independently verifies PostgreSQL connectivity.
- Laravel Pint applied to changed PHP files. Laravel Boost installed as a development dependency with generated guidelines.
- Compose configuration and PowerShell parser validation passed.

Limitations: browser interaction and Chromium/Firefox/WebKit agreement have not been tested. The browser button is supplied for a local smoke check. The fixture is deliberately small and is not a certification for complex puzzles. Vite reports a large lazy-loaded Rapier compatibility chunk (about 803 kB gzip); loading and bundle optimisation belong to the playable-prototype milestone. No production deployment, user authentication, editor or public sharing has been implemented.

Use dev.ps1 down to stop the local services while preserving data. Project repository: https://github.com/smittie2000/machine-workshop.
