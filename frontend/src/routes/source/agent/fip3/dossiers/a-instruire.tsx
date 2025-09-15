import {createFileRoute} from "@tanstack/react-router";
import {ListeDossierATransmettre} from "@/apps/agent/dossiers/components/ListeDossierATransmettre.tsx";
import {ListeDossierAAttribuer} from "@/apps/agent/dossiers/components/ListeDossierAAttribuer.tsx";
import {ListeDossierAInstruire} from "@/apps/agent/dossiers/components/ListeDossierAInstruire.tsx";

export const Route = createFileRoute("/agent/fip3/dossiers/a-instruire")({
    component: ListeDossierAInstruire,
})