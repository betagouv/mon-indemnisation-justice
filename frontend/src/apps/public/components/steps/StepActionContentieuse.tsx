import React from "react";
import { useForm, useStore } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { ActionContentieuse } from "../types";
import { SchemaEtapeActionContentieuse } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereActionContentieuse } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

export function StepActionContentieuse({ onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeActionContentieuse },
    defaultValues: { actionContentieuse: undefined } as { actionContentieuse?: ActionContentieuse },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        saveCritere("actionContentieuse", critereActionContentieuse(value.actionContentieuse!));
        onSuivant();
      }
    },
  });

  const actionContentieuse = useStore(formulaire.store, (s) => s.values.actionContentieuse);
  const estBloque = actionContentieuse === ActionContentieuse.Oui;

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        await formulaire.handleSubmit();
      }}
    >
      <formulaire.Field
        name="actionContentieuse"
        children={(field) => (
          <FormRadioButtons
            legend="Avez-vous engagé une action contentieuse pour des délais déraisonnables dirigée contre l'agent judiciaire de l'État ?"
            hintText="La délivrance d'une assignation à l'Agent Judiciaire de l'État (AJE) met fin à la phase précontentieuse."
            champ={field}
            options={[
              {
                label: "Non, aucune action contentieuse",
                nativeInputProps: {
                  value: ActionContentieuse.Non,
                  checked: field.state.value === ActionContentieuse.Non,
                  onChange: () => field.handleChange(ActionContentieuse.Non),
                },
              },
              {
                label: "Oui, la procédure est en cours devant l'AJE",
                nativeInputProps: {
                  value: ActionContentieuse.Oui,
                  checked: field.state.value === ActionContentieuse.Oui,
                  onChange: () => field.handleChange(ActionContentieuse.Oui),
                },
              },
            ]}
          />
        )}
      />
      {estBloque && (
        <Alert
          className="fr-mt-2w"
          severity="error"
          title="Démarche irrecevable"
          description="Une procédure contentieuse en cours devant l'AJE rend la démarche précontentieuse irrecevable. Vous pourrez effectuer cette déclaration après la clôture de cette procédure."
        />
      )}
      <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} peutContinuer={!estBloque} />
    </form>
  );
}
