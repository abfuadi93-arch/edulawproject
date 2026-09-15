import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const tinyMcePlugins = [
    'advlist',
    'autolink',
    'charmap',
    'code',
    'fullscreen',
    'image',
    'link',
    'lists',
    'searchreplace',
    'table',
    'visualchars',
    'wordcount',
];

const tinyMceAssets = [
    'tinymce.min.js',
    'icons/default/icons.min.js',
    'models/dom/model.min.js',
    'themes/silver/theme.min.js',
    'skins/ui/oxide/skin.min.css',
    'skins/ui/oxide/content.min.css',
    'skins/ui/oxide-dark/skin.min.css',
    'skins/ui/oxide-dark/content.min.css',
    'skins/content/default/content.css',
    'skins/content/dark/content.css',
    ...tinyMcePlugins.map((plugin) => `plugins/${plugin}/plugin.min.js`),
];

export default defineConfig({
    build: {
        rolldownOptions: {
            // Filament loads rich-editor extensions with a dynamic import and
            // expects the entry module's default export to remain available.
            preserveEntrySignatures: 'strict',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/home.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
                'resources/js/filament/rich-editor-footnote.js',
                'resources/js/filament/tinymce-editor.js',
            ],
            refresh: true,
            fonts: [
                local('Lato', {
                    variants: [400, 700].map((weight) => ({
                        src: `resources/fonts/lato/lato-${weight}-latin.woff2`,
                        weight,
                    })),
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
        viteStaticCopy({
            targets: tinyMceAssets.map((asset) => ({
                src: `node_modules/tinymce/${asset}`,
                dest: `tinymce/${asset.split('/').slice(0, -1).join('/')}`.replace(/\/$/, ''),
            })),
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
