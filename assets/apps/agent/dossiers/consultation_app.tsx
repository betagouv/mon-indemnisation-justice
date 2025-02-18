import "reflect-metadata";
import { ConsultationDossierApp } from "@/apps/agent/dossiers/components/ConsultationDossierApp";
import { Agent, Redacteur } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { disableReactDevTools } from "@/react/services/devtools.js";
import { plainToInstance } from "class-transformer";
import React from "react";
import ReactDOM from "react-dom/client";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const args = JSON.parse(document.getElementById("react-arguments").textContent);

Redacteur.charger(args.redacteurs ?? []);

const agent = plainToInstance(Agent, args.agent);

const dossier = plainToInstance(DossierDetail, args.dossier, {
  enableImplicitConversion: true,
});

ReactDOM.createRoot(
  document.getElementById("react-app-agent-consultation-dossiers"),
).render(<ConsultationDossierApp dossier={dossier} agent={agent} />);
