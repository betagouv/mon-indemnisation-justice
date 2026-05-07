import { routeTree } from "../routeur/routeur-fdo.gen.ts";
import { container } from "./_container.ts";
import { createRouter } from "@tanstack/react-router";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { AgentContext } from "@/apps/agent/_commun/contexts";
import * as Sentry from "@sentry/browser";

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
    RouteurFDO = createRouter({
      routeTree,
      defaultPreload: "intent",
      defaultStaleTime: 5000,
      scrollRestoration: true,
      context,
    });
  });

export { RouteurFDO };
