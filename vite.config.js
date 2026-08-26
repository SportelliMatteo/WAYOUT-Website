import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { createReadStream, existsSync } from 'node:fs';
import { resolve } from 'node:path';

const satoshiFonts = new Set([
    'Satoshi-Light.otf',
    'Satoshi-Regular.otf',
    'Satoshi-Medium.otf',
    'Satoshi-Bold.otf',
    'Satoshi-Black.otf',
]);

const serveSatoshiFontsInDevelopment = () => ({
    name: 'serve-satoshi-fonts-in-development',
    apply: 'serve',
    configureServer(server) {
        server.middlewares.use('/fonts/', (request, response, next) => {
            const filename = decodeURIComponent((request.url || '').split('?')[0]).replace(/^\/+/, '');

            if (!satoshiFonts.has(filename)) {
                next();
                return;
            }

            const fontPath = resolve('public/fonts', filename);

            if (!existsSync(fontPath)) {
                next();
                return;
            }

            response.statusCode = 200;
            response.setHeader('Content-Type', 'font/otf');
            response.setHeader('Cache-Control', 'no-cache');
            createReadStream(fontPath).pipe(response);
        });
    },
});

export default defineConfig({
    plugins: [
        serveSatoshiFontsInDevelopment(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/swagger.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
