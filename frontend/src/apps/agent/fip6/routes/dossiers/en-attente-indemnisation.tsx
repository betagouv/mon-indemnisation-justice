import { ListeDossierEnAttenteIndemnisation } from "@fip6/dossiers/components/ListeDossierEnAttenteIndemnisation.tsx";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/dossiers/en-attente-indemnisation")({
  component: ListeDossierEnAttenteIndemnisation,
});
