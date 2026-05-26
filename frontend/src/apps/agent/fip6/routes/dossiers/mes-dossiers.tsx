import { AgentContext } from "@/apps/agent/_commun/contexts";
import {
  RechercherRoute,
  validerParametres,
} from "@/apps/agent/fip6/composants/routes/RechercherRoute.tsx";
import { container } from "@/apps/agent/fip6/container";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import "@/style/agents.css";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/mes-dossiers")({
  loader: async ({ context }: { context: AgentContext }) => ({
    agent: context.agent,
    // TODO transformer par un appel API
    redacteurs: await container.get(AgentManagerInterface.$).redacteurs(),
  }),
  validateSearch: async (
    search: Record<string, string | string[] | undefined>,
  ) => {
    return {
      ...validerParametres(search),
      a: "17",
    };
  },
  component: RechercherRoute,
});
