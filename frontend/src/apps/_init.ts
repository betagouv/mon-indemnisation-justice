import "es-iterator-helpers/auto";
import * as Sentry from "@sentry/react";
import {RootOptions} from "react-dom/client";
import {startReactDsfr} from "@codegouvfr/react-dsfr/spa";
import {ColorScheme} from "@codegouvfr/react-dsfr/useIsDark";
import {disableReactDevTools} from "@/apps/requerant/dossier/services/devtools";

startReactDsfr({defaultColorScheme: localStorage.getItem('scheme') as ColorScheme ?? "system"});

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
    import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

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

export {sentryOptions};