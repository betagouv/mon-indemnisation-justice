import {createFileRoute} from "@tanstack/react-router";
import {ListeDossierArreteASigner} from "@/apps/agent/dossiers/components/ListeDossierArreteASigner.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/arrete-a-signer")({
    component: ListeDossierArreteASigner,
})