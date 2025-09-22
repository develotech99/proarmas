import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 
            'resources/js/app.js',
            'resources/js/usuarios/index.js',
            'resources/js/modelos/pro_modelo.js',
            'resources/js/usuarios/mapa.js',
            'resources/js/comisiones/index.js',
        ],
            refresh: true,
        }),
    ],
});
