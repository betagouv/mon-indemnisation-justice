import React from "react";
import { useForm } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { ActionContentieuse } from "../types";
import { SchemaEtapeActionContentieuse } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereActionContentieuse } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

export function StepActionContentieuse({ onPrecedent, onSuivant, isLastStep, test }: StepProps) {
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);

  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeActionContentieuse },
    defaultValues: { actionContentieuse: test?.actionContentieuse } as { actionContentieuse?: ActionContentieuse },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        manager.modifier({ actionContentieuse: value.actionContentieuse });
        saveCritere("actionContentieuse", critereActionContentieuse(value.actionContentieuse!));
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
        name="actionContentieuse"
        children={(field) => (
          <FormRadioButtons
            legend="Avez-vous engagé une action contentieuse pour des délais déraisonnables dirigée contre l'agent judiciaire de l'État ?"
            hintText="La délivrance d'une assignation à l'Agent Judiciaire de l'État (AJE) met fin à la phase précontentieuse."
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
      <formulaire.Subscribe
        selector={(state) => ({ actionContentieuse: state.values.actionContentieuse, showError: state.isDirty || state.submissionAttempts > 0 })}
        children={({ actionContentieuse, showError }) => {
          if (actionContentieuse === ActionContentieuse.Oui) {
            return (
              <Alert
                className="fr-mt-2w"
                severity="error"
                title="Démarche irrecevable"
                description="Une procédure contentieuse en cours devant l'AJE rend la démarche précontentieuse irrecevable. Vous pourrez effectuer cette déclaration après la clôture de cette procédure."
              />
            );
          }
          if (showError && !actionContentieuse) {
            return (
              <Alert
                className="fr-mt-2w"
                severity="error"
                title="Veuillez répondre à cette question"
              />
            );
          }
          return null;
        }}
      />
      <formulaire.Subscribe
        selector={(state) => state.values.actionContentieuse}
        children={(actionContentieuse) => (
          <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} peutContinuer={actionContentieuse !== ActionContentieuse.Oui} />
        )}
      />
    </form>
  );
}
