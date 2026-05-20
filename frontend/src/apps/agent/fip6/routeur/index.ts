import { AgentContext } from "@/apps/agent/_commun/contexts";
import { routeTree } from "@/apps/agent/fip6/routeur/routeur-fip6.gen.ts";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import * as Sentry from "@sentry/browser";
import { createRouter } from "@tanstack/react-router";
import { container } from "../container.ts";

const createFIP6Router = (context: AgentContext) =>
  createRouter({
    routeTree,
    defaultPreload: "intent",
    defaultStaleTime: 5000,
    scrollRestoration: true,
    context,
  });

// Création du router Tanstack
let RouteurFIP6: ReturnType<typeof createFIP6Router>;
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
    RouteurFIP6 = createFIP6Router(context);
  });

declare module "@tanstack/react-router" {
  interface Register {
    router: ReturnType<typeof createFIP6Router>;
  }

  interface Location {
    state: {
      session: any;
    };
  }
}

export { RouteurFIP6 };
