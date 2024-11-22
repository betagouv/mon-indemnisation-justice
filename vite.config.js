import {defineConfig} from 'vite'

import { fileURLToPath, URL } from 'node:url'
import symfonyPlugin from 'vite-plugin-symfony';
import reactPlugin from '@vitejs/plugin-react';
import copy from 'rollup-plugin-copy';
import legacy from '@vitejs/plugin-legacy'

// Vite will ignore native environment variables, unless they're declared in local `.env` file
// (see https://github.com/vitejs/vite/issues/562)
// Here we had them dynamically

import.meta.env = import.meta.env ?? {};

Object
    .entries(process.env)
    .filter(
        ([key, value]) => key.startsWith('VITE_')
    ).forEach(
        ([key, value]) => import.meta.env[key] = value
    );

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
                    stimulus: false,
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
            resolve: {
                alias: {
                  '@': fileURLToPath(new URL('./assets', import.meta.url))
                }
              },
            esbuild: false,
            build: {
                outDir,
                target: "es2015",
                modulePreload: false,
                rollupOptions: {
                    // TODO: test to export vendors as manualChunks https://gist.github.com/emmiep/8fb5a2887a8ec007b319f0abff04ffb1#file-rollup-config-js-L18
                    input: {
                        ...{
                            "bris_porte_tester_mon_eligibilite": "./assets/apps/bris_porte/tester_mon_eligibilite.ts",
                            "bris_porte_creation_de_compte": "./assets/apps/bris_porte/creation_de_compte.ts",
                            "bris_porte_deposer_mon_dossier": "./assets/apps/bris_porte/deposer_mon_dossier.tsx",
                        },
                        ...(import.meta.env?.VITE_SENTRY_DSN && {
                            "sentry": "./assets/apps/sentry.ts"
                        })
                    }
                },
            },
            server: {
                host: '0.0.0.0',

            }
        }
    }
);