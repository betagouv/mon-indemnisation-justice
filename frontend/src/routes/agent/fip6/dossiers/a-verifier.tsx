import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierAVerifier } from "@/apps/agent/dossiers/components/ListeDossierAVerifier.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/a-verifier")({
  component: ListeDossierAVerifier,
});
