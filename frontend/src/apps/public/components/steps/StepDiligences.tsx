import React from "react";
import { useForm } from "@tanstack/react-form";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { SchemaEtapeDiligences } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

export function StepDiligences({ onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeDiligences },
    defaultValues: { preuvesDiligences: undefined } as { preuvesDiligences?: boolean },
    onSubmit: async ({ formApi }) => {
      if (formApi.state.isValid) {
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
              champ={field}
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
        <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
      </form>
    </>
  );
}
