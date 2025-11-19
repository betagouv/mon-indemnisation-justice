import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierATransmettre } from "@/apps/agent/fip6/dossiers/components/ListeDossierATransmettre.tsx";
import { ListeDossierAAttribuer } from "@/apps/agent/fip6/dossiers/components/ListeDossierAAttribuer.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/a-attribuer")({
  component: ListeDossierAAttribuer,
});
