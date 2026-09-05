import { defineConfig } from 'vite'
import { tanstackStart } from '@tanstack/react-start/plugin/vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [tanstackStart(), react()],
  server: {
    port: 3000, strictPort: true,
    watch: { usePolling: true },
    proxy: {
      '/api': { target: process.env.API_INTERNAL_ORIGIN || 'http://api:8000' },
      '/sanctum': { target: process.env.API_INTERNAL_ORIGIN || 'http://api:8000' },
    },
  },
})
