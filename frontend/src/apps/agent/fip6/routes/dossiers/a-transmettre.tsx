import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierATransmettre } from "@/apps/agent/fip6/dossiers/components/ListeDossierATransmettre.tsx";

export const Route = createFileRoute("/dossiers/a-transmettre")({
  component: ListeDossierATransmettre,
});
