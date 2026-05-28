import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepDateDecision } from "@/apps/public/components/steps/StepDateDecision";

const TOTAL_STEPS = 6;

function DateDecisionRoute() {
  const navigate = usePublicNavigate();

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Test d'éligibilité"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un déni de justice",
            linkProps: {
              href: "#",
              onClick: (e) => {
                e.preventDefault();
                navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" });
              },
            },
          },
        ]}
      />
      <h1>Test d'éligibilité</h1>
      <Stepper
        currentStep={2}
        stepCount={TOTAL_STEPS}
        title="Date de la décision"
        nextTitle="État de la procédure"
      />
      <StepDateDecision
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite" })}
        onSuivant={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/2-action-contentieuse" })}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/1-date-decision")({
  component: DateDecisionRoute,
});
