import React from "react";
import { useForm } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { SchemaEtapeDiligences } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereDiligences } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

export function StepDiligences({ onPrecedent, onSuivant, isLastStep, test }: StepProps) {
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);

  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeDiligences },
    defaultValues: { preuvesDiligences: test?.preuvesDiligences } as { preuvesDiligences?: boolean },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        manager.modifier({ preuvesDiligences: value.preuvesDiligences });
        saveCritere("diligences", critereDiligences(value.preuvesDiligences!));
        onSuivant();
      }
    },
  });

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
      <form
        onSubmit={async (e) => {
          e.preventDefault();
          e.stopPropagation();
          await formulaire.handleSubmit();
        }}
      >
        <formulaire.Field
          name="preuvesDiligences"
          children={(field) => (
            <FormRadioButtons
              legend="Disposez-vous de preuves de diligences accomplies ?"
              options={[
                {
                  label: "Oui, j'ai des preuves de mes démarches",
                  nativeInputProps: {
                    value: "oui",
                    checked: field.state.value === true,
                    onChange: () => field.handleChange(true),
                  },
                },
                {
                  label: "Non, je n'ai pas de traces écrites",
                  nativeInputProps: {
                    value: "non",
                    checked: field.state.value === false,
                    onChange: () => field.handleChange(false),
                  },
                },
              ]}
            />
          )}
        />
        <formulaire.Subscribe
          selector={(state) => ({ preuvesDiligences: state.values.preuvesDiligences, showError: state.isDirty || state.submissionAttempts > 0 })}
          children={({ preuvesDiligences, showError }) =>
            showError && preuvesDiligences === undefined ? (
              <Alert
                className="fr-mt-2w"
                severity="error"
                title="Veuillez répondre à cette question"
              />
            ) : null
          }
        />
        <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
      </form>
    </>
  );
}
