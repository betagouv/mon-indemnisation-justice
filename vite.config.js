import {defineConfig} from 'vite'

import symfonyPlugin from 'vite-plugin-symfony';
import reactPlugin from '@vitejs/plugin-react';
import copy from 'rollup-plugin-copy';
import legacy from '@vitejs/plugin-legacy'

export default defineConfig(({command, mode}) => {
        const base = mode === 'production' ? '/build/' : '/preview/';
        const outDir = `public/${base}`;

        return {
            base,
            plugins: [
                legacy({
                    // Doc https://github.com/vitejs/vite/tree/main/packages/plugin-legacy
                    //targets: ['defaults', 'not IE 11'],
                    modernTargets: ['firefox>=60, chrome>=61'],
                    polyfills: ['es.promise.finally', 'es/map', 'es/set', ''],
                    modernPolyfills: ['es.promise.finally'],
                    renderLegacyChunks: false, // Pas besoin puisqu'on est ESM **only**
                }),
                symfonyPlugin({
                    originOverride: 'https://precontentieux.anje-justice.dev',
                    stimulus: true,
                    build: {
                        manifest: true,
                        outDir,
                    },
                }),
                reactPlugin(),
                copy({
                    targets: [
                        {src: "node_modules/@gouvfr/dsfr/dist/*", dest: "public/dsfr"},
                        {src: "node_modules/remixicon/*", dest: "public/remixicon"},
                    ],
                    hook: 'writeBundle'
                })
            ],
            esbuild: false,
            build: {
                outDir,
                target: "es2015",
                modulePreload: false,
                rollupOptions: {
                    input: {
                        "app": "./assets/app.js",
                        "bris_porte_tester_mon_eligibilite": "./assets/apps/bris_porte/tester_mon_eligibilite.ts",
                    }
                    // TODO: test to export vendors as manualChunks https://gist.github.com/emmiep/8fb5a2887a8ec007b319f0abff04ffb1#file-rollup-config-js-L18
                },
            },
            server: {
                host: '0.0.0.0',

            }
        }
    }
);