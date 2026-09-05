import { createFileRoute } from '@tanstack/react-router'
import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'

export const Route = createFileRoute('/')({ component: Development })
function Development() {
  const [result, setResult] = useState('')
  const api = useQuery({
    queryKey: ['health'],
    queryFn: async () => {
      const response = await fetch('/api/health')
      if (!response.ok) throw new Error('API unavailable')
      return response.json() as Promise<{ status: string; database: string }>
    },
    retry: false,
  })
  async function checkPhysics() {
    setResult('Checking…')
    try {
      const { runFixture } = await import('@workshop/simulation')
      const first = await runFixture()
      const second = await runFixture()
      setResult(JSON.stringify(first) === JSON.stringify(second)
        ? 'Two fresh 120-tick runs match.'
        : 'Mismatch: investigate before building puzzles.')
    } catch (error) { setResult(String(error)) }
  }
  return <main><p className="eyebrow">Development environment</p><h1>Machine Workshop</h1>
    <p>Browser contraptions. Repeatable physics. Shareable puzzles.</p>
    <section><h2>Services</h2><p>TanStack Start: running</p>
      <p>Laravel / PostgreSQL: {api.isPending ? 'checking…' : api.isError ? 'unavailable — start the API and database' : api.data.status + ' / ' + api.data.database}</p>
      <button onClick={() => api.refetch()}>Recheck API</button>
    </section>
    <section><h2>Physics smoke check</h2><p>A fresh Rapier world drops one ball onto a floor. This checks basic repeatability on this browser; cross-platform validation comes next.</p>
      <button onClick={checkPhysics}>Run physics check</button><p role="status">{result}</p>
    </section>
    <p>The editor, saving and leaderboards are planned in the design documents.</p>
  </main>
}
