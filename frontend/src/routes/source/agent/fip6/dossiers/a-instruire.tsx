import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierAInstruire } from "@/apps/agent/dossiers/components/ListeDossierAInstruire.tsx";
import { AgentContext } from "@/routes/contexts/AgentContext.ts";

export const Route = createFileRoute("/agent/fip6/dossiers/a-instruire")({
  beforeLoad: ({ context }: { context: AgentContext }) => {
    // TODO redirection sinon
    console.log(context.agent.estRedacteur());
  },
  component: ListeDossierAInstruire,
});
