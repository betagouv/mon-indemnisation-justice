/// <reference types="./types" />

import { sentryVitePlugin } from "@sentry/vite-plugin";
import { tanstackRouter } from "@tanstack/router-plugin/vite";
import legacy from "@vitejs/plugin-legacy";
import { default as react } from "@vitejs/plugin-react";
import { fileURLToPath, URL } from "node:url";
import * as path from "path";
import { defineConfig, loadEnv, UserConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

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
      // Espace rédacteur (FIP6)
      tanstackRouter({
        target: "react",
        autoCodeSplitting: true,
        generatedRouteTree: "./src/apps/agent/fip6/routeur/routeur-fip6.gen.ts",
        routesDirectory: "./src/apps/agent/fip6/routes/",
      }),
      // Espace Forces de l'ordre (FDO)
      tanstackRouter({
        target: "react",
        autoCodeSplitting: true,
        generatedRouteTree: "./src/apps/agent/fdo/routeur/routeur-fdo.gen.ts",
        routesDirectory: "./src/apps/agent/fdo/routes/",
      }),
      // Espace requérant
      tanstackRouter({
        target: "react",
        autoCodeSplitting: true,
        generatedRouteTree:
          "./src/apps/visiteur/routeur/routeur-visiteur.gen.ts",
        routesDirectory: "./src/apps/visiteur/routes/",
      }),
      /** Here  */
      tanstackRouter({
        target: "react",
        autoCodeSplitting: true,
        generatedRouteTree:
          "./src/apps/requerant/routeur/routeur-requerant.gen.ts",
        routesDirectory: "./src/apps/requerant/routes/",
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
      sentryVitePlugin({
        org: "betagouv",
        project: "mon-indemnisation-justice",
        url: process.env.VITE_SENTRY_URL || "",
        authToken: process.env.VITE_SENTRY_AUTH_TOKEN,
        release: {
          name: process.env.VITE_MIJ_VERSION || "dev",
        },
        sourcemaps: {
          // As you're enabling client source maps, you probably want to delete them after they're uploaded to Sentry.
          // Set the appropriate glob pattern for your output folder - some glob examples below:
          filesToDeleteAfterUpload: [
            "./**/*.map",
            ".*/**/public/**/*.map",
            "./dist/**/client/**/*.map",
            "./build/**/client/**/*.map",
          ],
        },
      }),
    ],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url)),
      },
    }, // TODO retirer la console et le debugger au build https://github.com/vitejs/vite/discussions/7920#discussioncomment-2709119
    oxc: {
      target: "esnext",
    },
    assetsInclude: ["*.pdf"],
    build: {
      target: "esnext",
      modulePreload: false,
      sourcemap: true,
      minify: mode === "production",
      emptyOutDir: false,
      // Voir https://rolldown.rs/reference/
      rolldownOptions: {
        input: {
          // Espace visiteur
          visiteur: "./src/apps/visiteur/visiteur.tsx",
          // Espace requérant
          "requerant/dossier/tester_mon_eligibilite":
            "./src/apps/requerant/dossier/tester_mon_eligibilite.tsx",
          "requerant/dossier/creation_de_compte":
            "./src/apps/requerant/dossier/creation_de_compte.tsx",
          requerant: "./src/apps/requerant/requerant.tsx",
          // Espace agent FIP6
          "agent/fip6": "./src/apps/agent/fip6/fip6.tsx",
          "agent/dossiers/recherche":
            "./src/apps/agent/fip6/dossiers/recherche_app.tsx",
          "agent/dossiers/consulter":
            "./src/apps/agent/fip6/dossiers/consultation_app.tsx",
          // Espace agent FDO
          "agent/fdo": path.join(__dirname, "./src/apps/agent/fdo/fdo.tsx"),
        },
        output: {
          manualChunks: {
            models: ["@/common/models"],
          },
      },
    },
    server: {
      host: "0.0.0.0",
      allowedHosts: true,
    },
  };
});
