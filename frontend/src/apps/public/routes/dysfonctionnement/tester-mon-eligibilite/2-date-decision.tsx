import { Layout } from "@/apps/public/components/Layout";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { StepDateDecision } from "@/apps/public/components/steps/StepDateDecision";
import { container } from "@/apps/public/container";
import { RouteurPublic } from "@/apps/public/routeur";
import { clearCriteres } from "@/apps/public/services/eligibiliteStore";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React from "react";

const DateDecisionRoute = () => {
  const navigate = useNavigate<typeof RouteurPublic>({
    from: Route.fullPath,
  });
  const manager = useInjection<TestEligibiliteManagerInterface>(
    TestEligibiliteManagerInterface.$,
  );
  const { test } = Route.useLoaderData();

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Test d'éligibilité"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un délai déraisonnable de procédure",
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
        onPrecedent={() =>
          navigate({
            to: "/dysfonctionnement/tester-mon-eligibilite/1-etat-procedure",
          })
        }
        onSuivant={() =>
          navigate({
            to: "/dysfonctionnement/tester-mon-eligibilite/3-action-contentieuse",
          })
        }
        onAnnuler={() =>
          navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" })
        }
        onRetour={() => {
          manager.effacer();
          clearCriteres();
          navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" });
        }}
      />
    </Layout>
  );
};

export const Route = createFileRoute(
  "/dysfonctionnement/tester-mon-eligibilite/2-date-decision",
)({
  component: DateDecisionRoute,
  loader: () => ({
    test: container
      .get<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$)
      .get(),
  }),
});
