import { sentryOptions } from "@/apps/sentry.ts";
import "@/common/polyfill.ts";
import "reflect-metadata";

import "@/style/agents.css";

import { ConsultationDossierApp } from "@/apps/agent/fip6/dossiers/components/ConsultationDossierApp.tsx";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
import { Agent, DossierDetail, Redacteur } from "@/common/models";
import { container } from "@/common/services/agent";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { plainToInstance } from "class-transformer";
import { Provider } from "inversify-react";
import React, { StrictMode } from "react";
import ReactDOM from "react-dom/client";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
});

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const args = JSON.parse(
  document.getElementById("react-arguments")?.textContent ?? "",
);

Redacteur.charger(args.redacteurs ?? []);

const agent = plainToInstance(Agent, args.agent);

const dossier = plainToInstance(DossierDetail, args.dossier, {
  enableImplicitConversion: true,
});

ReactDOM.createRoot(
  document.getElementById(
    "react-app-agent-consultation-dossiers",
  ) as HTMLElement,
  sentryOptions,
).render(
  <StrictMode>
    <Provider container={container}>
      <ConsultationDossierApp dossier={dossier} agent={agent} />
    </Provider>
  </StrictMode>,
);
