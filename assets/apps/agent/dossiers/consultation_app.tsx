import "@/common/polyfill";
import { Sentry } from "@/apps/sentry";
import "reflect-metadata";

import "@/style/agents.css";

import { ConsultationDossierApp } from "@/apps/agent/dossiers/components/ConsultationDossierApp";
import { Agent, Redacteur } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
import { plainToInstance } from "class-transformer";
import React, { StrictMode } from "react";
import ReactDOM from "react-dom/client";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

// Déclaration d'une variable globale indiquant si des opérations sont en attentes
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
  document.getElementById("react-app-agent-consultation-dossiers"),
  {
    ...(import.meta.env?.VITE_SENTRY_DSN
      ? {
          onUncaughtError: Sentry.reactErrorHandler((error, errorInfo) => {
            console.warn("Uncaught error", error, errorInfo.componentStack);
          }),
          // Callback called when React catches an error in an ErrorBoundary.
          onCaughtError: Sentry.reactErrorHandler(),
          // Callback called when React automatically recovers from errors.
          onRecoverableError: Sentry.reactErrorHandler(),
        }
      : {}),
  },
).render(
  <StrictMode>
    <ConsultationDossierApp dossier={dossier} agent={agent} />
  </StrictMode>,
);
