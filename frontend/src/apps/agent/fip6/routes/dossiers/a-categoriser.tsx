import { createFileRoute } from "@tanstack/react-router";
import { ListeDossierACategoriser } from "@/apps/agent/fip6/dossiers/components/ListeDossierACategoriser.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/a-categoriser")({
  component: ListeDossierACategoriser,
});
