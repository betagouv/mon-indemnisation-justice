import { createFileRoute } from "@tanstack/react-router";
import { ValidationAgentApp } from "@/apps/agent/fip6/gestion_agents/components";
import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";
import { RoleAgent } from "@/common/models/Agent.ts";

export const Route = createFileRoute("/agents/gestion")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    if (!context.agent.aRole(RoleAgent.GESTION_PERSONNEL)) {
      alert("Pas autorisé");
    }
  },
  loader: ({ context }) => {
    return {
      context,
    };
  },
  component: ValidationAgentApp,
});
