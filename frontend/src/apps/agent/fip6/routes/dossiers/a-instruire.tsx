import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";
import { ListeDossierAInstruire } from "@fip6/dossiers/components/ListeDossierAInstruire.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/a-instruire")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    // TDOO: s'assurer que l'agent est rédacteur
  },
  component: ListeDossierAInstruire,
});
