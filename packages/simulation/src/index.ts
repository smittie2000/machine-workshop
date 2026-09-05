import RAPIER from '@dimforge/rapier2d-compat'
export { createMaterialSimulation, MATERIAL_RECIPE } from './material-simulation'
export type { MaterialSimulation, BodyState } from './material-simulation'
let initialized: Promise<void> | undefined
export async function runFixture() {
  initialized ??= RAPIER.init()
  await initialized
  const world = new RAPIER.World({ x: 0, y: -9.81 })
  try {
    world.timestep = 1 / 60
    const floor = world.createRigidBody(RAPIER.RigidBodyDesc.fixed())
    world.createCollider(RAPIER.ColliderDesc.cuboid(5, 0.1), floor)
    const ball = world.createRigidBody(RAPIER.RigidBodyDesc.dynamic().setTranslation(0, 4))
    world.createCollider(RAPIER.ColliderDesc.ball(0.25), ball)
    const states: number[][] = []
    for (let tick = 1; tick <= 120; tick++) {
      world.step()
      const p = ball.translation()
      states.push([tick, p.x, p.y, ball.rotation()])
    }
    return states
  } finally { world.free() }
}
