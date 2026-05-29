import { AgentContext } from "@/apps/agent/_commun/contexts";
import { routeTree } from "@/apps/agent/fip6/routeur/routeur-fip6.gen.ts";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import * as Sentry from "@sentry/browser";
import { createRouter } from "@tanstack/react-router";
import { container } from "../container.ts";

const creerRouteurFIP6 = (context: AgentContext) =>
  createRouter({
    routeTree,
    defaultPreload: "intent",
    defaultStaleTime: 5000,
    scrollRestoration: true,
    rewrite: {
      // URL navigateur vers URL routeur
      input: ({ url }) => {
        url.pathname = url.pathname.replace(/^\/agent\/fip6/, "") || "/";

        return url;
      },
      // URL routeur vers URL navigateur
      output: ({ url }) => {
        if (!url.pathname.match(/^\/agent\/fip6\//)) {
          url.pathname = `/agent/fip6/${url.pathname.replace(/^\//, "") || ""}`;
        }

        return url;
      },
    },
    context,
  });

// Création du router Tanstack
let RouteurFIP6: ReturnType<typeof creerRouteurFIP6>;
await container
  .get(AgentManagerInterface.$)
  .moi()
  .then((context: AgentContext) => {
    Sentry.setTag("app", "fip6");
    Sentry.setUser({
      id: context.agent.id,
      email: context.agent.courriel,
      username: context.agent.nom,
    });
    RouteurFIP6 = creerRouteurFIP6(context);
  });

declare module "@tanstack/react-router" {
  interface Register {
    router: ReturnType<typeof creerRouteurFIP6>;
  }

  interface Location {
    state: {
      session: any;
    };
  }
}

export { RouteurFIP6 };
