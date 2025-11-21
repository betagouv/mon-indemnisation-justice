import { routeTree } from "../routeur/routeur-fdo.gen.ts";
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
      queryFn: async () => await container.get(AgentManagerInterface.$).moi(),
    }),
    incarnePar: queryClient.fetchQuery({
      queryKey: ["moi-agent-fdo"],
      queryFn: async () => await container.get(AgentManagerInterface.$).moi(),
    }),
  },
});
