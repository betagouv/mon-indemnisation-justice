import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepTypeDecision } from "@/apps/public/components/steps/StepTypeDecision";

const TOTAL_STEPS = 6;

function TypeDecisionRoute() {
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
        currentStep={4}
        stepCount={TOTAL_STEPS}
        title="Décisions de justice"
        nextTitle="Pièces de procédure"
      />
      <StepTypeDecision
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/2-action-contentieuse" })}
        onSuivant={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/4-pieces-procedure" })}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/3-type-decision")({
  component: TypeDecisionRoute,
});
