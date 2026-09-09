import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    build: {
        rollupOptions: {
            // Laravel serves these public files at the same URLs used by font preloads.
            external: [
                '/fonts/mouse28/besley-latin.woff2',
                '/fonts/mouse28/poppins-300.woff2',
                '/fonts/mouse28/poppins-400.woff2',
                '/fonts/mouse28/poppins-500.woff2',
                '/fonts/mouse28/poppins-600.woff2',
                '/fonts/mouse28/poppins-700.woff2',
            ],
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
