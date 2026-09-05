export type RunStatus = 'loading' | 'ready' | 'running' | 'paused' | 'complete' | 'error'

export function RunControls({ status, onPlay, onPause, onReset }: {
  status: RunStatus; onPlay(): void; onPause(): void; onReset(): void
}) {
  const label = status === 'running' ? 'Pause' : status === 'paused' ? 'Resume' : 'Drop the ball'
  return <div className="run-controls">
    <button className="button button-primary" disabled={['loading', 'complete', 'error'].includes(status)} onClick={status === 'running' ? onPause : onPlay}>
      <span aria-hidden="true">{status === 'running' ? 'Ⅱ' : '▶'}</span> {label}
    </button>
    <button className="button button-secondary" disabled={status === 'loading'} onClick={onReset}><span aria-hidden="true">↺</span> Reset</button>
    <span className="control-hint">{status === 'paused' ? 'Paused. Pick up where you left off.' : status === 'complete' ? 'Run complete. Reset to try again.' : 'Same parts. A fresh start, every time.'}</span>
  </div>
}
