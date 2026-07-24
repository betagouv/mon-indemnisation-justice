import React from "react";
import { usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { createFileRoute } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { StepDiligences } from "@/apps/public/components/steps/StepDiligences";
import { container } from "@/apps/public/container";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";
import { useInjection } from "inversify-react";


function DiligencesRoute() {
  const navigate = usePublicNavigate();
  const { test } = Route.useLoaderData();
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);

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
        test={test}
        onPrecedent={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/4-pieces-procedure" })}
        onSuivant={async () => {
          await manager.soumettre();
          navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/resultat" });
        }}
        onAnnuler={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" })}
        isLastStep
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/5-diligences")({
  component: DiligencesRoute,
  loader: () => ({
    test: container.get<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$).get(),
  }),
});
