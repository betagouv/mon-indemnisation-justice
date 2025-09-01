import * as Sentry from "@sentry/react";
import { RootOptions } from "react-dom/client";

let sentryOptions: RootOptions = {};

if (import.meta.env?.VITE_SENTRY_DSN) {
  Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,
    environment: import.meta.env.APP_ENV || "prod",
    integrations: [
      Sentry.browserTracingIntegration(),
      Sentry.replayIntegration(),
    ],
    tracesSampleRate: 1.0,
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
