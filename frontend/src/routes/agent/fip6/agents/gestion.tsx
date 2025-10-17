import { createFileRoute } from "@tanstack/react-router";
import { ValidationAgentApp } from "@/apps/agent/gestion_agents/components";
import { AgentContext } from "@/routers/contexts/AgentContext.ts";
import { RoleAgent } from "@/common/models/Agent.ts";

export const Route = createFileRoute("/agent/fip6/agents/gestion")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    const agent = await context.agent;
    if (!agent.aRole(RoleAgent.GESTION_PERSONNEL)) {
      alert("Pas autorisé");
    }
  },
  component: ValidationAgentApp,
});
