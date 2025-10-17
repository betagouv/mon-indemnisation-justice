import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierAInstruire } from "@/apps/agent/dossiers/components/ListeDossierAInstruire.tsx";
import { AgentContext } from "@/routers/contexts/AgentContext.ts";

export const Route = createFileRoute("/agent/fip6/dossiers/a-instruire")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    const agent = await context.agent;
    console.log(agent.estRedacteur());
  },
  component: ListeDossierAInstruire,
});
