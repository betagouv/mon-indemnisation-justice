import { routeTree } from "@/apps/agent/fip6/routeur/routeur-fip6.gen.ts";
import { container } from "./_container.ts";
import { queryClient } from "./_queryClient.ts";
import { createRouter } from "@tanstack/react-router";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";

// Création du router Tanstack
export const router = createRouter({
  routeTree,
  defaultPreload: "intent",
  defaultStaleTime: 5000,
  scrollRestoration: true,
  context: {
    agent: queryClient.fetchQuery({
      queryKey: ["moi-agent-fdo"],
      queryFn: () => container.get(AgentManagerInterface.$).moi(),
    }),
  },
});
