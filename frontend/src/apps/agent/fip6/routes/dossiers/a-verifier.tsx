import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierAVerifier } from "@fip6/dossiers/components/ListeDossierAVerifier.tsx";

export const Route = createFileRoute("/dossiers/a-verifier")({
  component: ListeDossierAVerifier,
});
