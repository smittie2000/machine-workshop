# Project instructions

This is a local Docker development project. Preserve the agreed Laravel API / PostgreSQL / TanStack Start / PixiJS / Rapier stack. Do not deploy or replace it with a hosted-site starter.

Read docs/01-product-design.md and docs/02-architecture.md before changing ownership boundaries. Use dev.ps1 for Docker commands on Windows. PHP, Composer and JavaScript tooling belong in containers; do not install host runtimes as a workaround.

Laravel owns authentication, authorization and durable business rules. Start must not connect directly to PostgreSQL. The simulation package must remain independent of React, DOM, Laravel and wall-clock time. Browser scores are provisional until independently verified.

Use the root pnpm lockfile and Laravel Composer lockfile. Changes to physics versions or numerical rules require explicit simulation versioning and determinism fixture review. Keep drafts separate from running worlds and create a fresh world for each run.

Run relevant checks from dev.ps1 check. Frontend route generation occurs during build before typechecking. Do not claim cross-platform determinism based solely on the Node smoke test. Record environment limitations in docs/development.md.

Never delete Docker data volumes as a repair step without explicit user authorization. Never commit .env, tokens or generated application keys.
