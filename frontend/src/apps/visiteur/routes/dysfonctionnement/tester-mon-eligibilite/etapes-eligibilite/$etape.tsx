import React from "react";
import { createFileRoute, redirect, useNavigate, useLocation } from "@tanstack/react-router";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/visiteur/components/Layout";
import { STEPS, STEP_COUNT, TOTAL_STEPS } from "@/apps/visiteur/components/steps";
import type { ReponsesEligibilite } from "@/apps/visiteur/components/types";

const BASE = "/dysfonctionnement/tester-mon-eligibilite";

function EtapeEligibiliteRoute() {
  const { etape } = Route.useParams();
  const etapeNum = parseInt(etape, 10);
  const { state } = useLocation();
  const navigate = useNavigate();

  const reponses: ReponsesEligibilite = state.reponses ?? { piecesProc: [] };
  const step = STEPS[etapeNum - 1];
  const StepComponent = step.Component;

  const aller = (to: string, nouvellesReponses?: ReponsesEligibilite) =>
    navigate({ to: to as any, ...(nouvellesReponses ? { state: { reponses: nouvellesReponses } } : {}) } as any);

  const suivant = (partial: Partial<ReponsesEligibilite>) => {
    const nouvellesReponses = { ...reponses, ...partial };
    if (etapeNum < STEP_COUNT) {
      aller(`${BASE}/etapes-eligibilite/${etapeNum + 1}`, nouvellesReponses);
    } else {
      aller(`${BASE}/resultat`, nouvellesReponses);
    }
  };

  const precedent = () => {
    if (etapeNum === 1) {
      aller(`${BASE}/test-eligibilite`);
    } else {
      aller(`${BASE}/etapes-eligibilite/${etapeNum - 1}`, reponses);
    }
  };

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
                aller(`${BASE}/test-eligibilite`);
              },
            },
          },
        ]}
      />
      <h1>Test d'éligibilité</h1>
      <Stepper
        currentStep={etapeNum + 1}
        stepCount={TOTAL_STEPS}
        title={step.title}
        nextTitle={etapeNum < STEP_COUNT ? STEPS[etapeNum].title : undefined}
      />
      <StepComponent
        reponses={reponses}
        onPrecedent={precedent}
        onSuivant={suivant}
        isLastStep={etapeNum === STEP_COUNT}
      />
    </Layout>
  );
}

export const Route = createFileRoute(
  "/dysfonctionnement/tester-mon-eligibilite/etapes-eligibilite/$etape",
)({
  component: EtapeEligibiliteRoute,
  beforeLoad: ({ params }) => {
    const etapeNum = parseInt(params.etape, 10);
    if (isNaN(etapeNum) || etapeNum < 1 || etapeNum > STEP_COUNT) {
      throw redirect({
        to: "/dysfonctionnement/tester-mon-eligibilite/etapes-eligibilite/1",
      } as any);
    }
  },
});
