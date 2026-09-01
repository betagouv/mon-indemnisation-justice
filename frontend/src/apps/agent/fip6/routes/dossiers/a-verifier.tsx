import { ListeDossierAVerifier } from "@fip6/dossiers/components/ListeDossierAVerifier.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/a-verifier")({
  component: ListeDossierAVerifier,
});
