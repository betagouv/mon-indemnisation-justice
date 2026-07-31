import { ListeDossierPropositionASigner } from "@fip6/dossiers/components/ListeDossierPropositionASigner.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/proposition-a-signer")({
  component: ListeDossierPropositionASigner,
});
