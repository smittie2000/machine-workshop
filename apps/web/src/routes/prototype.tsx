import { createFileRoute } from '@tanstack/react-router'
import { PrototypeScreen } from '../features/prototype/PrototypeScreen'

export const Route = createFileRoute('/prototype')({
  head: () => ({ meta: [{ title: 'The bounce test · Machine Workshop' }] }),
  component: PrototypeScreen,
})
