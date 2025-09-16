import {BeforeLoadContextOptions, createFileRoute} from "@tanstack/react-router";
import {ListeDossierATransmettre} from "@/apps/agent/dossiers/components/ListeDossierATransmettre.tsx";
import {ListeDossierAAttribuer} from "@/apps/agent/dossiers/components/ListeDossierAAttribuer.tsx";
import {ListeDossierAInstruire} from "@/apps/agent/dossiers/components/ListeDossierAInstruire.tsx";
import {AgentContext} from "@/routes/contexts/AgentContext.ts";

export const Route = createFileRoute("/agent/fip3/dossiers/a-instruire")({
    beforeLoad: ({context}: { context: AgentContext }) => {
        // TODO redirection sinon
        console.log(context.agent.estRedacteur());
    },
    component: ListeDossierAInstruire,
})