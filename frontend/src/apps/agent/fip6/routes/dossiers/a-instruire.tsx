import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierAInstruire } from "@/apps/agent/fip6/dossiers/components/ListeDossierAInstruire.tsx";
import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";

export const Route = createFileRoute("/agent/fip6/dossiers/a-instruire")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    console.log(context.agent.estRedacteur());
  },
  component: ListeDossierAInstruire,
});
