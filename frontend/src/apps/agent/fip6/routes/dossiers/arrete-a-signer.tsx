import { ListeDossierArreteASigner } from "@fip6/dossiers/components/ListeDossierArreteASigner.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/arrete-a-signer")({
  component: ListeDossierArreteASigner,
});
