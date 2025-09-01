import "reflect-metadata";

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools";
import ReactDOM from "react-dom/client";
import { sentryOptions } from "@/apps/sentry.ts";
import React, { StrictMode } from "react";
import { Provider } from "inversify-react";
import { container } from "@/common/services/agent";
import { ListeDossierATransmettre } from "@/apps/agent/dossiers/components/ListeDossierATransmettre.tsx";

startReactDsfr({ defaultColorScheme: "system" });

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

ReactDOM.createRoot(
  document.getElementById("react-app") as HTMLElement,
  sentryOptions,
).render(
  <StrictMode>
    <Provider container={container}>
      <ListeDossierATransmettre />
    </Provider>
  </StrictMode>,
);
