import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierATransmettre } from "@/apps/agent/fip6/dossiers/components/ListeDossierATransmettre.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/a-transmettre")({
  component: ListeDossierATransmettre,
});
