import { ListeDossierDeclarationAcceptationAVerifier } from "@fip6/dossiers/components/ListeDossierDeclarationAcceptationAVerifier.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/acceptation-a-verifier")({
  component: ListeDossierDeclarationAcceptationAVerifier,
});
