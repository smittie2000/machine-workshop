# Machine Workshop — product decisions

Status: accepted design for implementation. This document and the linked specifications replace earlier planning proposals. Features described here are not all implemented; see [handoff](06-implementation-handoff.md).

Catalogue revision: the initial product uses `workshop-1` with the basketball and three approved platform variants, plus an empty sandbox starter. Material comparison remains a possible simulation test. Follow the [catalogue contribution procedure](03-puzzle-contract.md#repeatable-procedure-for-adding-parts) when expanding the parts list.

## Guiding constraint

**Only glue code and product rules.**

Use Laravel/Eloquent, Spatie Laravel Data, TypeScript Transformer, Wayfinder, TanStack, browser APIs, PixiJS and Rapier for their existing capabilities. Write the game's definitions, permissions, state transitions and adapters. Do not build a physics engine, ORM, schema generator, routing system, generic entity/component framework, generic material editor or reusable command/event platform.

## Product

An independent browser contraption game inspired by The Incredible Machine. The sandbox is the foundation. Players build, run, save and remix creations; authors use that same editor to make prepared puzzles. A later competition layer rewards efficient solutions. A shaded, dimensional appearance on a 2D plane is compatible with this design, but artwork production is not a prerequisite for the first slice.

## Accepted permissions

| Activity | Guest | Signed-in player | Catalogue maintainer |
| --- | --- | --- | --- |
| Browse/play public creations and puzzles | Yes | Yes | Yes |
| Experiment and save a browser-local draft | Yes | Yes | Yes |
| Place, rotate, remove or duplicate permitted parts | Yes | Yes | Yes |
| Change arbitrary scale, collider, mass, friction, bounce or gravity | No | No | Through reviewed release definitions only |
| Choose brick/wood/rubber platform | Choose an approved part variant | Same | Defines variants |
| Cloud saves, publishing and ranked submissions | No | Own records | Same application policies |
| Edit a released catalogue | No | No | No; publish another release |

Position and rotation are the editable physical settings. Copying a part counts against puzzle inventory. Fixed scenery cannot be moved, removed, duplicated or reconfigured in solve mode. Creators can choose scenery and inventory in authoring mode; publishing freezes them. A basketball cannot be freely converted into a wooden ball: such an object would be another reviewed part.

Maintainer contributions use GitHub pull requests with Laravel seed definitions and original artwork. There is no in-game catalogue administration surface in this scope.

## Authoring, solving and sharing

- A creation has an editable draft. Cloud drafts are private to their owner.
- Publishing creates an immutable public version. Later edits affect only the draft until published again.
- A sandbox publication is a runnable creation with no required goal.
- A puzzle publication includes fixed scenery, allowed inventory and one goal. Ranked publication requires a server-verified example solution.
- Store the author's example solution privately; never include it in public puzzle responses.
- Remix creates a separate draft and retains the source publication reference. It never edits the original.
- Published URLs identify a specific version, not a mutable draft.
- Existing published physics remains reproducible. Removal from discovery may be added later without deleting dependencies needed for replay.

## Editing and running

Desktop-first. Use ordinary React controls around the PixiJS canvas: part palette, selected part properties, play controls. Do not recreate form controls inside the canvas.

Load -> validated draft -> edit -> Play -> fresh simulation -> Pause/Resume or Reset.
Reset discards the running world and shows the unchanged draft. Playback never rewrites starting placements. Placement edits require returning to edit mode. All runs in this release, including sandbox runs, use this rule; live intervention is deferred.

Save persists the draft, never the current falling-ball positions. Edits made while a save request is pending must not be lost when its response arrives. A cloud revision conflict is shown to the player; keep the local draft and offer reload or save as a separate creation. Do not implement automatic merging.

Undo/redo is added with the editor milestone: bounded document snapshots, one entry per completed edit/drag, excluding selection and simulation ticks. No generic command bus or event sourcing. A browser-local save uses native localStorage with explicit success/error feedback; no offline synchronization or offline-play guarantee.

## Catalogue and physics ownership

One catalogue release contains its complete material and part records and references one simulation recipe/version. The release is editable only before release. Releasing seals it. Unchanged definitions can be copied into a new release; no independently versioned material framework is needed.

Materials use known relational fields. Parts use known relational geometry, mass and material references. Puzzle JSON references those parts. Values do not come from SVG outlines, image pixels, Blender meshes, user scripts or arbitrary submitted Rapier options.

Laravel Data and native Laravel validation own catalogue product rules: supported identifiers, shape requirements, mass and coefficient limits. Migrations define storage, relationships and uniqueness, without embedding catalogue entries or duplicating those rules as database CHECK constraints. Adding an approved variant using existing fields is a seed/art change; adding a new visual or recipe also requires its reviewed implementation and PHP allowlist change, not a migration merely to approve its identifier.

The visual reference is separate from physics. The welcome screen and basketball/brick test share original SVG assets, drawn as images in React and textures in PixiJS. Art preserves contact alignment, origin and pivot. Art-only changes must not modify physics; physical changes require another catalogue release (and a simulation version change if numerical rules/code change).

## Goals and scores — later implementation, settled semantics

First supported puzzle goal: the centre of a designated object remains inside an axis-aligned rectangular region for 30 consecutive completed physics steps. Boundary inclusion is inclusive. Evaluate after each step; leaving resets the counter. The success tick is the tick completing that count, including dwell time. The region is a mathematical goal test, not a mass-contributing physics sensor.

Rank by completion tick ascending, then player-added part count ascending. Equal scores share rank; accepted timestamp then submission ID only stabilize display order. Rank per immutable publication. Client results are provisional. A slower computer gets no score disadvantage because wall time is never the score.

Use a table for standings; charts may show distributions and personal improvement. Charts, account UI, publishing and ranking are later milestones, not dependencies of the material prototype.

## First playable slice

An empty sandbox with a palette containing the basketball and brick, wood and rubber-mat platforms. Players place approved parts, change positions/rotations, Play, Reset, save locally and reopen. No prearranged demonstration or required goal is seeded. No coefficient sliders in the public editor.

Implement it after the Laravel catalogue and generated contracts work. See [implementation sequence](05-implementation-plan.md). The initial part coefficients are retained gameplay tuning values, not real-world material measurements. The three-material drop described in 04-material-prototype.md can be used as a test fixture without becoming the product starter.

## Deferred without blocking this design

Connections/joints, arbitrary resizing, ropes, gears, fans, characters, fluids, skins, custom behaviours, live multiplayer, user scripts, mobile authoring, monetization and AI-generated puzzles. Add supported part/connection types when a concrete mechanic needs them; do not build extension infrastructure in anticipation.
