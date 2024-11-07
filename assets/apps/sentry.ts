// import Sentry from your framework SDK (e.g. @sentry/react) instead of @sentry/browser
import * as Sentry from "@sentry/browser";

if (import.meta.env?.VITE_SENTRY_DSN) {
  Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,

    integrations: [
      Sentry.feedbackIntegration({
        // Additional SDK configuration goes in here, for example:
        colorScheme: "system",
      }),
    ],
  });
}
