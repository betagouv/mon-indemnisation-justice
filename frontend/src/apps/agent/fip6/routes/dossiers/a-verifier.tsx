import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierAVerifier } from "@/apps/agent/fip6/dossiers/components/ListeDossierAVerifier.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/a-verifier")({
  component: ListeDossierAVerifier,
});
