import {createFileRoute} from "@tanstack/react-router";
import {ListeDossierATransmettre} from "@/apps/agent/dossiers/components/ListeDossierATransmettre.tsx";
import {ListeDossierAAttribuer} from "@/apps/agent/dossiers/components/ListeDossierAAttribuer.tsx";

export const Route = createFileRoute("/agent/fip3/dossiers/a-attribuer")({
    component: ListeDossierAAttribuer,
})