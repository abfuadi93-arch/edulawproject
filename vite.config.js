import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
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
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
                'resources/js/filament/rich-editor-footnote.js',
                'resources/js/filament/tinymce-editor.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        viteStaticCopy({
            targets: [
                {
                    src: 'node_modules/tinymce/tinymce.min.js',
                    dest: 'tinymce',
                },
                {
                    src: 'node_modules/tinymce/icons/default/icons.min.js',
                    dest: 'tinymce/icons/default',
                },
                {
                    src: 'node_modules/tinymce/models/dom/model.min.js',
                    dest: 'tinymce/models/dom',
                },
                {
                    src: 'node_modules/tinymce/themes/silver/theme.min.js',
                    dest: 'tinymce/themes/silver',
                },
                {
                    src: 'node_modules/tinymce/skins/ui/oxide/skin.min.css',
                    dest: 'tinymce/skins/ui/oxide',
                },
                {
                    src: 'node_modules/tinymce/skins/ui/oxide/content.min.css',
                    dest: 'tinymce/skins/ui/oxide',
                },
                {
                    src: 'node_modules/tinymce/skins/ui/oxide-dark/skin.min.css',
                    dest: 'tinymce/skins/ui/oxide-dark',
                },
                {
                    src: 'node_modules/tinymce/skins/ui/oxide-dark/content.min.css',
                    dest: 'tinymce/skins/ui/oxide-dark',
                },
                {
                    src: 'node_modules/tinymce/skins/content/default/content.css',
                    dest: 'tinymce/skins/content/default',
                },
                {
                    src: 'node_modules/tinymce/skins/content/dark/content.css',
                    dest: 'tinymce/skins/content/dark',
                },
                ...tinyMcePlugins.map((plugin) => ({
                    src: `node_modules/tinymce/plugins/${plugin}/plugin.min.js`,
                    dest: `tinymce/plugins/${plugin}`,
                })),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
