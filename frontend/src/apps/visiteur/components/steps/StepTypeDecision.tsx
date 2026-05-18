import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { TypeDecision } from "../types";
import type { StepProps } from "../types";

export function StepTypeDecision({ reponses, set }: StepProps) {
  return (
    <>
      <RadioButtons
        legend="De quelles décisions disposez-vous ?"
        hintText="Pour qualifier le délai déraisonnable, l'ensemble des décisions rendues dans votre procédure est nécessaire."
        options={[
          {
            label: "Jugement de première instance",
            nativeInputProps: {
              value: TypeDecision.JugementPremiereInstance,
              checked: reponses.typeDecision === TypeDecision.JugementPremiereInstance,
              onChange: () => set("typeDecision", TypeDecision.JugementPremiereInstance),
            },
          },
          {
            label: "Arrêt de la Cour d'appel",
            nativeInputProps: {
              value: TypeDecision.ArretCourAppel,
              checked: reponses.typeDecision === TypeDecision.ArretCourAppel,
              onChange: () => set("typeDecision", TypeDecision.ArretCourAppel),
            },
          },
          {
            label: "Arrêt de la Cour de cassation",
            nativeInputProps: {
              value: TypeDecision.ArretCourCassation,
              checked: reponses.typeDecision === TypeDecision.ArretCourCassation,
              onChange: () => set("typeDecision", TypeDecision.ArretCourCassation),
            },
          },
          {
            label: "Aucune décision",
            nativeInputProps: {
              value: TypeDecision.Aucune,
              checked: reponses.typeDecision === TypeDecision.Aucune,
              onChange: () => set("typeDecision", TypeDecision.Aucune),
            },
          },
        ]}
      />
      {reponses.typeDecision === TypeDecision.Aucune && (
        <Alert
          className="fr-mt-2w"
          severity="error"
          title="Critère non rempli"
          description="L'absence de décision de justice ne permet pas de qualifier un délai déraisonnable. Une décision rendue dans votre affaire est nécessaire pour engager cette démarche."
        />
      )}
    </>
  );
}
