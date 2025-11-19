/// <reference types="./types" />

import * as path from "path";
import { defineConfig, loadEnv, UserConfig } from "vite";
import { fileURLToPath, URL } from "node:url";
import symfonyPlugin from "vite-plugin-symfony";
import { default as react } from "@vitejs/plugin-react";
import { tanstackRouter } from "@tanstack/router-plugin/vite";
import legacy from "@vitejs/plugin-legacy";

export default defineConfig(({ mode }: UserConfig): UserConfig => {
  process.env = mode ? { ...process.env, ...loadEnv(mode, process.cwd()) } : {};

  return {
    cacheDir: process.env.VITE_CACHE_DIR ?? "node_modules/.vite",
    css: {
      postcss: {
        map: true,
      },
    },
    plugins: [
      tanstackRouter({
        target: "react",
        virtualRouteConfig: "./src/apps/agent/fip6/routeur/routeur-fip6.ts",
        generatedRouteTree: "./src/apps/agent/fip6/routeur/routeur-fip6.gen.ts",
        routesDirectory: "./src/apps/agent/fip6/routes/",
      }),
      tanstackRouter({
        target: "react",
        generatedRouteTree: "./src/apps/agent/fdo/routeur/routeur-fdo.gen.ts",
        routesDirectory: "./src/apps/agent/fdo/routes/",
      }),
      legacy({
        // Doc https://github.com/vitejs/vite/tree/main/packages/plugin-legacy
        //targets: ['defaults', 'not IE 11'],
        modernTargets: ["firefox>=60, chrome>=61"],
        polyfills: ["es.promise.finally", "es/map", "es/set", ""],
        modernPolyfills: true,
        renderLegacyChunks: false, // Pas besoin puisqu'on est ESM **only**
      }),
      symfonyPlugin({
        originOverride: "https://mon-indemnisation.justice.gouv.dev",
        stimulus: false,
      }),
      react(),
    ],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url)),
      },
    }, // TODO retirer la console et le debugger au build https://github.com/vitejs/vite/discussions/7920#discussioncomment-2709119
    esbuild: false,
    build: {
      target: "es2015",
      modulePreload: false,
      sourcemap: true,
      minify: mode === "production",
      emptyOutDir: false,
      rollupOptions: {
        // TODO: test to export vendors as manualChunks https://gist.github.com/emmiep/8fb5a2887a8ec007b319f0abff04ffb1#file-rollup-config-js-L18
        input: {
          ...{
            "requerant/dossier/tester_mon_eligibilite":
              "./src/apps/requerant/dossier/tester_mon_eligibilite.tsx",
            "requerant/dossier/creation_de_compte":
              "./src/apps/requerant/dossier/creation_de_compte.tsx",
            "requerant/dossier/deposer_mon_dossier":
              "./src/apps/requerant/dossier/deposer_mon_dossier.tsx",
            "requerant/dossier/consulter_la_decision":
              "./src/apps/requerant/dossier/consulter_la_decision.tsx",
            // Espace agent
            "agent/fip6": "./src/apps/agent/fip6/fip6.tsx",
            "agent/dossiers/recherche":
              "./src/apps/agent/fip6/dossiers/recherche_app.tsx",
            "agent/dossiers/consulter":
              "./src/apps/agent/fip6/dossiers/consultation_app.tsx",
            // Espace FDO
            "agent/fdo": path.join(__dirname, "./src/apps/agent/fdo/fdo.tsx"),
          },
        },
        output: {
          manualChunks: {
            models: ["@/common/models"],
          },
        },
      },
    },
    server: {
      host: "0.0.0.0",
      allowedHosts: true,
    },
  };
});
