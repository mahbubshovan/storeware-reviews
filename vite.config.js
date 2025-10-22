import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
    plugins: [react()],
    build: {
        outDir: '',
        assetsDir: 'assets',
        // Ensure relative paths in production
        base: './',
        rollupOptions: {
            output: {
                entryFileNames: 'assets/index.js',
                assetFileNames: 'assets/index.css',
            }
        }
    },
    server: {
        // Development server configuration
        proxy: {
            '/backend': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/backend/, '')
            }
        }
    }
})
