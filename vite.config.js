import { defineConfig } from 'vite'

import symfonyPlugin from 'vite-plugin-symfony';
import react from '@vitejs/plugin-react';
import copy from 'rollup-plugin-copy';

export default defineConfig({
  plugins: [
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
    },
  },
  server: {
    host: '0.0.0.0'
  }
});