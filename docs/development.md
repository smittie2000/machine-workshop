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
.\dev.ps1 generate
.\dev.ps1 check
.\dev.ps1 logs
.\dev.ps1 down
```

Setup builds images, installs dependencies, creates a Laravel key only for a new .env, starts PostgreSQL, runs additive migrations, seeds the immutable workshop catalogue, and generates contracts. Repeating setup verifies the sealed seed matches; it never replaces released definitions. Existing prototype-1 database rows are retained, but fresh setup no longer creates them. Down preserves data volumes. Never run compose down --volumes unless intentionally deleting local database and dependency data.

Generate runs Spatie TypeScript Transformer and Wayfinder in the PHP container, clearing route cache first. Run it after changing DTOs/controllers/routes and include all generated output and the Spatie manifest in the change. Check regenerates and fails if any generated TypeScript or manifest changes, then builds before typechecking, runs the existing simulation/verifier checks and the PostgreSQL Laravel suite. The GitHub check also compares regeneration against the checkout. No PHP runs in the web container.

Laravel database tests use a separate PostgreSQL service, `test-db`, with database/user `workshop_test` and temporary filesystem storage. Check starts it automatically. PHPUnit explicitly overrides both environment and server variables; the base test class checks the connection before database refresh traits run. Never point these tests at `db`/`workshop`. The development PostgreSQL volume is not mounted in `test-db`.

For a targeted test after starting the test service with check, use `./dev.ps1 docker compose run --rm --no-deps api php artisan test --compact --filter=CatalogueTest`. The docker action forwards arguments through the same Docker executable discovery. All PHP/Composer/pnpm commands still run in containers.

## Welcome screen and basketball test

`/` is the welcome screen; `/prototype` is the basketball-on-brick test. React owns navigation, status, controls and observations; PixiJS draws the two original SVG assets and Rapier simulates the catalogue-defined bodies. Play, pause/resume and reset are implemented. Reset discards the world without changing the document. Hiding the page pauses the run and clears presentation time. Returning does not automatically resume it. A run is capped at the existing 3600 ticks; its observations are not a score.

Routes render named feature screens. `src/components` contains the shared header, footer and PartArtwork mapping. `src/features/prototype` contains the screen, controls and canvas lifecycle. The artwork is in `apps/web/public/assets/parts`. Only basketball and brick artwork is implemented; adding wood/rubber rendering follows the same explicit mapping. The full editor, changing placements and local save remain future work.

The dedicated `GET /api/v1/prototypes/basketball-brick` endpoint serves a validated placement document from PHP config. The empty sandbox starter remains unchanged. Catalogue coefficients and dimensions come from the released API catalogue, never the images or React constants. The new adapter in `packages/simulation/src/material-simulation.ts` implements the existing `material-demo-1` numerical recipe without upgrading Rapier or changing coefficients. The old smoke fixture remains a separate regression test.

`dev.ps1 generate` now also runs `workshop:export-simulation-fixture`. This reads the seeded development database and exports the validated document and catalogue through Laravel DTO serialization to `packages/simulation/fixtures/basketball-brick.json`. Start and seed the development database before generation. Commit this fixture; never edit it by hand. `dev.ps1 check` and CI detect generated fixture drift. The Laravel feature test compares the fixture with the seeded API response.

Verified 2026-09-05: Docker build/typecheck, five Node tests (including 20 identical fresh basketball/brick runs, rebound, synthetic 30/60/144 Hz schedules, disposal, reset and boundary failures), the original verifier smoke fixture, and 103 Laravel tests / 280 assertions passed. Pint passed. In-app Chromium checks covered welcome/test navigation, initial height, play/pause/reset, and desktop plus 390 px layouts; no console errors were observed. This is not cross-browser or native Windows/Linux determinism certification. Firefox/WebKit, material ordering across all three platforms, and browser state digests remain unverified. The existing Rapier and new lazily loaded Pixi chunks trigger size warnings; neither library loads on the welcome screen until the test route needs it.

Implementation references: [PixiJS SVG assets](https://pixijs.com/8.x/guides/components/assets/svg) and [Rapier collider restitution](https://rapier.rs/docs/user_guides/javascript/collider_restitution/). Existing package versions and the reviewed numerical recipe remain pinned.

## Phase A catalogue and contracts

Workshop catalogue revision verified on 2026-09-05: `dev.ps1 check` passed with 102 Laravel tests / 272 assertions, unchanged regenerated contracts, web build/typecheck, the existing 20-world smoke test and Node verifier fixture. Pint passed for changed PHP files. Default seeding installed workshop-1 without rewriting existing releases. Live API checks returned the four expected part keys and an empty sandbox referencing workshop-1. Tests cover default seed idempotence, absence of prototype-1 on a fresh database, removal of the demo starter route, and adding a variant in a new release while preserving the original. The existing large Rapier bundle warning remains; no new physics or cross-platform determinism verification is implied.

Phase A is implemented; the editor is not. `GET /api/v1/catalogues/workshop-1` returns four materials and four reusable parts. `GET /api/v1/starters/sandbox` returns an empty sandbox referencing that release; the material-demo starter route was removed. `POST /api/v1/documents/validate` accepts `{ "document": ... }` and returns the accepted document with HTTP 200. Validation is guest-accessible, stateless, limited to 60 requests/minute/IP and 256 KiB per request. It accepts sandbox documents only: empty inventory/locked IDs and null goal. Unknown keys, invalid nested values, duplicate instance IDs and unapproved release/part references yield Laravel 422 errors. It preserves integer transforms and array order.

`WorkshopOneSeeder` is the reviewed definition source. `ImportCatalogue::handle($definition, release: false)` imports a complete draft; omitting the flag seals it after validation. Subsequent imports of a sealed release must match exactly. Model save/delete operations lock the catalogue and reject sealed writes, including stale loaded models. Model saves reuse DTO validation for raw attributes; PHP enum casts also reject unsupported identifiers. Numeric increment helpers are deliberately refused. Do not use bulk query-builder updates, inserts, deletes or upserts for catalogue maintenance: those bypass Eloquent validation and release guards. PostgreSQL enforces column types/nullability, uniqueness and same-release material membership. Shape/mass/coefficient rules and supported identifiers belong to Laravel, not database CHECK constraints.

The create-table migrations contain only the relational schema; there is no legacy compatibility migration. The development database was explicitly reset and reseeded at the user's request after this design revision. Normal setup still uses migrate, not migrate:fresh.

The DTO package is consumed as `@workshop/contracts` (and `/enums`). The browser fetch/Query adapter is in `apps/web/src/lib/api.ts`; it uses relative Wayfinder URLs and exact release query keys. It is not an SSR prefetch adapter or a frontend runtime validator. Restore local documents through the validation endpoint when implementing phase C.

Installed versions are locked: Laravel Data 4.23.0, Laravel TypeScript Transformer 3.3.0 (core 3.3.1), Wayfinder 0.1.21. Existing Laravel/PHP/physics dependencies were not upgraded. Spatie collection allowed-key rules are added through Laravel's validator after nested DTO rules expand; replacing the collection wildcard would discard its nested rules.

Initial phase-A validation on 2026-09-05: `dev.ps1 check` passed (97 Laravel tests / 252 assertions, generated output unchanged, web build/typecheck, 20 fresh smoke worlds and Node verifier fixture). Tests ran against PostgreSQL 17 and PHP 8.4.25 in Linux containers on Docker Desktop. The build retains the existing large Rapier chunk warning.

After removing the compatibility migration, Docker-backed `migrate:fresh --seed` completed successfully on the development database as explicitly requested. The Laravel suite passed against the separate test database: 102 tests / 258 assertions. Pint passed, and the live catalogue endpoint returned four materials and four parts. Catalogue validation covers direct model saves, immutable releases, new variants without schema changes and retained FK/unique constraints.

Phase-A historical limits: GitHub-hosted CI was configured but did not run in the local session. At that gate the shared package implemented only its original smoke fixture. The welcome/test work above subsequently adds the material adapter and Node rebound/schedule evidence. Native Windows Node and Chromium/Firefox/WebKit digest comparisons remain unverified. Do not use catalogue validation as proof of successful physics replay.

Only http://localhost:3000 is browser-facing. Vite proxies /api and /sanctum to Laravel during development. Production SSR hosting and the production reverse proxy are not implemented.

Laravel serve uses --no-reload so its child process preserves Docker-provided environment variables. PHP source edits remain visible per request. After changing environment values, recreate the API container with dev.ps1 up (use Docker Compose restart api for .env-only changes).

## Baseline scope

The original development status page has been replaced by the welcome screen and interactive test described above. The simulation package retains its 120-tick smoke fixture in addition to the material adapter tests. This is not yet a cross-browser determinism certification or a submission verifier.

The editor Store factory establishes request-safe ownership but does not implement editing or undo. PixiJS is installed for the editor milestone. Charts will be evaluated and pinned when actual leaderboard views are built.

Laravel skeleton source: official laravel/laravel branch 13.x, commit aa0cf127fc365a56ee016867144ddffabc2290ae. Its MIT license declaration is retained in apps/api/composer.json. The upstream skeleton's Git metadata is not nested in the application.

## Docker first-start troubleshooting

If Docker remains in Starting and requests return engine HTTP 500, use Docker Desktop's normal Restart action and wait for Running before rerunning setup. Do not reset Docker data or unregister its WSL distribution.

The PowerShell wrapper adds Docker's helper directory to the current process PATH so freshly installed credential helpers work without restarting the terminal. It does not modify the machine/user PATH or install application language runtimes on Windows.

## Verify your setup

After running setup and up:

1. Open http://localhost:3000 and confirm the development page loads.
2. Check http://localhost:3000/api/health. A healthy response contains status=ok and database=connected.
3. Run dev.ps1 check to build the frontend, check TypeScript, run the 20-world physics repeatability test, execute the headless fixture and run the Laravel tests.
4. Open `/prototype` and use Drop the ball, Pause/Resume and Reset to check the browser test.

The PHP health tests cover success and failure responses with database mocks. The live health endpoint checks the actual PostgreSQL connection. Dependency versions are recorded in the pnpm and Composer lockfiles; runtime versions are selected by the Dockerfiles.

The fixture is deliberately small and does not certify complex puzzles or agreement across Chromium, Firefox and WebKit. Rapier is loaded separately from the initial page; loading and bundle optimisation belong to the playable-prototype milestone. Production deployment, user authentication, editing and public sharing are not implemented yet.

Use dev.ps1 down to stop the local services while preserving data.
