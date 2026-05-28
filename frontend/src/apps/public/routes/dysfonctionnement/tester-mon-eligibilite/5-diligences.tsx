import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepDiligences } from "@/apps/public/components/steps/StepDiligences";


function DiligencesRoute() {
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
        currentStep={6}
        stepCount={TOTAL_STEPS}
        title="Diligences"
      />
      <StepDiligences
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/4-pieces-procedure" })}
        onSuivant={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/resultat" })}
        isLastStep
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/5-diligences")({
  component: DiligencesRoute,
});
