# Machine Workshop — product design

Project name: Machine Workshop. Design baseline: 2026-09-05.

## Product

A browser-based 2D contraption sandbox inspired by the experience of The Incredible Machine. Players build machines, share runnable creations, remix them, and publish puzzles. The creator uses the same editor to produce the initial playable puzzle collection. Original artwork, names, and content will give the game its own identity.

## Primary journeys

1. Open a shared creation, press Play, Reset, and Remix into an editable copy.
2. Build a machine by placing, rotating, configuring, and connecting parts. Undo/redo edits. Save a draft.
3. Turn a creation into a puzzle by locking scenery, selecting the available inventory, and defining a goal. Supply a working solution before publishing a ranked puzzle.
4. Solve a puzzle, inspect a provisional score, submit for verification, and improve the solution.

Public browsing and local experimentation should not require login. Accounts are needed for server saves, publishing, and ranked submissions. This is a proposed product default, not implemented authentication.

## Editor experience

Desktop-first. Main canvas with part palette on the left, selected-part properties on the right, and Play/Pause/Reset plus tick status above. Keyboard shortcuts supplement visible controls. Touch-friendly play comes before a full mobile authoring experience.

Editing state and simulation state are separate. Play creates a fresh simulation from a draft snapshot. Reset restores the draft. Ranked runs allow no placement changes or live intervention. Pause and visual playback speed do not change the physics timestep.

## Initial parts and goals

First physics proof: ball, fixed ramp/platform, dynamic box, bucket target. Then add seesaw, switch, and motor. Ropes, belts, gears, characters, fluids, and arbitrary scripting are later investigations.

Initial goal: a designated object's centre stays within a designated goal region for 30 consecutive simulation ticks. This definition must be visible to authors; graphical bucket boundaries must agree with the actual region. Goal evaluation follows the physics step.

Initial score: ascending completion tick, then ascending added-part count. Equal scores share a rank; submission time is only a stable display ordering. Leaderboards are scoped to an immutable puzzle version and simulation version. Faster hardware never improves scores.

Charts show score distributions or personal improvements. An accessible table remains the authoritative standings view. Charts are a later milestone, not a prerequisite for proving physics.

## Milestones and acceptance

1. Environment: reproducible dependency installation, frontend build/typecheck, API health, PostgreSQL connection and migrations.
2. Physics proof: browser and headless runner use the same package; repeat runs match at every step, reset is clean, different rendering rates agree.
3. Editor: place/rotate/remove, selection, undo/redo, draft snapshot, save/load round-trip without changing part order or numbers.
4. Sharing: Laravel persistence and ownership, immutable published versions, public URLs, remix provenance, three starter puzzles.
5. Competition: bounded verification jobs, server-calculated scores, version-scoped rankings and score charts.

## Deferred

Realtime multiplayer, monetary rewards, arbitrary user code, user-created physics behaviours, advanced mechanics, AI-generated puzzles, and a large campaign. AI may assist development; simulation correctness is established by tests.
