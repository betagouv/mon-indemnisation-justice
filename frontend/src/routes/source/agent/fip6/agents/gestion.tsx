import { createFileRoute } from "@tanstack/react-router";
import { ValidationAgentApp } from "@/apps/agent/gestion_agents/components";
import { AgentContext } from "@/routes/contexts/AgentContext.ts";

export const Route = createFileRoute("/agent/fip6/agents/gestion")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    // TODO redirection sinon
    console.log(await context.agent);
  },
  component: ValidationAgentApp,
});
