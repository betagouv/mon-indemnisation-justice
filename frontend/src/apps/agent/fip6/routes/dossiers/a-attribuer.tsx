import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierATransmettre } from "@/apps/agent/fip6/dossiers/components/ListeDossierATransmettre.tsx";
import { ListeDossierAAttribuer } from "@/apps/agent/fip6/dossiers/components/ListeDossierAAttribuer.tsx";

export const Route = createFileRoute("/dossiers/a-attribuer")({
  component: ListeDossierAAttribuer,
});
