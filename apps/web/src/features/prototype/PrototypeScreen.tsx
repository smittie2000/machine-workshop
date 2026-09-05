import { useCallback, useState } from 'react'
import { Link } from '@tanstack/react-router'
import { useQuery } from '@tanstack/react-query'
import { catalogueQueryOptions, prototypeQueryOptions } from '../../lib/api'
import { WorkshopHeader } from '../../components/WorkshopHeader'
import { WorkshopFooter } from '../../components/WorkshopFooter'
import { PartArtwork } from '../../components/PartArtwork'
import { SimulationCanvas, type RunProgress } from './SimulationCanvas'
import { RunControls, type RunStatus } from './RunControls'
import type { CatalogueData, StarterDocumentData } from '@workshop/contracts'

export function PrototypeScreen() {
  const prototype = useQuery(prototypeQueryOptions())
  const catalogue = useQuery({ ...catalogueQueryOptions(prototype.data?.document.catalogueVersion ?? ''), enabled: !!prototype.data, retry: false })
  return <div className="site-shell"><WorkshopHeader /><main className="prototype-main">
    <Link to="/" className="back-link">← Back to the workshop</Link>
    <div className="prototype-heading"><div><p className="eyebrow">Experiment 001 / The test bench</p><h1>The bounce test<span>.</span></h1><p>A basketball meets a brick platform. You take it from here.</p></div><span className="tag">Basketball × Brick</span></div>
    {prototype.isError || catalogue.isError
      ? <div className="message-panel" role="alert"><h2>The parts couldn’t be loaded.</h2><p>Check that the workshop is connected, then try again.</p><button className="button button-primary" onClick={() => { void prototype.refetch(); if (prototype.data) void catalogue.refetch() }}>Try again</button></div>
      : prototype.data && catalogue.data
        ? <PrototypeExperiment catalogue={catalogue.data} prototype={prototype.data} />
        : <div className="message-panel" role="status">Getting the test bench ready…</div>}
  </main><WorkshopFooter /></div>
}

function PrototypeExperiment({ catalogue, prototype }: { catalogue: CatalogueData; prototype: StarterDocumentData }) {
  const [status, setStatus] = useState<RunStatus>('loading')
  const [run, setRun] = useState(0)
  const [error, setError] = useState('')
  const [progress, setProgress] = useState<RunProgress>({ tick: 0, seconds: 0, height: 0, velocityY: 0 })
  const onReady = useCallback(() => setStatus('ready'), [])
  const onComplete = useCallback(() => setStatus('complete'), [])
  const onHidden = useCallback(() => setStatus(value => value === 'running' ? 'paused' : value), [])
  const onError = useCallback((message: string) => { setError(message); setStatus('error') }, [])
  const reset = () => { setStatus('loading'); setError(''); setProgress({ tick: 0, seconds: 0, height: 0, velocityY: 0 }); setRun(value => value + 1) }
  const usedParts = catalogue.parts.filter(part => prototype.document.instances.some(instance => instance.partKey === part.key))
  return <div className="test-layout">
    <section className="test-bench" aria-label="Interactive bounce test">
      <div className="bench-heading"><span><span className={`status-dot ${status === 'running' ? 'live' : ''}`} /> {status === 'loading' ? 'Preparing the bench' : status === 'running' ? 'In motion' : status === 'ready' ? 'Ready when you are' : status === 'error' ? 'Unable to start' : status === 'complete' ? 'Run complete' : 'Paused'}</span><span>01 / BASKETBALL ON BRICK</span></div>
      <div className="canvas-wrap">
        <SimulationCanvas key={run} catalogue={catalogue} document={prototype.document} playing={status === 'running'} onReady={onReady} onProgress={setProgress} onComplete={onComplete} onHidden={onHidden} onError={onError} />
        {status === 'loading' && <div className="canvas-message" role="status">Setting out the parts…</div>}
        {status === 'error' && <div className="canvas-message" role="alert"><strong>The test couldn’t start.</strong><p>{error}</p><p>Press Reset to try again.</p></div>}
        <span className="canvas-caption">THE TEST BENCH <span>•</span> NO GOAL, JUST CURIOSITY</span>
      </div>
      <RunControls status={status} onPlay={() => setStatus('running')} onPause={() => setStatus('paused')} onReset={reset} />
    </section>
    <aside className="test-sidebar">
      <section className="parts-panel"><p className="eyebrow">The ingredients</p><h2>Two parts.<br />One simple idea.</h2>
        {usedParts.map(part => <div className="part-row" key={part.key}><div className="part-preview"><PartArtwork visualKey={part.visualKey} alt="" /></div><div><h3>{part.name}</h3><p>{part.bodyType === 'dynamic' ? `${part.massG} g · Free to move` : 'Fixed in place'}</p></div></div>)}
      </section>
      <section className="observations"><p className="eyebrow">Watch it happen</p><dl><div><dt>Time in motion</dt><dd>{progress.seconds.toFixed(1)} <small>s</small></dd></div><div><dt>Height above the brick</dt><dd>{progress.height.toFixed(2)} <small>m</small></dd></div></dl><p className="observation-note">Watch each bounce get a little smaller. Reset to bring the ball back to the top.</p></section>
      <p className="bench-note"><span aria-hidden="true">✳</span> Every good machine begins<br />with a little experimenting.</p>
    </aside>
  </div>
}
