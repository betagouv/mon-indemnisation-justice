import { AgentContext } from "@/apps/agent/_commun/contexts";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import * as Sentry from "@sentry/browser";
import { createRouter } from "@tanstack/react-router";
import { container } from "../container";
import { routeTree } from "./routeur-fdo.gen";

const creerRouteurFDO = (context: AgentContext) =>
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
  .then((context: AgentContext) => {
    Sentry.setTag("app", "fdo");
    Sentry.setUser({
      id: context.agent.id,
      email: context.agent.courriel,
      username: context.agent.nomComplet(),
    });
    RouteurFDO = creerRouteurFDO(context);
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
