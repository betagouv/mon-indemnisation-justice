import React from "react";
import { useForm } from "@tanstack/react-form";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { TypeDecision } from "../types";
import { SchemaEtapeTypeDecision } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereDecisionsJustice } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

const TYPE_DECISION_LABELS: Record<TypeDecision, string> = {
  [TypeDecision.JugementPremiereInstance]: "Jugement de première instance",
  [TypeDecision.ArretCourAppel]: "Arrêt de la Cour d'appel",
  [TypeDecision.ArretCourCassation]: "Arrêt de la Cour de cassation",
  [TypeDecision.Aucune]: "Aucune décision",
};

export function StepTypeDecision({ onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeTypeDecision },
    defaultValues: { typeDecision: undefined } as { typeDecision?: TypeDecision },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        saveCritere("decisionsJustice", critereDecisionsJustice(value.typeDecision!));
        onSuivant();
      }
    },
  });

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        await formulaire.handleSubmit();
      }}
    >
      <formulaire.Field
        name="typeDecision"
        children={(field) => (
          <FormRadioButtons
            legend="De quelles décisions disposez-vous ?"
            hintText="Pour qualifier le délai déraisonnable, l'ensemble des décisions rendues dans votre procédure est nécessaire."
            champ={field}
            options={Object.values(TypeDecision).map((type) => ({
              label: TYPE_DECISION_LABELS[type],
              nativeInputProps: {
                value: type,
                checked: field.state.value === type,
                onChange: () => field.handleChange(type),
              },
            }))}
          />
        )}
      />
      <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
    </form>
  );
}
