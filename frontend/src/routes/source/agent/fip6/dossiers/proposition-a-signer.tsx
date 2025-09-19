import {createFileRoute} from "@tanstack/react-router";
import {ListeDossierPropositionASigner} from "@/apps/agent/dossiers/components/ListeDossierPropositionASigner.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/proposition-a-signer")({
    component: ListeDossierPropositionASigner,
})