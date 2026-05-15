import { AgentContext } from "@/apps/agent/_commun/contexts";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import * as Sentry from "@sentry/browser";
import { createRouter } from "@tanstack/react-router";
import { routeTree } from "../routeur/routeur-fdo.gen.ts";
import { container } from "./_container.ts";

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
