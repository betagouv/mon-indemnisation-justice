// import Sentry from your framework SDK (e.g. @sentry/react) instead of @sentry/browser
import * as Sentry from "@sentry/react";

if (import.meta.env?.VITE_SENTRY_DSN) {
  Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,
    environment: "prod",
  });
}

export { Sentry };
