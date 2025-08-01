import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            postcss: { // อาจจะมีส่วนนี้
                plugins: [
                    '@tailwindcss/postcss', // แก้ไขตรงนี้
                    'autoprefixer',
                ],
            },
        }),
        tailwindcss(),
    ],
});
