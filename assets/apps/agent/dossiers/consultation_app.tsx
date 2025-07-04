import "reflect-metadata";
import { sentryOptions } from "@/apps/sentry";
import "@/common/polyfill";

import "@/style/agents.css";

import { ConsultationDossierApp } from "@/apps/agent/dossiers/components/ConsultationDossierApp";
import {
  Agent,
  DocumentType,
  DossierDetail,
  Redacteur,
} from "@/apps/agent/dossiers/models";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
import { plainToInstance } from "class-transformer";
import React, { StrictMode } from "react";
import ReactDOM from "react-dom/client";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

declare global {
  interface Window {
    dsfr: any;
  }
}

const args = JSON.parse(document.getElementById("react-arguments").textContent);

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
    <ConsultationDossierApp dossier={dossier} agent={agent} />
  </StrictMode>,
);
