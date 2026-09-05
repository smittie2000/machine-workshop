# Implementation sequence and acceptance gates

Status: phase A implemented and checked; see [development acceptance evidence](development.md#phase-a-catalogue-and-contracts). B and later phases remain implementation work. Do not jump to the canvas or build future milestones at once.

User-requested welcome/test slice is implemented: named React screens, shared basketball/brick SVGs, a dedicated two-part prototype document, and the material recipe adapter with Node rebound/repeatability coverage. This implements part of B and a bounded test surface, not the full C editor. Cross-browser digests, three-material ordering, editing and local save remain outstanding. See development.md for evidence and conventions before continuing.

Catalogue revision: use `WorkshopOneSeeder` and the empty sandbox starter as the current foundation. Keep any material-comparison fixture in tests when phase B implements it. Follow the [explicit contribution conventions](03-puzzle-contract.md#repeatable-procedure-for-adding-parts) when adding parts.

## A — Laravel catalogue and generated contracts

1. Inspect existing Docker/runtime versions. Install compatible Spatie Laravel Data, Spatie Laravel TypeScript Transformer and Laravel Wayfinder inside the API container. Use package-provided commands/configuration. Pin lockfiles; do not introduce Inertia.
2. Generate the three catalogue migrations, Eloquent models, enums and initial DTOs with normal Laravel tooling. Implement the explicit schema from 03-puzzle-contract.md, including same-release FKs and uniqueness. Keep bounds, supported identifiers and shape/mass requirements in Laravel DTO rules reused by imports and ordinary model saves; no catalogue product CHECK constraints.
3. Add the small catalogue import/release action and release-specific seeder using Laravel transactions/locks. Add the four initial materials/parts for workshop-1 with the reviewed defaults and explicit seed structure. Put the empty sandbox starter in the small PHP config specified in the domain contract, referencing that release; never mutate a released definition on subsequent setup.
4. Expose the released catalogue, starter and stateless document validation controller endpoints. Use DTO validation/serialization and named routes. Rate-limit validation; no database write or authentication required for this endpoint.
5. Generate TypeScript contracts and Wayfinder helpers into the agreed monorepo locations from PHP. Add an explicit root Docker-backed generation command and integrate generation before build/check. Keep generated files portable and committed.
6. Add a small typed fetch adapter and Query options for catalogue/starter/validation. Do not build editor features yet.

Tests: PostgreSQL-backed relational/unique constraints (including cross-release material references), DTO validation of product rules on imports and ordinary model saves, new variants without schema changes, expected seed content, safe repeated seed, refusal of released edits, unreleased catalogue not public, coefficient number serialization, valid/invalid nested document requests, unknown fields, generated types compiling and regeneration producing no diff. Use a separate test database for database-mutating tests; never RefreshDatabase against the development database. Keep existing smoke checks.

Gate: Laravel returns real catalogue data, generated contracts compile in the frontend, and a document can be validated without handwritten duplicate interfaces. Review this gate before expanding scope.

## B — Shared material simulation

Implement the explicit supported ball/cuboid adapter, recipe and fixture in packages/simulation. Accept resolved DTOs, not HTTP clients. Replace the hardcoded smoke-world fixture with data driven by the agreed catalogue contract while retaining a simple regression fixture.

Export a catalogue/starter test fixture through Laravel's DTO serialization for Node/browser tests; do not manually maintain an unrelated second material list in TypeScript. Keep the exported test fixture generated and committed alongside its source/release reference.

Gate: fixed-step repeatability, material rebound ordering, reset cleanup and render-schedule independence. The same module runs in Node and the browser. No custom collision math or physics integration.

## C — Small editor and local save

Use Start/React controls, a per-editor TanStack Store and PixiJS. Wire Query data -> validated draft -> world -> render. Use integer transform fields, native pointer input, structuredClone snapshots and localStorage.

One completed placement/rotation/duplicate/delete action creates one history entry, capped at 100; selection doesn't. This small bounded snapshot behaviour is sufficient. Do not add a generic command framework or another state library. Local save needs explicit feedback; malformed/stale local drafts must be validated before running. API unavailable means keep the draft and show a retry state, never silently substitute catalogue data.

Gate: empty sandbox, palette with the four approved parts, placement/rotation changes, Reset preserving draft, save/reopen round-trip, browser lifecycle cleanup and cross-browser fixture checks. Material rebound comparisons belong to simulation tests, not required starter placements. This is the first playable slice.

## D — Accounts and cloud drafts

Use headless Laravel Fortify + Sanctum sessions, policies, standard rate limits and same-origin proxying. Add creations and DTOs; support expectedRevision updates. Query mutation response handling must preserve edits made during save. Guests retain local access. No fake authenticated user or public unauthenticated write endpoint as an interim shortcut.

Gate: ownership enforced server-side, CSRF protected writes, clean 401/403/409/422 behaviour, local-to-cloud save and logout cache handling.

## E — Publishing and prepared puzzles

Add publications and private publication solutions. Freeze published snapshots/catalogue references. Enable the already specified goal DTO and constrained inventory/fixed scenery; introduce no general goal language. Use Laravel's queue and the shared Node runner to verify ranked example solutions before exposing those publications.

Gate: immutable version URLs, private solution not serialized publicly, legitimate remix, invalid scenery/inventory edits rejected and at least three prepared puzzles authored with the same editor.

## F — Verified submissions and rankings

Add submissions with unique idempotency keys and bounded queue processing. Initial limits: 256 KiB input, 200 parts, 3600 ticks, one verifier job at a time, 30-second process timeout and 512 MiB worker-container memory. Tune operational limits based on measurements; changing these cannot alter accepted simulated scores. Treat timeout as error, not verified completion. Capture at most 64 KiB of result/diagnostic output and reject oversized output rather than accumulating unbounded process logs.

Use Symfony Process argument arrays and JSON stdin/stdout. The worker selects an allowlisted recipe; it does not execute user code. Build the queue-worker image with PHP and Node as necessary rather than adding a service framework.

Gate: replay cannot trust client scores or scenery, retries don't duplicate acceptance, worker errors don't rank, scores use exact-version ordering, readable standings table. Add TanStack chart integration only for an actual chosen chart.

## Implementation discipline

- Every new abstraction must name the concrete framework capability it connects or the product rule it implements.
- Prefer ordinary Eloquent, DTOs and explicit functions over service/repository scaffolding.
- Avoid prebuilding shape registries, scripting APIs, custom serializers, synchronization engines or asset processors.
- Tests verify observable rules and failure paths, not getters/setters or code-shaped scaffolding.
- Regenerate contracts whenever PHP DTO/routes change. Never patch generated TS to make a build pass.
- Document engine/recipe changes. Never silently change data underlying a released catalogue.
- Later mechanics may need new product decisions; they are explicitly out of scope, not blockers for A–C.

## Framework references

These identify the package features the design relies on. Installation must use documentation for the versions actually resolved.

- [Laravel Data Eloquent casts](https://spatie.be/docs/laravel-data/v4/advanced-usage/eloquent-casting)
- [Laravel Data validation](https://spatie.be/docs/laravel-data/v4/validation/introduction)
- [Spatie Laravel TypeScript Transformer setup](https://spatie.be/docs/typescript-transformer/v3/laravel/installation-and-setup)
- [Laravel Data TypeScript integration](https://spatie.be/docs/typescript-transformer/v3/laravel/laravel-data)
- [Wayfinder route helper generation](https://github.com/laravel/wayfinder)
- [TanStack Query](https://tanstack.com/query/latest/docs/framework/react/overview)
- [TanStack Store](https://tanstack.com/store/latest/docs/quick-start)
- [Rapier colliders](https://rapier.rs/docs/user_guides/javascript/colliders/)
- [Rapier determinism](https://rapier.rs/docs/user_guides/javascript/determinism/)
