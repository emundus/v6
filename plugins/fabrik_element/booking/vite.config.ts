import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'
import env from 'vite-plugin-env-compatible'
import replace from '@rollup/plugin-replace';

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        react(),
        env(),
        replace({
            'process.env.CAL_API_URL': JSON.stringify("https://api.cal.com/v2"),
        })
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
