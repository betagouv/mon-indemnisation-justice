import { AgentContext } from "@/apps/agent/_commun/contexts";
import { RechercherRoute, validerParametres } from "@/apps/agent/fip6/composants/routes/RechercherRoute.tsx";
import { container } from "@/apps/agent/fip6/container";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import "@/style/agents.css";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/rechercher")({
  loader: async ({ context }: { context: AgentContext }) => ({
    agent: context.agent,
    // TODO transformer par un appel API
    redacteurs: await container.get(AgentManagerInterface.$).redacteurs(),
  }),
  validateSearch: validerParametres,
  component: RechercherRoute,
});
