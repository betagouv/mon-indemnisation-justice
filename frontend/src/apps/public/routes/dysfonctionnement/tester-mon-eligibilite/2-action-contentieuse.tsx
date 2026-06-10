import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepActionContentieuse } from "@/apps/public/components/steps/StepActionContentieuse";


function ActionContentieuseRoute() {
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
        currentStep={3}
        stepCount={TOTAL_STEPS}
        title="État de la procédure"
        nextTitle="Décisions de justice"
      />
      <StepActionContentieuse
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/1-date-decision" })}
        onSuivant={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/3-type-decision" })}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/2-action-contentieuse")({
  component: ActionContentieuseRoute,
});
