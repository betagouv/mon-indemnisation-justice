import React from "react";
import { useForm, useStore } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { calculerPrescription } from "@/apps/public/services/prescription";
import { saveCritere, criterePrescription } from "@/apps/public/services/eligibiliteStore";
import { SchemaEtapeDateDecision } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

const ReferenceJuridique = (
  <div
    className="fr-text--sm fr-mb-2w fr-p-2w"
    style={{
      border: "1px solid var(--border-default-grey)",
      backgroundColor: "var(--background-alt-grey)",
      color: "var(--text-default-grey)",
    }}
  >
    Art. 1er, loi n°68-1250 du 31/12/1968 — Civ. 1re, 15/06/2017, n°16-18.769
  </div>
);

function ExemplePrescription() {
  const annee = new Date().getFullYear() - 3;
  return (
    <div className="fr-callout fr-mb-3w">
      <p className="fr-text--sm fr-mb-0">
        Décision rendue le 15 mars {annee} → délai du 1er janvier {annee + 1} au 31 décembre{" "}
        {annee + 4}. Au 1er janvier {annee + 5}, l'action est prescrite.
      </p>
    </div>
  );
}

export function StepDateDecision({ onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeDateDecision },
    defaultValues: { dateDecision: "" },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        const critere = criterePrescription(new Date(value.dateDecision));
        if (!critere.rempli) return;
        saveCritere("prescription", critere);
        onSuivant();
      }
    },
  });

  const dateDecision = useStore(formulaire.store, (state) => state.values.dateDecision);
  const prescription = calculerPrescription(dateDecision ? new Date(dateDecision) : undefined);

  return (
    <>
      <p className="fr-text--sm fr-mb-2w">
        La prescription est quadriennale : vous disposez de 4 ans à compter du 1er janvier de
        l'année suivant celle de la décision pour agir.
      </p>
      {ReferenceJuridique}
      <ExemplePrescription />
      <form
        onSubmit={async (e) => {
          e.preventDefault();
          e.stopPropagation();
          await formulaire.handleSubmit();
        }}
      >
        <div className="fr-grid-row">
          <div className="fr-col-12 fr-col-lg-3">
            <formulaire.Field
              name="dateDecision"
              children={(field) => (
                <FormInput
                  label="Date de la décision"
                  hintText="Indiquez la date de la dernière décision de justice rendue dans votre affaire."
                  champ={field}
                  nativeInputProps={{
                    type: "date",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
                />
              )}
            />
          </div>
        </div>
        {dateDecision && (
          <Alert
            className="fr-mt-2w"
            severity={prescription.rempli ? "success" : "error"}
            title={prescription.rempli ? "Vous êtes dans les délais" : "Délai de prescription dépassé"}
            description={prescription.detail}
          />
        )}
        <NavButtons
          onPrecedent={onPrecedent}
          isLastStep={isLastStep}
          peutContinuer={!dateDecision || prescription.rempli}
        />
      </form>
    </>
  );
}
