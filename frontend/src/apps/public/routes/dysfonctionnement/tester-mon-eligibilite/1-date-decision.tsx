import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepDateDecision } from "@/apps/public/components/steps/StepDateDecision";
import { container } from "@/apps/public/container";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";


function DateDecisionRoute() {
  const navigate = usePublicNavigate();
  const { test } = Route.useLoaderData();

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
        currentStep={2}
        stepCount={TOTAL_STEPS}
        title="Date de la décision de justice "
        nextTitle="État de la procédure"
      />
      <StepDateDecision
        test={test}
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite" })}
        onSuivant={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/2-action-contentieuse" })}
        onAnnuler={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" })}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/1-date-decision")({
  component: DateDecisionRoute,
  loader: () => ({
    test: container.get<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$).get(),
  }),
});
