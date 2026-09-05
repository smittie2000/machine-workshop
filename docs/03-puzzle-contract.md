# Puzzle contract draft

Status: proposed v1; freeze after the first physics proof.

```json
{
  "schemaVersion": 1,
  "simulationVersion": "prototype-1",
  "title": "First drop",
  "world": { "gravity": { "x": 0, "y": -9.81 } },
  "parts": [
    { "id": "ball-1", "type": "ball", "x": 0, "y": 4, "angle": 0, "locked": true },
    { "id": "ramp-1", "type": "platform", "x": 0, "y": 1, "angle": 0, "locked": false }
  ],
  "connections": [],
  "inventory": { "platform": 2 },
  "goal": {
    "type": "remain-in-region",
    "objectId": "ball-1",
    "region": { "x": 0, "y": 0, "width": 2, "height": 1 },
    "consecutiveTicks": 30
  },
  "maxTicks": 3600
}
```

This is an illustrative document, not a proven solvable level. Physics uses metres, seconds and radians, with positive Y up. Region coordinates identify its centre. Rendering converts world units to pixels and reverses Y. The part catalogue defines dimensions, mass and collision properties; published levels cannot inject arbitrary properties or code.

## Validation requirements

- Unique IDs, finite bounded numbers, known types and supported versions.
- Valid object references, bounded counts, allowed connections and no unknown fields.
- Quantization policy established before version 1 is frozen; save/load must preserve canonical values.
- Initial overlaps, out-of-bounds placement and inventory accounting have explicit authoring rules.
- Immutable server puzzle settings override any values in an untrusted submission.
- Stable canonical ordering and number representation before computing content hashes. Never hash raw JSON text whose key order may vary.

## Planned API

| Method and path | Purpose |
| --- | --- |
| GET /api/health | Environment connectivity |
| GET /api/puzzles | Public published catalogue |
| GET /api/puzzles/{version} | Immutable published version |
| POST /api/creations | Create owned draft |
| PUT /api/creations/{id} | Save with expected revision |
| POST /api/creations/{id}/publish | Validate and publish a new immutable version |
| POST /api/puzzles/{version}/submissions | Submit placement with idempotency key |
| GET /api/submissions/{id} | Authorized verification status |
| GET /api/puzzles/{version}/leaderboard | Verified standings |

Generate or maintain a machine-readable API schema when these endpoints are implemented. PHP validates at the trust boundary even when TypeScript has already validated client input.
