import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
    plugins: [react()],
    publicDir: false,
    build: {
        outDir: '.',
        assetsDir: 'assets',
        emptyOutDir: false,
        // Ensure relative paths in production
        base: './',
        minify: false,
        rollupOptions: {
            input: 'dashboard.html',
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
