# Implementation handoff

## Start here

Read in order:
1. [Accepted product decisions](01-product-design.md)
2. [Framework ownership and lifecycle](02-architecture.md)
3. [Migrations, models and DTO contract](03-puzzle-contract.md)
4. [Material prototype](04-material-prototype.md)
5. [Implementation sequence](05-implementation-plan.md)

These documents supersede earlier conversation suggestions, particularly the TypeScript-owned catalogue and arbitrary per-instance material settings.

## Accepted decisions

- **Only glue code and product rules.** Use framework/package/native features; do not invent systems to maintain.
- Players place/rotate approved parts; no arbitrary resizing or editing physical coefficients.
- Materials are explicit relational columns. Brick/wood/rubber platforms are catalogue part variants.
- Laravel/Eloquent owns definitions and persistence. One immutable release owns its material/part set and exact simulation recipe.
- Laravel DTO rules own catalogue bounds, shape/mass requirements and supported identifiers; ordinary model saves reuse them. Database migrations retain relational/unique constraints, with no catalogue product CHECK constraints. New approved variants using existing fields do not require migrations.
- Contributions arrive through reviewed GitHub seed/art changes. No player catalogue editor.
- Puzzle documents use typed JSONB with integer placements and exact catalogue references.
- Spatie Laravel Data defines PHP contracts; TypeScript Transformer generates frontend types; Wayfinder generates Laravel API route helpers.
- Query owns server records, Store owns editing drafts, Rapier owns running worlds, PixiJS draws.
- Published versions are immutable. Ranked puzzle publication needs a private verified example solution.
- Guests browse/play/experiment/save locally. Accounts are required for cloud saves, publishing and ranking.
- First playable slice is an empty sandbox with a basketball and three fixed material platform variants in its palette, using PixiJS primitives. No SVG/Blender decision blocks it.

## Current repository state

Welcome and bounce-test slice: `/` renders the welcome screen; `/prototype` renders a basketball falling/bouncing on brick with play/pause/reset. Shared original SVG assets and named React components establish the presentation conventions in AGENTS.md. The simulation package now contains a catalogue-driven material recipe adapter and generated Laravel fixture with Node tests. This does not complete the editor/local-save milestone or cross-browser determinism review. See development.md for the latest checks.

Catalogue convention revision: `WorkshopOneSeeder` replaces `PrototypeOneSeeder`. It seeds `workshop-1` with basketball and brick/wood/rubber-mat platforms; `starters/sandbox` returns an empty document. Demo placements are removed from product configuration. Use the contribution procedure in 03-puzzle-contract.md and root AGENTS.md for future parts. Earlier three-lane product requirements below are superseded; the physical recipe and deferred simulation work remain unchanged.

Phase A has now been implemented and its Docker-backed checks passed. Catalogue models/migrations, safe release seeding, DTO validation, public read/validation endpoints, generated contracts/Wayfinder helpers and the browser fetch/Query adapter exist. See [development](development.md) for commands, test isolation and acceptance evidence. Continue with phase B before the editor; the original smoke fixture is not the material recipe implementation.

The paragraphs below describe the original design-only handoff baseline.

Docker Compose, Laravel API/PostgreSQL health endpoint, Start development page, Store factory and a small shared Rapier/Node smoke fixture exist. Frontend builds/typechecks and the original smoke tests passed during setup; no new runtime testing is implied by this design-only commit.

The catalogue tables/models/DTOs, Spatie packages, Wayfinder, authentication, editor, local draft persistence, publication and ranking remain to be implemented. packages/contracts currently contains only a README. Do not mistake the development page or fixture for the planned prototype.

Use the root dev.ps1 setup/up/check/logs/down commands. Preserve Docker volumes and secrets. API source has its own AGENTS.md. The frontend lives in apps/web; ignore unused Laravel starter frontend scaffolding when choosing frontend tooling.

## Suggested prompt for a new implementation conversation

> Implement phase A of docs/05-implementation-plan.md for Machine Workshop. First read AGENTS.md and docs/01-product-design.md through docs/06-implementation-handoff.md, plus applicable API instructions. The accepted constraint is “Only glue code and product rules.” Use Docker and normal Laravel migrations/Eloquent, Spatie Laravel Data, generated TypeScript contracts and Wayfinder. Implement the catalogue schema, release-specific seed/import rules, DTOs, read/validation endpoints and generation workflow with meaningful tests. Do not build the PixiJS editor, auth or leaderboards yet. Preserve released definitions, the existing stack and Docker data. Report the phase-A acceptance results and remaining work before moving on.

The local checkout path is intentionally not embedded in public documentation. Open this repository as the project for the new conversation.

## Verification for this planning change

No migrations, dependencies or runtime code were changed. Review checks cover consistent product decisions, relative links, JSON example syntax, no workstation-specific documentation and clean Git diff. Implementation should run its relevant Docker-backed checks and PostgreSQL tests when code is added.
