import React, { useState } from "react";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "./Layout";
import { PieceProcedure, type ReponsesEligibilite } from "./types";
import { STEPS, NavButtons } from "./steps";

export type { ReponsesEligibilite } from "./types";

const STEP_COUNT = STEPS.length;
export const TOTAL_STEPS = STEP_COUNT + 1;

type Props = {
  onPrecedent: () => void;
  onTerminer: (reponses: ReponsesEligibilite) => void;
};

export const EtapesEligibilite = ({ onPrecedent, onTerminer }: Props) => {
  const [etape, setEtape] = useState(1);
  const [reponses, setReponses] = useState<ReponsesEligibilite>({ piecesProc: [] });

  const set = <K extends keyof ReponsesEligibilite>(key: K, val: ReponsesEligibilite[K]) =>
    setReponses((prev) => ({ ...prev, [key]: val }));

  const togglePiece = (val: PieceProcedure) =>
    setReponses((prev) => ({
      ...prev,
      piecesProc: prev.piecesProc.includes(val)
        ? prev.piecesProc.filter((v) => v !== val)
        : [...prev.piecesProc, val],
    }));

  const precedent = () => {
    if (etape === 1) onPrecedent();
    else setEtape((e) => e - 1);
  };

  const suivant = () => {
    if (etape < STEP_COUNT) setEtape((e) => e + 1);
    else onTerminer(reponses);
  };

  const step = STEPS[etape - 1];
  const StepComponent = step.Component;

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
                onPrecedent();
              },
            },
          },
        ]}
      />
      <h1>Test d'éligibilité</h1>
      <Stepper
        currentStep={etape + 1}
        stepCount={TOTAL_STEPS}
        title={step.title}
        nextTitle={etape < STEP_COUNT ? STEPS[etape].title : undefined}
      />
      <StepComponent reponses={reponses} set={set} togglePiece={togglePiece} />
      <NavButtons
        onPrecedent={precedent}
        onSuivant={suivant}
        canContinue={step.canContinue(reponses)}
        isLastStep={etape === STEP_COUNT}
      />
    </Layout>
  );
};
