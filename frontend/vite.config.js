import { defineConfig } from "vite";

import { fileURLToPath, URL } from "node:url";
import symfonyPlugin from "vite-plugin-symfony";
import reactPlugin from "@vitejs/plugin-react";
import copy from "rollup-plugin-copy";
import legacy from "@vitejs/plugin-legacy";
import autoprefixer from "autoprefixer";
import nested from "postcss-nested";

// Vite will ignore native environment variables, unless they're declared in local `.env` file
// (see https://github.com/vitejs/vite/issues/562)
// Here we load them dynamically
import.meta.env = import.meta.env ?? {};

Object.entries(process.env)
  .filter(([key, value]) => key.startsWith("VITE_"))
  .forEach(([key, value]) => (import.meta.env[key] = value));

export default defineConfig(({ command, mode }) => {
  const outDir = `../public/${mode.startsWith("dev") ? "preview" : "build"}`;
  const base = mode.startsWith("dev") ? "/preview" : "/build";

  return {
    base,
    cacheDir: import.meta.env.VITE_CACHE_DIR ?? "node_modules/.vite",
    css: {
      postcss: {
        plugins: [autoprefixer(), nested()],
      },
    },
    plugins: [
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
        build: {
          manifest: true,
          //outDir,
        },
      }),
      reactPlugin(),
      copy({
        targets: [
          { src: "node_modules/@gouvfr/dsfr/dist/*", dest: "../public/dsfr" },
          {
            src: "node_modules/remixicon/*",
            dest: "../public/remixicon",
          },
        ],
        verbose: true,
      }),
    ],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url)),
      },
    }, // TODO retirer la console et le debugger au build https://github.com/vitejs/vite/discussions/7920#discussioncomment-2709119
    esbuild: false,
    build: {
      outDir,
      target: "es2015",
      modulePreload: true,
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
            "agent/gestion_agents":
              "./src/apps/agent/gestion_agents/gestion_agents_app.tsx",
            "agent/dossiers/recherche":
              "./src/apps/agent/dossiers/recherche_app.tsx",
            "agent/dossiers/consulter":
              "./src/apps/agent/dossiers/consultation_app.tsx",
            "agent/dossiers/dossiers_a_transmettre":
              "./src/apps/agent/dossiers/dossiers_a_transmettre.tsx",
            "agent/dossiers/dossiers_en_attente_indemnisation":
              "./src/apps/agent/dossiers/dossiers_en_attente_indemnisation.tsx",
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
