import { defineConfig } from 'vite'

import symfonyPlugin from 'vite-plugin-symfony';
import react from '@vitejs/plugin-react';
import copy from 'rollup-plugin-copy';
import legacy from '@vitejs/plugin-legacy'

export default defineConfig({
  plugins: [
    legacy({
      targets: ['defaults', 'not IE 11'],
      modernTargets: 'edge>=18, firefox>=60, chrome>=61, opera>=48, safari>=12, chromeAndroid>=64, iOS>=12'
    }),
    symfonyPlugin({
      originOverride: 'https://precontentieux.anje-justice.dev',
      stimulus: true,
      build: {
        manifest: true,
        outDir: "public/build",
      },
    }),
    react(),
    copy({
      targets: [
        { src: "node_modules/@gouvfr/dsfr/dist/*", dest: "public/dsfr" },
        { src: "node_modules/remixicon/*", dest: "public/remixicon" },
      ],
      hook: 'writeBundle'
    })
  ],
  build: {
    target: "es2015",
    commonjsOptions: { transformMixedEsModules: true },
    rollupOptions: {
      input: {
        "app": "./assets/app.js",
        "bris_porte_tester_mon_eligibilite": "./assets/apps/bris_porte/tester_mon_eligibilite.ts",
      }
      // TODO: test to export vendors as manualChunks https://gist.github.com/emmiep/8fb5a2887a8ec007b319f0abff04ffb1#file-rollup-config-js-L18
    },
  },
  server: {
    host: '0.0.0.0'
  }
});