import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Code splitting for better caching
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Vendor chunk for node_modules
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Source maps for production debugging (disable for smaller builds)
        sourcemap: false,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: [],
    },
    // Server configuration for development
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
