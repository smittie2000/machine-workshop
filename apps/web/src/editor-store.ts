import { Store } from '@tanstack/react-store'
// Factory prevents drafts being shared between SSR requests.
// Undo/redo command history will be added with the editor milestone.
export function createEditorStore() {
  return new Store<{ selectedPartId: string | null; dirty: boolean; mode: 'edit' | 'running' | 'paused' }>({
    selectedPartId: null, dirty: false, mode: 'edit',
  })
}
