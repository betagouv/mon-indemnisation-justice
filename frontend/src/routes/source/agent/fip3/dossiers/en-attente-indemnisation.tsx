import {
    ListeDossierEnAttenteIndemnisation
} from "@/apps/agent/dossiers/components/ListeDossierEnAttenteIndemnisation.tsx";
import {createFileRoute} from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fip3/dossiers/en-attente-indemnisation")({
    component: ListeDossierEnAttenteIndemnisation,
})