import {createFileRoute} from "@tanstack/react-router";
import {ListeDossierPropositionASigner} from "@/apps/agent/dossiers/components/ListeDossierPropositionASigner.tsx";
import {ListeDossierRejetASigner} from "@/apps/agent/dossiers/components/ListeDossierRejetASigner.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/rejet-a-signer")({
    component: ListeDossierRejetASigner,
})