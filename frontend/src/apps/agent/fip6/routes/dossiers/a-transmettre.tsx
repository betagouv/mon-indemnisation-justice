import { ListeDossierATransmettre } from "@fip6/dossiers/components/ListeDossierATransmettre.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/a-transmettre")({
  component: ListeDossierATransmettre,
});
