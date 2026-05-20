import React from "react";
import { useForm } from "@tanstack/react-form";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { TypeDecision } from "../types";
import { SchemaEtapeTypeDecision } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

export function StepTypeDecision({ reponses, onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeTypeDecision },
    defaultValues: { typeDecision: reponses.typeDecision } as { typeDecision?: TypeDecision },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        onSuivant({ typeDecision: value.typeDecision! });
      }
    },
  });

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        formulaire.validate("submit");
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
            options={[
              {
                label: "Jugement de première instance",
                nativeInputProps: {
                  value: TypeDecision.JugementPremiereInstance,
                  checked: field.state.value === TypeDecision.JugementPremiereInstance,
                  onChange: () => field.handleChange(TypeDecision.JugementPremiereInstance),
                },
              },
              {
                label: "Arrêt de la Cour d'appel",
                nativeInputProps: {
                  value: TypeDecision.ArretCourAppel,
                  checked: field.state.value === TypeDecision.ArretCourAppel,
                  onChange: () => field.handleChange(TypeDecision.ArretCourAppel),
                },
              },
              {
                label: "Arrêt de la Cour de cassation",
                nativeInputProps: {
                  value: TypeDecision.ArretCourCassation,
                  checked: field.state.value === TypeDecision.ArretCourCassation,
                  onChange: () => field.handleChange(TypeDecision.ArretCourCassation),
                },
              },
              {
                label: "Aucune décision",
                nativeInputProps: {
                  value: TypeDecision.Aucune,
                  checked: field.state.value === TypeDecision.Aucune,
                  onChange: () => field.handleChange(TypeDecision.Aucune),
                },
              },
            ]}
          />
        )}
      />
      <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
    </form>
  );
}
