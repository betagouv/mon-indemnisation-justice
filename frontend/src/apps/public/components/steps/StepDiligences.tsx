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
        L'appréciation du caractère raisonnable de la durée d’une procédure tient compte du comportement des parties.
      </p>
      <div className="fr-callout fr-mb-3w">
        <p className="fr-text--sm fr-mb-0">
          Exemples : relances auprès du greffe, demandes d’information sur l’avancement de la procédure, demandes de fixation d’audience, courriers adressés à la juridiction 
          ou tout autre échange relatif au traitement de l’affaire.
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
              legend="Disposez-vous de justificatifs des démarches que vous avez effectuées auprès de la juridiction ?"
              hintText="Ces éléments peuvent être utiles pour apprécier le déroulement de la procédure."
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
