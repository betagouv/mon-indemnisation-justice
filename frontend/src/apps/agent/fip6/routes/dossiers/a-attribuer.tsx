import { ListeDossierAAttribuer } from "@/apps/agent/fip6/dossiers/components/ListeDossierAAttribuer.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/a-attribuer")({
  component: ListeDossierAAttribuer,
});
