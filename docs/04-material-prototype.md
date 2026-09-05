# Material prototype specification

Status: implementation target after the catalogue/DTO slice. No artwork or new rendering framework required.

The requested basketball/brick test now exists at `/prototype`, with shared SVG assets and the accepted material recipe adapter. The three-lane comparison below remains an optional test scenario, not the empty sandbox starter. See development.md for current evidence; it does not yet include all three material rebounds or cross-browser digests.

Revision: this document retains the reviewed numerical recipe and an optional material-comparison test scenario. Product setup now seeds `workshop-1` with the same four parts and starts with an empty sandbox. It does not seed the three-lane layout or prototype-1. The scenario below must not be used to restrict catalogue membership or repopulate the product starter. The existing `material-demo-1` recipe identifier is retained because this change does not alter numerical rules; its adapter still belongs to phase B.

## Recipe material-demo-1

| Setting | Value |
| --- | --- |
| Rapier | Existing pinned @dimforge/rapier2d-compat 0.20.0 |
| Coordinates | metres, positive Y up; convert integer mm by dividing by 1000 |
| Step | exactly 1/60 second, tick counter begins at 0 before stepping |
| Gravity | x=0, y=-9.81 m/s² |
| Restitution combination | Rapier Multiply on every collider |
| Friction combination | Rapier Average on every collider |
| Initial velocity / angular velocity | zero |
| Damping | zero for this fixture |
| Dynamic ball CCD | enabled through Rapier |
| Max ticks | 3600 |
| Collisions | normal Rapier contacts; no custom bounce solver |
| Placement ordering | ASCII instance ID order |

Pass rotation in radians to Rapier using integer millidegrees * Math.PI / 180000; use Rapier for rotation/collision geometry instead of computing collider vertices with JavaScript trigonometry. Pin any solver settings overridden by the adapter in this recipe. Unspecified engine settings remain the pinned engine defaults, and changing them later is a recipe change.

Restitution Multiply is selected deliberately: basketball coefficient 1 leaves the selected floor's coefficient as the effective pair coefficient. No per-object combine-rule selector and no ball/floor lookup matrix. Friction is not the vertical-bounce tuning knob.

## Catalogue prototype-1 seed

Illustrative gameplay presets, not laboratory material claims. Tuning can change before releasing this catalogue; after release use a new release key.

| Material key | Friction | Restitution |
| --- | --- | --- |
| basketball-surface | 0.50 | 1.00 |
| brick | 0.60 | 0.85 |
| wood | 0.40 | 0.60 |
| rubber-mat | 0.90 | 0.25 |

Rubber-mat means an energy-absorbing mat in this game. Do not generalize the low bounce to all rubber.

| Part key | Body / shape | Dimensions | Mass | Material | Visual key |
| --- | --- | --- | --- | --- | --- |
| basketball | dynamic / ball | radius 120 mm | 620 g | basketball-surface | basketball |
| platform-brick | fixed / cuboid | 3000 × 300 mm | null | brick | platform-brick |
| platform-wood | fixed / cuboid | 3000 × 300 mm | null | wood | platform-wood |
| platform-rubber | fixed / cuboid | 3000 × 300 mm | null | rubber-mat | platform-rubber |

The three lanes use x=-4000, 0, 4000 mm. Floor centres are y=0. Ball centres are y=3000. All angles are zero. Stable IDs identify each ball and floor. Ball-to-floor surface drop distance is 2730 mm.

For an ideal single vertical impact, rebound height/drop height is approximately restitution squared. Use this as intuition, not an exact engine assertion. Acceptance is reproducible ordering brick > wood > rubber-mat with plausible positive rebound heights, measured from the same contact/resting centre reference.

## Browser surface

Display all three lanes with material names, Drop/Reset and tick status. Drop takes a draft snapshot; Reset restores it. No simulation coefficients are editable in this UI. Basic palette/selection/position/rotation controls arrive in the same small editor slice; no arbitrary resizing.

Draw the ball and surfaces using PixiJS Graphics. Colours/patterns communicate materials. Add shading only with existing PixiJS drawing features. Geometry comes from PartData, while a small explicit visual-key switch supplies decoration. A debug collider outline may help review alignment; do not build an asset pipeline or live Blender integration.

A first-bounce diagnostic follows each ball: identify first floor contact via Rapier events, then track the first upward phase until vertical velocity becomes non-positive. Measure rebound height as apexCentreY - (floorTopY + ballRadius). Store diagnostic state outside the saved draft. This diagnostic is not a puzzle win condition or leaderboard score. Enable the relevant Rapier collision events when constructing the fixture. The comparison diagnostic applies only to the unmodified horizontal three-lane starter; arbitrary edited layouts may run but display no comparison measurement.

## Timing and reset

Use requestAnimationFrame/Pixi ticker for presentation and an accumulator for fixed physics steps. Cap work per render frame to prevent freezing; if overloaded, simulation may lag/slow down rather than enlarge its timestep. Pause on a hidden tab and clear accumulated wall time on resume. Wall-clock time never enters product scoring or physics inputs.

Optional visual interpolation does not feed back into Rapier. Do not store body transforms in React state each frame. Start with ordinary fixed-step rendering; polish interpolation only if needed.

Each run owns a fresh Rapier world, body mapping, event queue and diagnostics. Dispose them on reset/unmount. Asynchronous Rapier initialization must not create a world after the editor unmounts. Do not use a global mutable world or start two loops from React effect reruns.

## Acceptance

- Catalogue coefficients are JSON numbers from Laravel, not browser hardcoded copies.
- The seed produces four materials/four parts and the three-lane starter references them.
- Twenty fresh runs match at every tick for the same recipe/document on the headless runner.
- 30/60/144 Hz synthetic render schedules produce identical per-tick simulation states.
- Windows/Linux Node and Chromium/Firefox/WebKit run the same fixture; compare digests over ordered body state, not screenshots. Document any unsupported platform; do not promise cross-browser replay before evidence.
- First rebound ordering matches the three presets; friction remains constant per preset throughout a run.
- Changing a placement and resetting preserves the changed draft, not the original starter and not a mid-run state.
- Local save/reload through validation preserves integer placements and exact catalogue reference.
- Reject an unknown part, unreleased catalogue, non-finite/out-of-range values and unsupported schema.
- No cloud account, public leaderboard or polished artwork is required to accept this prototype.
