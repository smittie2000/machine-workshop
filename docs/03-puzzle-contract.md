# Domain schema and data contract

Status: accepted v1 implementation design; phase A is implemented. Catalogue validation belongs to Laravel Data/native Laravel rules, with relational integrity enforced by PostgreSQL. Released definitions remain immutable.

## Four domain concepts

1. Material: explicit friction/restitution values belonging to a catalogue release.
2. Part: explicit supported geometry, body kind, mass and material/visual references.
3. Catalogue version: a complete immutable set of those definitions and an exact simulation recipe.
4. Puzzle document: ordered placements and authoring rules referencing that catalogue.

## Catalogue tables — first migration slice

Use Eloquent relationships, PHP backed enums, Laravel Data/native Laravel validation, and PostgreSQL primary/unique/foreign keys. Migrations own column types, nullability and relationships. Catalogue-specific identifiers and product bounds are validated in PHP, not embedded as PostgreSQL CHECK constraints. Public identifiers are stable strings; internal material/part IDs can be ordinary generated bigint keys.

### catalogue_versions / CatalogueVersion

| Column | Shape / rule |
| --- | --- |
| id | varchar(64) primary key, release key such as workshop-1; non-incrementing Eloquent string key |
| name | varchar(120) |
| simulation_version | varchar(64), allowlisted recipe such as material-demo-1 |
| released_at | nullable timestamp with timezone; null means draft |
| created_at, updated_at | timestamps |

Simulation version selects a committed, immutable recipe (Rapier version, timestep, gravity, combine rules and numerical behaviour). It is not arbitrary executable code or free-form configuration JSON. Do not create a recipe administration system.

### materials / Material

| Column | Shape / rule |
| --- | --- |
| id | bigint primary key |
| catalogue_version_id | FK to catalogue_versions, restrict deletion |
| key | varchar(64), stable within release |
| name | varchar(120) |
| friction | numeric(6,5), 0 through 2 |
| restitution | numeric(6,5), 0 through 1 |
| created_at, updated_at | timestamps |

UNIQUE(catalogue_version_id, key); UNIQUE(catalogue_version_id, id) supports the same-release composite FK below. No material properties bag and no separate material-history/version table.

Store decimal coefficients as exact decimal columns. DTOs deliberately serialize them as JSON numbers; do not accidentally expose Eloquent decimal strings to Rapier. Test this wire representation.

### parts / Part

| Column | Shape / rule |
| --- | --- |
| id | bigint primary key |
| catalogue_version_id | FK to catalogue_versions |
| material_id | bigint; composite FK (catalogue_version_id, material_id) -> materials(catalogue_version_id, id) |
| key | varchar(64), UNIQUE(catalogue_version_id, key) |
| name | varchar(120) |
| body_type | varchar enum: fixed or dynamic |
| shape_type | varchar enum: ball or cuboid |
| radius_mm | nullable positive integer; required only for ball |
| width_mm, height_mm | nullable positive integers; required only for cuboid |
| mass_g | nullable positive integer for dynamic; null for fixed |
| visual_key | varchar(100), allowlisted identifier for a bundled visual |
| created_at, updated_at | timestamps |

Shape-dependent null/required rules live in PartData and are reused for imports and ordinary model saves. Dimensions 1..10000 mm; dynamic mass 1..100000 g. MaterialData owns coefficient bounds, and PHP enums/validation own supported body, shape, visual and recipe identifiers. Database columns store these values without duplicating the product rules as CHECK constraints or database enums. Only complete widths/heights cross the contract; the Rapier adapter halves them for cuboid half-extents.

Explicit mass is the one initial mass policy. Set collider mass once for a dynamic part and let Rapier derive shape-consistent inertia. Do not also set body additional mass or introduce a density override. Fixed bodies have no player-configurable mass. Complex compound shapes are deferred.

## Catalogue contribution and release

Author contributions as ordinary Laravel seeder data in a release-specific class, including stable release/part/material keys. Database rows are authoritative at runtime; PHP seed definitions are the reviewed source for reproducible installation. No custom catalogue language or parallel TypeScript definitions.

An approved variant using the current fields does not require a schema migration. Add its reviewed seed/art definition; a new visual/recipe also requires the corresponding implementation and PHP allowlist entry. Schema migrations are required only when storage structure changes. Ordinary model saves must validate raw attributes through the existing DTO rules before database casts can hide invalid input. Do not duplicate those rules in model-specific validators.

A small import/release action uses DB::transaction and locks the catalogue row. Imports may update a draft. A release validates membership and required shapes/visual keys, then sets released_at. After release, importer and ordinary model operations must reject updates/deletions to the release and its materials/parts. All write paths lock/check the parent; avoid bulk updates that bypass guards. FK restrictions preserve referenced data. No HTTP catalogue-write endpoints.

Re-running an already released seed must confirm matching definitions or refuse differences, never updateOrCreate over published values. To change physics, copy the release to a new key and modify its draft. Locking and comparison here are product rules, not a generic versioning framework.

The empty sandbox starter is a small committed PHP array in config/machine-workshop.php, constructed/validated through PuzzleDocumentData when served. It references workshop-1 with no placements, inventory, locked instances or goal. It contains no physics definitions. This avoids an unauthenticated database save or a fake owner before accounts exist. Local edits are stored in the browser; creations/publications tables arrive with their actual authenticated workflows.

### Repeatable procedure for adding parts

The catalogue is the reusable workshop inventory. `WorkshopOneSeeder` defines the initial `workshop-1` snapshot: basketball and brick, wood and rubber-mat platforms, retaining their reviewed physical values. It replaces the demo seeder; normal setup no longer seeds prototype-1 or a three-lane layout. Previously stored releases are not deleted or rewritten. Use this seeder's structure for subsequent releases. A part needing joints, compound geometry or behaviour outside the supported recipe requires a separate mechanic decision and implementation first.

1. Create the next release seeder with Laravel Artisan through `dev.ps1 docker`. Give it a new explicit release key. Keep the previous release and register both explicitly in `DatabaseSeeder`, oldest first. Do not choose releases dynamically from files or environment variables.
2. Keep a public `definition(): array` manifest containing `id`, `name`, `simulationVersion`, `materials` and `parts`. Use private `materials(): array` and `parts(): array` methods for the two lists. `run(ImportCatalogue $import)` only imports the complete manifest. Seeders contain data; DTOs own validation and the action owns persistence.
3. Copy retained definitions into the new snapshot. Never derive released content from another release, a shared mutable part list, a factory, a database query or random data. Explicit duplication between immutable snapshots is intentional: changing a later release must not change an earlier seed.
4. Add each approved material and part as a complete array with one field per line, in its DTO constructor order. Include the null fields. Use lowercase kebab-case keys for new records, integer millimetres/grams, and decimal gameplay coefficients. A size or material variant gets its own part key; do not generate a matrix of every possible combination. Reuse a material key within the release when its surface rules are identical.
5. Reuse a supported visual key only when its drawing is appropriate for the geometry. A new visual needs the corresponding renderer implementation and PHP allowlist entry. A changed recipe needs its implementation, new simulation version and determinism fixture review. Neither is accomplished by adding an identifier alone.
6. Keep the empty sandbox starter in `config/machine-workshop.php`, referencing an exact release. Expanding the catalogue does not require adding placements or goals. Future prepared creations select from a catalogue. Existing documents continue to reference their original release.
7. Add feature coverage for the new release's expected part keys and reviewed values, public DTO numbers, and a valid document using its new variants. Seed through `DatabaseSeeder` twice and assert no stored rows change. Verify the previous release remains unchanged and that a part available only in the new release is rejected under the old release. Existing validation/release tests remain applicable; do not duplicate them for every part.
8. Run `dev.ps1 check` and the API-required Pint formatting command through Docker. Regenerate contracts only through the existing tooling when DTOs/enums/routes change. Record actual verification and remaining simulation limitations in `docs/development.md`.

The initial catalogue contribution convention introduces no part framework, per-part PHP classes, generic builder, new storage structure or manually maintained TypeScript contract. Additional organization should solve a demonstrated readability problem without making a contributor trace implicit defaults.

## Puzzle document wire shape

The same PuzzleDocumentData DTO is used for JSONB casting, local drafts and API transport. It contains finite, bounded, typed data, not Rapier world snapshots.

```json
{
  "schemaVersion": 1,
  "catalogueVersion": "workshop-1",
  "instances": [
    { "id": "ball-brick", "partKey": "basketball", "xMm": -4000, "yMm": 3000, "rotationMilliDegrees": 0 },
    { "id": "floor-brick", "partKey": "platform-brick", "xMm": -4000, "yMm": 0, "rotationMilliDegrees": 0 }
  ],
  "lockedInstanceIds": [],
  "inventory": [],
  "goal": null
}
```

This is an example of a player-populated sandbox; the product starter has no instances. Titles and owner metadata belong to the outer resource, not the simulation document.

- catalogueVersion is a released catalogue key. It determines simulationVersion; clients cannot independently override the recipe or gravity.
- id is a document-local stable ASCII identifier, 1..64 characters using letters, digits, underscore or hyphen. Uniqueness is validated. Generate new player instance IDs with native crypto.randomUUID; retain IDs through save/run/reset.
- xMm/yMm are integer millimetres, -50000..50000 inclusive. Rotation is integer millidegrees in [0, 359999]. UI snaps/translates input to these units before updating the draft.
- No scale, mass, material coefficients, initial velocity, collider vertices, executable code or arbitrary options.
- No connections field in schema v1. Add a typed connection collection only when a supported joint mechanic is implemented; do not silently accept unknown keys.
- lockedInstanceIds is empty for sandbox. In a published puzzle it references author-fixed placements.
- inventory entries, for puzzle authoring, have partKey and integer quantity 0..200; keys unique, same catalogue. Sandbox inventory is empty and means unrestricted approved parts within the global cap. Mode is the outer resource kind.
- goal is null for sandbox. Future puzzle publication requires a typed remain-in-region goal: objectId, centre xMm/yMm, widthMm/heightMm and consecutiveTicks=30. No arbitrary expressions.
- Limit documents to 200 instances and 256 KiB serialized request bodies. Maximum run: 3600 physics ticks (60 simulated seconds). These limits are recipe/product constants, not user-editable fields.
- Initially allow overlapping placement: Rapier resolves it. The prototype starts non-overlapping. Do not invent geometry validation to reject all overlaps. Future puzzle authoring guidance may warn without changing old recipe rules.
- Preserve array order on save; simulation constructs bodies by ASCII instance ID order for stable replay. Do not depend on Eloquent query order or locale-sensitive sorting.
- Unsupported schema/catalogue/recipe yields an explicit error. Never replace with latest or silently migrate a published document.

## Cloud persistence — later milestone, planned now

| Model/table | Fields and relationships |
| --- | --- |
| Creation / creations | UUID id, user_id FK, title varchar(120), kind enum sandbox/puzzle, catalogue_version_id FK, document JSONB cast to PuzzleDocumentData, revision integer starting at 1, optional source_publication_id, timestamps |
| Publication / publications | UUID id, creation_id FK, version integer unique within creation, catalogue_version_id FK, title, kind, document JSONB, published_at; immutable public snapshot |
| PublicationSolution / publication_solutions | publication_id unique FK, private solution document JSONB, verified completion tick; never returned by public DTO |
| Submission / submissions | UUID id, user_id, publication_id, idempotency_key, player placements JSONB, status enum pending/running/verified/rejected/error, nullable completion_tick/added_part_count, bounded diagnostic/error_code, timestamps |

No generic entity table, material JSONB properties or separate event store. Separate solution storage makes accidental exposure harder. Submissions can store verification results directly; a second result table is unnecessary initially.

For creations/publications, document.catalogueVersion must equal relational catalogue_version_id. Validate and set both atomically. Published records cannot cascade-delete their referenced catalogue. Title/kind and document constraints are validated together.

Cloud save uses an atomic revision comparison/update and 409 on conflict. Publication uses an Eloquent transaction and row lock, assigning the next integer publication version. Only verified submissions enter leaderboard queries. Unique(user_id, publication_id, idempotency_key) handles repeated submissions. Include publication and catalogue scope in replay inputs.

## DTOs and enum ownership

First slice: CatalogueData, MaterialData, PartData, PuzzleDocumentData, PartInstanceData, InventoryItemData, RegionGoalData, ValidateDocumentData, ValidatedDocumentData and StarterDocumentData. PHP enums: BodyType, ShapeType and SimulationVersion. The known inventory/goal DTOs define the document shape now; phase A accepts only sandbox documents with empty inventory/lockedInstanceIds and null goal. Publishing later enables those known fields with the associated product rules. Future milestones add CreationData, PublicationData and submission DTOs.

DTOs live in apps/api/app/Data using normal Laravel/Spatie conventions. Generated TypeScript goes into packages/contracts. Use Laravel Data casting for JSONB and normal transformations for response DTOs. Cross-field rules remain named Laravel rules/actions, not generated JavaScript behaviour.

Do not wrap a DTO in a matching JsonResource that duplicates its field mapping. Use one serialization owner per endpoint. Requests use explicit validation before DTO construction/persistence, including non-HTTP seed and local-restoration paths.

### First-slice response shapes

Return the DTO object directly as JSON, with camelCase properties. No extra generic success envelope.

- CatalogueData: id (release key), name, simulationVersion, materials (MaterialData[]), parts (PartData[]). Sort both collections by key for stable presentation/export.
- MaterialData: key, name, friction (number), restitution (number). Omit internal database IDs.
- PartData: key, name, materialKey, bodyType, shapeType, radiusMm, widthMm, heightMm, massG, visualKey. Inapplicable numeric fields are explicitly null. Omit internal database IDs.
- StarterDocumentData: title and document (PuzzleDocumentData).
- ValidateDocumentData request: document (PuzzleDocumentData).
- ValidatedDocumentData response: document (the accepted document). Validation rejects invalid values rather than silently correcting or switching versions. UI transform normalization happens before sending.

The frontend resolves materialKey within the loaded catalogue. Laravel's DTO transformation translates the Eloquent material relationship to that key; it does not expose cross-release IDs. The simulation accepts these DTOs plus the document. SimulationVersion's generated values identify allowed recipes; test that every released catalogue references a recipe actually implemented by the shared package.

## API slices

All new application routes live under /api/v1; existing /api/health remains operational.

| Method/path | Purpose / milestone |
| --- | --- |
| GET /api/v1/catalogues/{catalogue} | Public released catalogue; first slice |
| GET /api/v1/starters/sandbox | Public starter document + title; first slice |
| POST /api/v1/documents/validate | Guest, rate-limited, stateless validation; first slice |
| GET/POST /api/v1/creations | List own / create cloud draft; auth milestone |
| GET/PUT /api/v1/creations/{creation} | Authorized read/save with expectedRevision |
| POST /api/v1/creations/{creation}/publications | Publish frozen version; publishing milestone |
| GET /api/v1/publications and /{publication} | Public browse/exact version |
| POST /api/v1/publications/{publication}/remixes | Create own draft from publication |
| POST /api/v1/publications/{publication}/submissions | Submit player-added placements only |
| GET /api/v1/submissions/{submission} | Authorized status |
| GET /api/v1/publications/{publication}/leaderboard | Verified exact-version standings |

Use named controller routes for Wayfinder. Standard Laravel JSON errors: 401 authentication, 403 forbidden, 404 missing/unreleased catalogue, 409 revision/version conflict, 422 field/domain validation, 429 rate limit. Use default JSON validation error shape; do not invent a parallel error protocol.

In solve mode, reconstruct scenery, inventory and goal from the publication. Submitted placements cannot reuse locked IDs or modify server-owned fields. Catalogue resolution and inventory enforcement happen before queueing.
