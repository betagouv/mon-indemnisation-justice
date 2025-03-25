import "@/common/polyfill";
import { ValidationAgentApp } from "@/apps/agent/gestion_agents/components";
import {
  Administration,
  Agent,
  RequeteAgentValidationListe,
} from "@/apps/agent/gestion_agents/models/index.ts";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
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

const {
  administrations,
  agents: _agts,
  preDeclaration = false,
  titre = "Gestion des agents",
} = JSON.parse(document.getElementById("react-arguments").textContent);

Administration.charger(administrations);

const agents: Agent[] = plainToInstance(Agent, _agts as any[]);

const validations = new RequeteAgentValidationListe(agents);

const root = ReactDOM.createRoot(
  document.getElementById("react-app-agent-gestion-agents"),
);

root.render(
  <div className="fr-container fr-my-5w">
    <ValidationAgentApp
      liste={validations}
      preDeclaration={preDeclaration}
      titre={titre}
    />
  </div>,
);
