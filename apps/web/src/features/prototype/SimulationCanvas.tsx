import { useEffect, useRef } from 'react'
import type { CatalogueData, PuzzleDocumentData } from '@workshop/contracts'
import type { Application, Sprite } from 'pixi.js'
import type { MaterialSimulation } from '@workshop/simulation'
import { partAsset } from '../../components/PartArtwork'

export type RunProgress = { tick: number; seconds: number; height: number; velocityY: number }
type Props = {
  catalogue: CatalogueData
  document: PuzzleDocumentData
  playing: boolean
  onReady(): void
  onProgress(progress: RunProgress): void
  onComplete(): void
  onHidden(): void
  onError(message: string): void
}

/** One mount owns one world, canvas and ticker. Reset remounts with the unchanged document. */
export function SimulationCanvas({ catalogue, document: puzzle, playing, onReady, onProgress, onComplete, onHidden, onError }: Props) {
  const host = useRef<HTMLDivElement>(null)
  const playingRef = useRef(playing)
  useEffect(() => { playingRef.current = playing }, [playing])

  useEffect(() => {
    const element = host.current!
    let cancelled = false
    let app: Application | undefined
    let simulation: MaterialSimulation | undefined
    let observer: ResizeObserver | undefined
    let accumulator = 0
    let wasPlaying = false
    const onVisibility = () => {
      accumulator = 0
      wasPlaying = false
      if (document.hidden) {
        playingRef.current = false
        onHidden()
      }
    }
    document.addEventListener('visibilitychange', onVisibility)

    async function initialize() {
      const [{ Application, Container, Assets, Sprite }, { createMaterialSimulation, MATERIAL_RECIPE }] = await Promise.all([
        import('pixi.js'), import('@workshop/simulation'),
      ])
      if (cancelled) return
      const application = new Application()
      await application.init({ width: element.clientWidth, height: element.clientHeight, antialias: true,
        backgroundAlpha: 0, autoStart: false, resolution: Math.min(window.devicePixelRatio || 1, 2), autoDensity: true })
      if (cancelled) { application.destroy(true, { children: true }); return }
      app = application
      const parts = new Map(catalogue.parts.map(part => [part.key, part]))
      const stage = new Container()
      application.stage.addChild(stage)
      const sprites = new Map<string, Sprite>()
      for (const instance of puzzle.instances) {
        const part = parts.get(instance.partKey)
        if (!part) throw new Error(`Part is unavailable: ${instance.partKey}`)
        const texture = await Assets.load(partAsset(part.visualKey))
        if (cancelled) return
        const sprite = new Sprite(texture)
        sprite.anchor.set(0.5)
        sprite.width = part.shapeType === 'ball' ? part.radiusMm! * 2 / 1000 : part.widthMm! / 1000
        sprite.height = part.shapeType === 'ball' ? part.radiusMm! * 2 / 1000 : part.heightMm! / 1000
        stage.addChild(sprite)
        sprites.set(instance.id, sprite)
      }
      if (cancelled) return
      const world = await createMaterialSimulation(catalogue, puzzle)
      if (cancelled) { world.dispose(); return }
      simulation = world
      element.appendChild(application.canvas)
      application.canvas.setAttribute('aria-label', 'Basketball falling and bouncing on a brick platform')
      application.canvas.setAttribute('role', 'img')
      const ballPart = parts.get(puzzle.instances.find(instance => instance.id === 'ball')!.partKey)!
      const platformPart = parts.get(puzzle.instances.find(instance => instance.id === 'platform')!.partKey)!
      const restingHeight = (platformPart.heightMm! / 2 + ballPart.radiusMm!) / 1000

      function draw(report = false) {
        for (const state of world.states()) {
          const sprite = sprites.get(state.id)!
          sprite.position.set(state.x, -state.y)
          sprite.rotation = -state.rotation
          if (report && state.id === 'ball') onProgress({ tick: world.tick, seconds: world.tick * MATERIAL_RECIPE.stepSeconds,
            height: Math.max(0, state.y - restingHeight), velocityY: state.velocityY })
        }
      }
      function resize() {
        application.renderer.resize(element.clientWidth, element.clientHeight)
        stage.position.set(element.clientWidth / 2, element.clientHeight - 65)
        stage.scale.set(Math.min((element.clientWidth - 40) / 4.2, (element.clientHeight - 110) / 3.3))
        draw()
      }
      observer = new ResizeObserver(resize)
      observer.observe(element)
      resize()
      draw(true)
      application.ticker.add(ticker => {
        const running = playingRef.current && !document.hidden
        if (!running) { accumulator = 0; wasPlaying = false; return }
        // Ignore paused wall time. Excess work is discarded, never a larger physics timestep.
        if (!wasPlaying) { wasPlaying = true; return }
        accumulator = Math.min(accumulator + ticker.elapsedMS / 1000, MATERIAL_RECIPE.stepSeconds * 8)
        let stepped = false
        while (accumulator + 1e-12 >= MATERIAL_RECIPE.stepSeconds && world.tick < MATERIAL_RECIPE.maxTicks) {
          world.step()
          accumulator -= MATERIAL_RECIPE.stepSeconds
          stepped = true
        }
        if (stepped) draw(world.tick % 6 === 0 || world.tick === MATERIAL_RECIPE.maxTicks)
        if (world.tick >= MATERIAL_RECIPE.maxTicks) { playingRef.current = false; onComplete() }
      })
      application.start()
      onReady()
    }

    void initialize().catch(error => {
      observer?.disconnect()
      simulation?.dispose()
      app?.destroy(true, { children: true })
      app = undefined
      if (!cancelled) onError(error instanceof Error ? error.message : 'The test could not be loaded.')
    })
    return () => {
      cancelled = true
      document.removeEventListener('visibilitychange', onVisibility)
      observer?.disconnect()
      simulation?.dispose()
      app?.destroy(true, { children: true })
      app = undefined
    }
  }, [catalogue, puzzle, onReady, onProgress, onComplete, onHidden, onError])

  return <div ref={host} className="simulation-canvas" />
}
