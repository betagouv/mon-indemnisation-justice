import { AgentManagerInterface } from "@fdo/services/agent";
import * as Sentry from "@sentry/browser";
import { createRouter } from "@tanstack/react-router";
import { container } from "../container";
import { AgentFDOContexte } from "./contexte.ts";
import { routeTree } from "./routeur-fdo.gen";

const creerRouteurFDO = (context: AgentFDOContexte) =>
  createRouter({
    routeTree,
    defaultPreload: "intent",
    defaultStaleTime: 5000,
    scrollRestoration: true,
    // Préfixage des URLS : le chemin /agent/fdo dans le navigateur pointe sur la racine du dossier "routes"
    rewrite: {
      // URL navigateur vers URL routeur
      input: ({ url }) => {
        url.pathname = url.pathname.replace(/^\/agent\/fdo/, "") || "/";

        return url;
      },
      // URL routeur vers URL navigateur
      output: ({ url }) => {
        if (!url.pathname.match(/^\/agent\/fdo\//)) {
          url.pathname = `/agent/fdo/${url.pathname.replace(/^\//, "") || ""}`;
        }

        return url;
      },
    },
    context,
  });

// Création du router Tanstack
let RouteurFDO;
await container
  .get(AgentManagerInterface.$)
  .moi()
  .then((contexte: AgentFDOContexte) => {
    Sentry.setTag("app", "fdo");
    Sentry.setUser({
      id: contexte.agent.id,
      email: contexte.agent.courriel,
      username: contexte.agent.nomComplet(),
    });
    RouteurFDO = creerRouteurFDO(contexte);
  });

declare module "@tanstack/react-router" {
  interface Register {
    router: ReturnType<typeof creerRouteurFDO>;
  }

  interface Location {
    state: {
      session: any;
    };
  }
}

export { RouteurFDO };
