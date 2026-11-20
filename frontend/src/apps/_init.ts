import "reflect-metadata";

import { ApplicationRechargeur } from "@/common/services/ApplicationRechargeur";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools";
import * as Sentry from "@sentry/react";
import "es-iterator-helpers/auto";
import { RootOptions } from "react-dom/client";
import { z } from "zod";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// Échec de préchargement d'un chunk par Vite (typiquement après un redéploiement) : on recharge la page pour récupérer
// la nouvelle version. `preventDefault()` empêche alors l'erreur d'être propagée (et d'afficher l'écran d'erreur).
// cf. https://vite.dev/guide/build#load-error-handling
window.addEventListener("vite:preloadError", (event) => {
  if (ApplicationRechargeur.rechargerSiPossible()) {
    event.preventDefault();
  }
});

// Polyfill de Promise.withResolvers, requis pour `react-pdf`
if (typeof Promise.withResolvers === "undefined") {
  if (window)
    // @ts-expect-error
    // This does not exist outside of polyfill which this is doing
    window.Promise.withResolvers = function () {
      let resolve, reject;
      const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
      });
      return { promise, resolve, reject };
    };
}

// Désactiver le "unsafe eval" de zod qui lève une erreur CSP (https://github.com/colinhacks/zod/issues/5414)
z.config({ jitless: true });

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}
let sentryOptions: RootOptions = {};

if (import.meta.env?.VITE_SENTRY_DSN) {
  Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,
    environment: import.meta.env.APP_ENV || "prod",
    integrations: [
      Sentry.browserTracingIntegration(),
      Sentry.replayIntegration(),
    ],
    tracesSampleRate: 0.5,
    //tracePropagationTargets: ["localhost"],

    debug: (import.meta.env.APP_ENV || "prod") != "prod",
    release: import.meta.env.COMMIT_ID || "",
  });

  sentryOptions = {
    onUncaughtError: Sentry.reactErrorHandler((error, errorInfo) => {
      console.warn("Uncaught error", error, errorInfo.componentStack);
    }),
    // Callback called when React catches an error in an ErrorBoundary.
    onCaughtError: Sentry.reactErrorHandler(),
    // Callback called when React automatically recovers from errors.
    onRecoverableError: Sentry.reactErrorHandler(),
  };
}

export { sentryOptions };
