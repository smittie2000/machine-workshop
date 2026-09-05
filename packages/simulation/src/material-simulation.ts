import RAPIER from '@dimforge/rapier2d-compat'
import type { CatalogueData, PuzzleDocumentData } from '@workshop/contracts'

/** Implements the accepted material-demo-1 recipe; changes require a new version. */
export const MATERIAL_RECIPE = { version: 'material-demo-1', stepSeconds: 1 / 60, maxTicks: 3600 } as const

export type BodyState = { id: string; x: number; y: number; rotation: number; velocityY: number }
export type MaterialSimulation = {
  readonly tick: number
  step(): void
  states(): BodyState[]
  dispose(): void
}

let initialization: Promise<void> | undefined

function positive(value: number | null, field: string): number {
  if (value === null || !Number.isFinite(value) || value <= 0) throw new Error(`Invalid ${field}`)
  return value
}

/** Accepts Laravel-validated documents. Guards protect the engine boundary, not a second schema. */
export async function createMaterialSimulation(catalogue: CatalogueData, document: PuzzleDocumentData): Promise<MaterialSimulation> {
  if (catalogue.simulationVersion !== MATERIAL_RECIPE.version) throw new Error('Unsupported simulation recipe')
  if (document.schemaVersion !== 1 || document.catalogueVersion !== catalogue.id) throw new Error('Document/catalogue mismatch')
  if (document.goal !== null || document.inventory.length || document.lockedInstanceIds.length) throw new Error('Only sandbox runs are supported')
  const snapshot = structuredClone(document)
  const definitions = structuredClone(catalogue)
  initialization ??= RAPIER.init()
  await initialization
  const world = new RAPIER.World({ x: 0, y: -9.81 })
  world.timestep = MATERIAL_RECIPE.stepSeconds
  const bodies = new Map<string, RAPIER.RigidBody>()
  let tick = 0
  let disposed = false
  try {
    const parts = new Map(definitions.parts.map(part => [part.key, part]))
    const materials = new Map(definitions.materials.map(material => [material.key, material]))
    const instances = snapshot.instances.sort((a, b) => a.id < b.id ? -1 : a.id > b.id ? 1 : 0)
    for (const instance of instances) {
      if (bodies.has(instance.id)) throw new Error('Duplicate instance ID')
      if (![instance.xMm, instance.yMm, instance.rotationMilliDegrees].every(Number.isFinite)) throw new Error('Invalid transform')
      const part = parts.get(instance.partKey)
      if (!part) throw new Error(`Unknown part: ${instance.partKey}`)
      const material = materials.get(part.materialKey)
      if (!material || !Number.isFinite(material.friction) || !Number.isFinite(material.restitution)
        || material.friction < 0 || material.friction > 2 || material.restitution < 0 || material.restitution > 1) throw new Error('Invalid material')
      if (part.bodyType !== 'fixed' && part.bodyType !== 'dynamic') throw new Error('Unsupported body type')
      const bodyDescription = part.bodyType === 'fixed' ? RAPIER.RigidBodyDesc.fixed() : RAPIER.RigidBodyDesc.dynamic()
      bodyDescription.setTranslation(instance.xMm / 1000, instance.yMm / 1000)
        .setRotation(instance.rotationMilliDegrees * Math.PI / 180000)
        .setLinearDamping(0).setAngularDamping(0)
      if (part.bodyType === 'dynamic' && part.shapeType === 'ball') bodyDescription.setCcdEnabled(true)
      let collider: RAPIER.ColliderDesc
      switch (part.shapeType) {
        case 'ball': collider = RAPIER.ColliderDesc.ball(positive(part.radiusMm, 'radius') / 1000); break
        case 'cuboid': collider = RAPIER.ColliderDesc.cuboid(positive(part.widthMm, 'width') / 2000, positive(part.heightMm, 'height') / 2000); break
        default: throw new Error('Unsupported shape')
      }
      collider.setFriction(material.friction).setRestitution(material.restitution)
        .setFrictionCombineRule(RAPIER.CoefficientCombineRule.Average)
        .setRestitutionCombineRule(RAPIER.CoefficientCombineRule.Multiply)
      if (part.bodyType === 'dynamic') collider.setMass(positive(part.massG, 'mass') / 1000)
      const body = world.createRigidBody(bodyDescription)
      world.createCollider(collider, body)
      bodies.set(instance.id, body)
    }
    return {
      get tick() { return tick },
      step() {
        if (disposed) throw new Error('Simulation is disposed')
        if (tick >= MATERIAL_RECIPE.maxTicks) return
        world.step()
        tick++
      },
      states() {
        if (disposed) throw new Error('Simulation is disposed')
        return [...bodies].map(([id, body]) => ({ id, ...body.translation(), rotation: body.rotation(), velocityY: body.linvel().y }))
      },
      dispose() {
        if (disposed) return
        disposed = true
        bodies.clear()
        world.free()
      },
    }
  } catch (error) {
    world.free()
    throw error
  }
}
