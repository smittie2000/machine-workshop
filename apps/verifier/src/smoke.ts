import { createHash } from 'node:crypto'
import { runFixture } from '@workshop/simulation'
// Development fixture only. This does not accept or verify user submissions yet.
const states = await runFixture()
console.log(JSON.stringify({ ticks: states.length, sha256: createHash('sha256').update(JSON.stringify(states)).digest('hex') }))
