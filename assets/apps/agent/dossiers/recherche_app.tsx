import "reflect-metadata";
import { sentryOptions } from "@/apps/sentry";
import "@/common/polyfill";

import "@/style/agents.css";

import { RechercheDossierApp } from "@/apps/agent/dossiers/components/RechercheDossierApp";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
import { plainToInstance } from "class-transformer";
import { autorun, observable, runInAction } from "mobx";
import { IObservableArray } from "mobx/src/internal";
import React from "react";
import ReactDOM from "react-dom/client";
import {
  Agent,
  Redacteur,
  DossierApercu,
  RechercheDossier,
} from "@/common/models/";

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

const recherche = RechercheDossier.fromURL();
let dossiers: IObservableArray<DossierApercu> = observable([]);

autorun(() => {
  history.replaceState(null, "", recherche.toURL());

  fetch(`${location.pathname}.json?${recherche.buildURLParameters()}`)
    .then((response) => response.json())
    .then((data: []) =>
      runInAction(() => dossiers.replace(plainToInstance(DossierApercu, data))),
    );
});

ReactDOM.createRoot(
  document.getElementById("react-app-agent-recherche-dossiers"),
  sentryOptions,
).render(
  <RechercheDossierApp
    recherche={recherche}
    agent={agent}
    dossiers={dossiers}
  ></RechercheDossierApp>,
);
