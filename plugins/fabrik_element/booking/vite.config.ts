import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                entryFileNames: 'app_booking.js',
                assetFileNames: 'app_booking.css',
                chunkFileNames: "chunk.js",
            }
        }
    }
})
