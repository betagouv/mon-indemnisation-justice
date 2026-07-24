import { ListeDossierACategoriser } from "@fip6/dossiers/components/ListeDossierACategoriser.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/a-categoriser")({
  component: ListeDossierACategoriser,
});
