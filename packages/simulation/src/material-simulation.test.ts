import { test } from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import type { CatalogueData, PuzzleDocumentData } from '@workshop/contracts'
import { createMaterialSimulation, MATERIAL_RECIPE } from './material-simulation.ts'

const fixture: { catalogue: CatalogueData; document: PuzzleDocumentData } = JSON.parse(
  readFileSync(new URL('../fixtures/basketball-brick.json', import.meta.url), 'utf8'),
)

async function run(document = fixture.document) {
  const world = await createMaterialSimulation(fixture.catalogue, document)
  try {
    return Array.from({ length: 240 }, () => { world.step(); return world.states() })
  } finally { world.dispose() }
}

test('catalogue basketball falls, rebounds on brick and fresh worlds agree at every tick', async () => {
  const original = structuredClone(fixture)
  const baseline = await run()
  const ball = baseline.map(states => states.find(state => state.id === 'ball')!)
  assert.ok(ball[10].y < ball[0].y)
  const rebound = ball.findIndex((state, index) => index > 0 && ball[index - 1].velocityY < 0 && state.velocityY > 0)
  assert.ok(rebound > 0, 'Ball must change from falling to rising')
  assert.ok(ball[rebound].y > 0.2 && ball[rebound].y < 0.35, 'Contact aligns with catalogue radius and platform top')
  const apex = ball.slice(rebound).findIndex(state => state.velocityY <= 0)
  assert.ok(apex > 0)
  assert.ok(ball[rebound + apex].y > 1.7 && ball[rebound + apex].y < 2.6, 'Brick produces the reviewed positive rebound')
  for (let i = 1; i < 20; i++) assert.deepEqual(await run(), baseline)
  assert.deepEqual(await run({ ...fixture.document, instances: [...fixture.document.instances].reverse() }), baseline)
  assert.deepEqual(fixture, original, 'Running must not mutate catalogue or draft')
})

test('fixed-step states agree under 30, 60 and 144 Hz presentation schedules', async () => {
  const baseline = await run()
  for (const hz of [30, 60, 144]) {
    const world = await createMaterialSimulation(fixture.catalogue, fixture.document)
    try {
      const states = []
      let accumulator = 0
      while (world.tick < 240) {
        accumulator += 1 / hz
        while (accumulator + 1e-12 >= MATERIAL_RECIPE.stepSeconds && world.tick < 240) {
          world.step()
          states.push(world.states())
          accumulator -= MATERIAL_RECIPE.stepSeconds
        }
      }
      assert.deepEqual(states, baseline)
    } finally { world.dispose() }
  }
})

test('new worlds restore initial placements, enforce the run limit and reject disposed access', async () => {
  const world = await createMaterialSimulation(fixture.catalogue, fixture.document)
  const initial = world.states()
  for (let i = 0; i < MATERIAL_RECIPE.maxTicks + 2; i++) world.step()
  assert.equal(world.tick, MATERIAL_RECIPE.maxTicks)
  world.dispose()
  world.dispose()
  assert.throws(() => world.step(), /disposed/)
  const reset = await createMaterialSimulation(fixture.catalogue, fixture.document)
  try { assert.deepEqual(reset.states(), initial); assert.equal(reset.tick, 0) } finally { reset.dispose() }
})

test('engine boundary rejects wrong releases, unsupported recipes, missing parts and non-finite values', async () => {
  await assert.rejects(createMaterialSimulation(fixture.catalogue, { ...fixture.document, catalogueVersion: 'missing' }), /mismatch/)
  const recipe = structuredClone(fixture.catalogue)
  Object.assign(recipe, { simulationVersion: 'unknown' })
  await assert.rejects(createMaterialSimulation(recipe, fixture.document), /recipe/)
  const unknown = structuredClone(fixture.document)
  unknown.instances[0].partKey = 'missing'
  await assert.rejects(createMaterialSimulation(fixture.catalogue, unknown), /Unknown part/)
  const invalid = structuredClone(fixture.document)
  invalid.instances[0].xMm = NaN
  await assert.rejects(createMaterialSimulation(fixture.catalogue, invalid), /transform/)
})
