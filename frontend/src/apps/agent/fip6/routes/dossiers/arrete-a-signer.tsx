import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierArreteASigner } from "@/apps/agent/fip6/dossiers/components/ListeDossierArreteASigner.tsx";

export const Route = createFileRoute("/dossiers/arrete-a-signer")({
  component: ListeDossierArreteASigner,
});
