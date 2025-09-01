import "reflect-metadata";
import { sentryOptions } from "@/apps/sentry";
import "@/common/polyfill";

import "@/style/agents.css";

import { RechercheDossierApp } from "@/apps/agent/dossiers/components/RechercheDossierApp";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
import { plainToInstance } from "class-transformer";
import React from "react";
import ReactDOM from "react-dom/client";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { Agent, Redacteur } from "@/common/models/";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const queryClient = new QueryClient();

const args = JSON.parse(document.getElementById("react-arguments").textContent);

Redacteur.charger(args.redacteurs ?? []);

const agent = plainToInstance(Agent, args.agent);

ReactDOM.createRoot(
  document.getElementById("react-app-agent-recherche-dossiers"),
  sentryOptions,
).render(
  <QueryClientProvider client={queryClient}>
    <RechercheDossierApp agent={agent}></RechercheDossierApp>,
  </QueryClientProvider>,
);
