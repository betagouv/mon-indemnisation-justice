import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ActionContentieuse } from "../types";
import type { StepProps } from "../types";

export function StepActionContentieuse({ reponses, set }: StepProps) {
  return (
    <>
      <RadioButtons
        legend="Avez-vous engagé une action contentieuse pour des délais déraisonnables dirigée contre l'agent judiciaire de l'État ?"
        hintText="La délivrance d'une assignation à l'Agent Judiciaire de l'État (AJE) met fin à la phase précontentieuse."
        options={[
          {
            label: "Non, aucune action contentieuse",
            nativeInputProps: {
              value: ActionContentieuse.Non,
              checked: reponses.actionContentieuse === ActionContentieuse.Non,
              onChange: () => set("actionContentieuse", ActionContentieuse.Non),
            },
          },
          {
            label: "Oui, la procédure est en cours devant l'AJE",
            nativeInputProps: {
              value: ActionContentieuse.Oui,
              checked: reponses.actionContentieuse === ActionContentieuse.Oui,
              onChange: () => set("actionContentieuse", ActionContentieuse.Oui),
            },
          },
        ]}
      />
      {reponses.actionContentieuse === ActionContentieuse.Oui && (
        <Alert
          className="fr-mt-2w"
          severity="error"
          title="Critère non rempli"
          description="Une procédure contentieuse en cours devant l'AJE rend la démarche précontentieuse irrecevable. Vous pourrez effectuer cette déclaration après la clôture de cette procédure."
        />
      )}
    </>
  );
}
