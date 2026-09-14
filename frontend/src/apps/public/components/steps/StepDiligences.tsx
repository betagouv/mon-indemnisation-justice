import { TypePreuveDiligence } from "@/apps/public/models/TestEligibilite.ts";
import {
  critereDiligences,
  saveCritere,
} from "@/apps/public/services/eligibiliteStore";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { FormRadioButtons } from "@common/composants/dsfr/champs/form/FormRadioButtons.tsx";
import { useForm } from "@tanstack/react-form";
import { useInjection } from "inversify-react";
import React from "react";
import { SchemaEtapeDiligences } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

export function StepDiligences({
  onPrecedent,
  onSuivant,
  onAnnuler,
  isLastStep,
  test,
}: StepProps) {
  const manager = useInjection<TestEligibiliteManagerInterface>(
    TestEligibiliteManagerInterface.$,
  );

  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeDiligences },
    defaultValues: { preuvesDiligences: test?.preuvesDiligences } as {
      preuvesDiligences?: TypePreuveDiligence;
    },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        manager.modifier({ preuvesDiligences: value.preuvesDiligences });
        saveCritere("diligences", critereDiligences(value.preuvesDiligences!));
        await onSuivant();
      }
    },
  });

  return (
    <>
      <p>
        L'appréciation du caractère raisonnable de la durée d’une procédure
        tient compte du comportement des parties.
      </p>
      <div className="fr-callout fr-mb-3w">
        <p className="fr-text--sm fr-mb-0">
          Exemples : relances auprès du greffe, demandes d’information sur
          l’avancement de la procédure, demandes de fixation d’audience,
          courriers adressés à la juridiction ou tout autre échange relatif au
          traitement de l’affaire.
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
                  label: "Je dispose de justificatifs de mes démarches",
                  nativeInputProps: {
                    value: "avec_justificatifs",
                    checked: field.state.value === "avec_justificatifs",
                    onChange: (e) =>
                      field.handleChange(e.target.value as TypePreuveDiligence),
                  },
                },
                {
                  label:
                    "J’ai effectué des démarches, mais je ne dispose pas de justificatifs",
                  nativeInputProps: {
                    value: "sans_justificatif",
                    checked: field.state.value === "sans_justificatif",
                    onChange: (e) =>
                      field.handleChange(e.target.value as TypePreuveDiligence),
                  },
                },
                {
                  label:
                    "Je n’ai effectué aucune démarche auprès de la juridiction",
                  nativeInputProps: {
                    value: "pas_de_demarche",
                    checked: field.state.value === "pas_de_demarche",
                    onChange: (e) =>
                      field.handleChange(e.target.value as TypePreuveDiligence),
                  },
                },
              ]}
            />
          )}
        />
        <formulaire.Subscribe
          selector={(state) => ({
            preuvesDiligences: state.values.preuvesDiligences,
            showError: state.isDirty || state.submissionAttempts > 0,
          })}
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
        <NavButtons
          onPrecedent={onPrecedent}
          onAnnuler={onAnnuler}
          isLastStep={isLastStep}
        />
      </form>
    </>
  );
}
