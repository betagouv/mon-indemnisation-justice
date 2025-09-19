import {createFileRoute} from "@tanstack/react-router";
import {ListeDossierATransmettre} from "@/apps/agent/dossiers/components/ListeDossierATransmettre.tsx";
import {ListeDossierAAttribuer} from "@/apps/agent/dossiers/components/ListeDossierAAttribuer.tsx";
import {ListeDossierAInstruire} from "@/apps/agent/dossiers/components/ListeDossierAInstruire.tsx";
import {ListeDossierAVerifier} from "@/apps/agent/dossiers/components/ListeDossierAVerifier.tsx";

export const Route = createFileRoute("/agent/fip6/dossiers/a-verifier")({
    component: ListeDossierAVerifier,
})