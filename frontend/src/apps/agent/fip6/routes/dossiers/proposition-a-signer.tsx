import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierPropositionASigner } from "@/apps/agent/fip6/dossiers/components/ListeDossierPropositionASigner.tsx";

export const Route = createFileRoute(
  "/dossiers/proposition-a-signer",
)({
  component: ListeDossierPropositionASigner,
});
