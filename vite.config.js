import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/components.css',
                'resources/css/scrollbar.css',
                'resources/js/app.js',
                'resources/js/landing.js'
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'app/Livewire/**/*.php',
                'app/Http/Livewire/**/*.php',
                'resources/js/**/*.js',
                'resources/css/**/*.css'
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: 'localhost',
        port: 5173,
        cors: {
            origin: [
                'http://localhost:8000',
                'http://127.0.0.1:8000',
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                '*.test',
                '*.localhost'
            ],
        },
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs', 'lucide'],
                },
            },
        },
    },
    define: {
        global: 'globalThis',
    },
});