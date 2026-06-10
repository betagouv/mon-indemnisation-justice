import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepPiecesProc } from "@/apps/public/components/steps/StepPiecesProc";


function PiecesProcedureRoute() {
  const navigate = usePublicNavigate();

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Test d'éligibilité"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un déni de justice",
            linkProps: { to: "/dysfonctionnement/tester-mon-eligibilite/" },
          },
        ]}
      />
      <h1>Test d'éligibilité</h1>
      <Stepper
        currentStep={5}
        stepCount={TOTAL_STEPS}
        title="Pièces de procédure"
        nextTitle="Diligences"
      />
      <StepPiecesProc
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/3-type-decision" })}
        onSuivant={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/5-diligences" })}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/4-pieces-procedure")({
  component: PiecesProcedureRoute,
});
