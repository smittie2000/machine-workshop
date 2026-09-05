# Project instructions

This is a local Docker development project. Preserve the agreed Laravel API / PostgreSQL / TanStack Start / PixiJS / Rapier stack. Do not deploy or replace it with a hosted-site starter.

Read docs/01-product-design.md and docs/02-architecture.md before changing ownership boundaries. Use dev.ps1 for Docker commands on Windows. PHP, Composer and JavaScript tooling belong in containers; do not install host runtimes as a workaround.

The accepted design is in docs/01-product-design.md through docs/06-implementation-handoff.md. Start implementation with phase A in docs/05-implementation-plan.md. The controlling constraint is "Only glue code and product rules." Do not build custom physics, a generic ECS/editor framework, schema/code generators or duplicated PHP/TypeScript contracts. Use Eloquent, Spatie Laravel Data/TypeScript Transformer and Wayfinder; the latter packages are selected but not installed at the design handoff.

Laravel owns catalogue definitions. Materials and parts have explicit relational fields within immutable catalogue releases; puzzle JSON stores placements and exact catalogue references. Players may place/rotate approved variants, never freely resize or change physical coefficients. Keep generated types separate from the shared simulation's implementation rules. These decisions supersede earlier proposals for a TypeScript-owned catalogue.

Laravel owns authentication, authorization and durable business rules. Start must not connect directly to PostgreSQL. The simulation package must remain independent of React, DOM, Laravel and wall-clock time. Browser scores are provisional until independently verified.

Use the root pnpm lockfile and Laravel Composer lockfile. Changes to physics versions or numerical rules require explicit simulation versioning and determinism fixture review. Keep drafts separate from running worlds and create a fresh world for each run.

Run relevant checks from dev.ps1 check. Frontend route generation occurs during build before typechecking. Do not claim cross-platform determinism based solely on the Node smoke test. Record environment limitations in docs/development.md.

Never delete Docker data volumes as a repair step without explicit user authorization. Never commit .env, tokens or generated application keys.
