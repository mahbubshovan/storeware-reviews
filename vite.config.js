import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'
import {resolve} from 'path'

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        react(),
        {
            name: 'serve-dashboard',
            configureServer(server) {
                return () => {
                    server.middlewares.use((req, res, next) => {
                        if (req.url === '/' || req.url === '/index.html') {
                            req.url = '/dashboard.html'
                        }
                        next()
                    })
                }
            }
        },
        {
            name: 'rename-index',
            enforce: 'post',
            generateBundle(options, bundle) {
                // Find the dashboard.html file in the bundle
                const dashboardHtml = bundle['dashboard.html']
                if (dashboardHtml) {
                    // Rename it to index.html
                    dashboardHtml.fileName = 'index.html'
                    bundle['index.html'] = dashboardHtml
                    delete bundle['dashboard.html']
                }
            }
        }
    ],
    publicDir: 'public-resources',
    build: {
        outDir: '.',
        assetsDir: 'assets',
        emptyOutDir: false,
        // Ensure relative paths in production
        base: './',
        minify: true,
        rollupOptions: {
            input: resolve(__dirname, 'dashboard.html'),
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
                changeOrigin: true
            }
        }
    }
})
