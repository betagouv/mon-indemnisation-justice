import { routeTree } from "../routeur/routeur-fdo.gen.ts";
import { container } from "./_container.ts";
import { createRouter } from "@tanstack/react-router";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { AgentContext } from "@/apps/agent/_commun/contexts";

// Création du router Tanstack

let router;
await container
  .get(AgentManagerInterface.$)
  .moi()
  .then((context: AgentContext) => {
    router = createRouter({
      routeTree,
      defaultPreload: "intent",
      defaultStaleTime: 5000,
      scrollRestoration: true,
      context,
    });
  });

export { router };
