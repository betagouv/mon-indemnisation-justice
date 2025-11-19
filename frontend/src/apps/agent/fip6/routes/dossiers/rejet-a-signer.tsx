import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierRejetASigner } from "@/apps/agent/fip6/dossiers/components/ListeDossierRejetASigner.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/rejet-a-signer")({
  component: ListeDossierRejetASigner,
});
