import { ListeDossierRejetASigner } from "@fip6/dossiers/components/ListeDossierRejetASigner.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/rejet-a-signer")({
  component: ListeDossierRejetASigner,
});
