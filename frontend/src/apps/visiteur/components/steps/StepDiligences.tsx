import React from "react";
import { useForm } from "@tanstack/react-form";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { PreuvesDiligences } from "../types";
import { SchemaEtapeDiligences } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

export function StepDiligences({ reponses, onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeDiligences },
    defaultValues: { preuvesDiligences: reponses.preuvesDiligences } as { preuvesDiligences?: PreuvesDiligences },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        onSuivant({ preuvesDiligences: value.preuvesDiligences! });
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
          formulaire.validate("submit");
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
                    value: PreuvesDiligences.Oui,
                    checked: field.state.value === PreuvesDiligences.Oui,
                    onChange: () => field.handleChange(PreuvesDiligences.Oui),
                  },
                },
                {
                  label: "Non, je n'ai pas de traces écrites",
                  nativeInputProps: {
                    value: PreuvesDiligences.Non,
                    checked: field.state.value === PreuvesDiligences.Non,
                    onChange: () => field.handleChange(PreuvesDiligences.Non),
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
