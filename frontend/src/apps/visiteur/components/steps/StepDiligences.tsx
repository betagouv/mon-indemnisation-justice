import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { PreuvesDiligences } from "../types";
import type { StepProps } from "../types";

export function StepDiligences({ reponses, set }: StepProps) {
  return (
    <>
      <p>
        L'appréciation du délai tient compte du comportement des parties. Avoir alerté la
        juridiction sur les délais renforce votre dossier.
      </p>
      <div className="fr-callout fr-mb-3w">
        <p className="fr-text--sm fr-mb-0">
          Courrier au juge signalant que le dossier était complet, relances écrites auprès du
          greffe, demandes de fixation d'audience…
        </p>
      </div>
      <RadioButtons
        legend="Disposez-vous de preuves de diligences accomplies ?"
        options={[
          {
            label: "Oui, j'ai des preuves de mes démarches",
            nativeInputProps: {
              value: PreuvesDiligences.Oui,
              checked: reponses.preuvesDiligences === PreuvesDiligences.Oui,
              onChange: () => set("preuvesDiligences", PreuvesDiligences.Oui),
            },
          },
          {
            label: "Non, je n'ai pas de traces écrites",
            nativeInputProps: {
              value: PreuvesDiligences.Non,
              checked: reponses.preuvesDiligences === PreuvesDiligences.Non,
              onChange: () => set("preuvesDiligences", PreuvesDiligences.Non),
            },
          },
        ]}
      />
    </>
  );
}
