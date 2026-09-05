# Project instructions

This is a local Docker development project. Preserve the agreed Laravel API / PostgreSQL / TanStack Start / PixiJS / Rapier stack. Do not deploy or replace it with a hosted-site starter.

Read docs/01-product-design.md and docs/02-architecture.md before changing ownership boundaries. Use dev.ps1 for Docker commands on Windows. PHP, Composer and JavaScript tooling belong in containers; do not install host runtimes as a workaround.

The accepted design is in docs/01-product-design.md through docs/06-implementation-handoff.md. Start implementation with phase A in docs/05-implementation-plan.md. The controlling constraint is "Only glue code and product rules." Do not build custom physics, a generic ECS/editor framework, schema/code generators or duplicated PHP/TypeScript contracts. Use Eloquent, Spatie Laravel Data/TypeScript Transformer and Wayfinder; the latter packages are selected but not installed at the design handoff.

Laravel owns catalogue definitions. Materials and parts have explicit relational fields within immutable catalogue releases; puzzle JSON stores placements and exact catalogue references. Players may place/rotate approved variants, never freely resize or change physical coefficients. Keep generated types separate from the shared simulation's implementation rules. These decisions supersede earlier proposals for a TypeScript-owned catalogue.

Laravel owns authentication, authorization and durable business rules. Start must not connect directly to PostgreSQL. The simulation package must remain independent of React, DOM, Laravel and wall-clock time. Browser scores are provisional until independently verified.

Use the root pnpm lockfile and Laravel Composer lockfile. Changes to physics versions or numerical rules require explicit simulation versioning and determinism fixture review. Keep drafts separate from running worlds and create a fresh world for each run.

Run relevant checks from dev.ps1 check. Frontend route generation occurs during build before typechecking. Do not claim cross-platform determinism based solely on the Node smoke test. Record environment limitations in docs/development.md.

Never delete Docker data volumes as a repair step without explicit user authorization. Never commit .env, tokens or generated application keys.

## Catalogue contribution conventions

Before changing catalogue seeds, read the contribution procedure in docs/03-puzzle-contract.md. Use WorkshopOneSeeder as the structural example, not as the parts list for the whole game.

- One self-contained seeder per immutable catalogue release. `definition()` lists release metadata, `materials()` and `parts()`; `run()` delegates persistence to ImportCatalogue. DatabaseSeeder explicitly lists release seeders in oldest-first order.
- Write every material/part field explicitly, one field per line, in DTO constructor order. Include nullable geometry/mass fields. Use stable lowercase kebab-case keys for new entries and unit-bearing field names. Do not infer physics from a name, visual, category or another part.
- Do not build part class hierarchies, discovery registries, inherited defaults or generators. Copy reviewed values into each new release snapshot; never make an old release read mutable shared definitions or another release's methods.
- Catalogues describe approved reusable parts. Starter documents and puzzle goals select from a catalogue and do not determine its full membership. Do not add unsupported mechanics or invent the planned parts list to fill out a release.
- Preserve sealed release IDs and data. Adding a part requires a new release, even when all old parts are unchanged. A recipe change additionally requires simulation versioning and fixture review.
- Use existing DTO validation and ImportCatalogue. Test new parts through catalogue reads and document validation, repeat seeding, and preservation of older releases. See docs/03-puzzle-contract.md for the exact procedure and check command.

## Screen, asset and simulation conventions

- Route files in apps/web/src/routes only configure navigation/head metadata and render a named screen. Keep screen composition in src/features/<feature>, shared site components in src/components, and API access in src/lib/api.ts with Query and generated Wayfinder helpers.
- Keep original visual assets in apps/web/public/assets/parts/<visual-key>.svg. PartArtwork maps supported visual keys explicitly. SVG bounds match the part silhouette with a centred pivot; keep shadows outside the asset in presentation code. Physics dimensions always come from PartData, never image pixels. New artwork does not change catalogue coefficients.
- PrototypeDocumentController serves the basketball/brick test document from config/machine-workshop.php. The empty sandbox starter remains separate. Do not hardcode another catalogue in React or the simulation package.
- SimulationCanvas owns one Pixi application, ticker and fresh simulation per mount. Use React for coarse status and controls, not per-frame body transforms. Reset remounts the canvas with the unchanged document. Dispose on reset/unmount and handle cancellation during async initialization.
- packages/simulation/src/material-simulation.ts implements the explicit versioned Rapier recipe, with generated contract types as inputs. Keep HTTP, React, DOM and presentation timing outside it. Never implement bounce by changing CSS positions or writing collision math.
- Generate the committed simulation fixture through dev.ps1 generate; the command reads the released Laravel catalogue and validates the prototype document. Do not hand-edit fixture JSON. Generation now requires the seeded development database. Test observable rebound, repeatability and cleanup when changing simulation code, and review screens in the browser when changing presentation.
