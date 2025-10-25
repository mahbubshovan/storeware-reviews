import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'
import {copyFileSync, unlinkSync, existsSync, renameSync, writeFileSync} from 'fs'
import {resolve} from 'path'

// Source template content
const sourceTemplate = `<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="icon" type="image/svg+xml" sizes="32x32" href="/favicon-32x32.svg" />
    <link rel="icon" type="image/svg+xml" sizes="16x16" href="/favicon-16x16.svg" />
    <link rel="apple-touch-icon" href="/apple-touch-icon.svg" />
    <link rel="manifest" href="/manifest.json" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#8bc34a" />
    <title>Shopify App Review Analytics</title>
    <!-- Meta tags for better SEO -->
    <meta name="description" content="Comprehensive analytics dashboard for tracking and analyzing Shopify app reviews with real-time scraping capabilities." />
    <meta name="keywords" content="shopify, app reviews, analytics, dashboard, review tracking" />
    <meta name="author" content="Shopify App Review Analytics" />
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.jsx"></script>
  </body>
</html>
`

// Custom plugin to manage index.html and index.template.html
const manageIndexPlugin = () => {
    return {
        name: 'manage-index',
        buildStart() {
            // Delete index.html before build to ensure template is used
            const indexPath = resolve(process.cwd(), 'index.html')
            try {
                if (existsSync(indexPath)) {
                    unlinkSync(indexPath)
                    console.log('✓ Removed old index.html before build')
                }
            } catch (err) {
                console.error('Failed to remove index.html:', err.message)
            }
        },
        closeBundle() {
            const templatePath = resolve(process.cwd(), 'index.template.html')
            const indexPath = resolve(process.cwd(), 'index.html')

            try {
                // Rename the built index.template.html to index.html
                if (existsSync(templatePath)) {
                    renameSync(templatePath, indexPath)
                    console.log('✓ Renamed built index.template.html to index.html')
                }

                // Restore the source template
                writeFileSync(templatePath, sourceTemplate)
                console.log('✓ Restored source index.template.html')
            } catch (err) {
                console.error('Failed to manage index files:', err.message)
            }
        }
    }
}

// https://vite.dev/config/
export default defineConfig({
    plugins: [react(), manageIndexPlugin()],
    build: {
        outDir: '',
        assetsDir: 'assets',
        // Ensure relative paths in production
        base: './',
        rollupOptions: {
            input: 'index.template.html',
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
