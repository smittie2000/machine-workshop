import { test } from 'node:test'
import assert from 'node:assert/strict'
import { runFixture } from './index.ts'
test('fresh worlds agree at every tick over 20 runs and the ball settles on the floor', async () => {
  const baseline = await runFixture()
  assert.equal(baseline.length, 120)
  assert.ok(baseline[119][2] > 0.3 && baseline[119][2] < 0.4)
  for (let run = 0; run < 19; run++) assert.deepEqual(await runFixture(), baseline)
})
