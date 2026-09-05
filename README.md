# Machine Workshop

A fun project recreating the spirit of **The Incredible Machine** as a modern browser game.

Build wonderfully unnecessary contraptions, send balls rolling down ramps, trigger chain reactions, and share the results with a link. Start in the sandbox, tweak a machine, remix someone else's creation, or jump into a ready-to-play puzzle and mess around.

For players who enjoy optimisation, the plan is to compete for solutions that finish in the fewest simulation ticks. The same machine should behave the same way every time, with scores independently verified using the same simulation code.

## Current status

**Early development: the environment and architecture are in place.** This is not a playable puzzle game yet.

The scaffold includes a Docker environment, a TanStack development page with API/database checks, and a shared Rapier physics fixture with repeatability tests and a headless Node runner. Next up is a small playable sandbox, followed by editing, saving, sharing and leaderboards.

## Design documents

- [Product and milestones](docs/01-product-design.md)
- [Architecture and decisions](docs/02-architecture.md)
- [Domain schema and data contract](docs/03-puzzle-contract.md)
- [Material prototype specification](docs/04-material-prototype.md)
- [Implementation sequence](docs/05-implementation-plan.md)
- [Start a new implementation conversation](docs/06-implementation-handoff.md)
- [Development setup and checks](docs/development.md)

## Stack

Laravel API / Eloquent / PostgreSQL; TanStack Start, Router, Query and Store; React / TypeScript / CSS; PixiJS and Rapier 2D. Charts are deferred to the leaderboard milestone. The current page is an environment smoke check, not the game editor.

The accepted design adds Spatie Laravel Data, TypeScript Transformer and Laravel Wayfinder for Laravel-owned data contracts and generated frontend types/routes. These additions are planned, not installed yet. The guiding constraint is **only glue code and product rules**: use existing framework capabilities instead of building replacement systems.

## Start

Clone this repository into any directory. Start Docker Desktop in Linux-container mode, then open PowerShell in the project directory:

```powershell
.\dev.ps1 setup
.\dev.ps1 up
```

Open [localhost:3000](http://localhost:3000). No PHP, Composer, Node or pnpm installation on the host is required.

```powershell
.\dev.ps1 check  # Build, type checks, physics checks and Laravel tests
.\dev.ps1 logs   # Recent service logs
.\dev.ps1 down   # Stop services and preserve local data
```

The PowerShell wrapper is intended for Windows. The underlying Compose services use Linux containers. These configurations are for local development, not production hosting.

## Repeatable physics

The initial fixture agrees over 20 fresh runs, and its Windows and Linux Node results match. Complex contraptions and cross-browser agreement still need testing. Physics and game-rule versions will be recorded with published puzzles so future updates can be managed deliberately.

Machine Workshop is an independent project inspired by a childhood favourite, with its own identity and planned original content.
