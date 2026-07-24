import React from "react";
import { useForm, useStore } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { TypeDecision } from "../types";
import { SchemaEtapeTypeDecision } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereDecisionsJustice } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";
import { BlockedNavButtons } from "./BlockedNavButtons";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

const TYPE_DECISION_LABELS: Record<TypeDecision, string> = {
  [TypeDecision.JugementPremiereInstance]: "Décision de première instance",
  [TypeDecision.ArretCourAppel]: "Décision de la cour d'appel",
  [TypeDecision.ArretCourCassation]: "Décision de la Cour de cassation",
  [TypeDecision.Aucune]: "Je ne dispose pas de la décision",
};

const DECISIONS_DISPONIBLES = [
  TypeDecision.JugementPremiereInstance,
  TypeDecision.ArretCourAppel,
  TypeDecision.ArretCourCassation,
];

export function StepTypeDecision({ onPrecedent, onSuivant, onAnnuler, onRetour, isLastStep, test }: StepProps) {
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeTypeDecision },
    defaultValues: { typeDecision: test?.typeDecision ?? ([] as TypeDecision[]) },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        manager.modifier({ typeDecision: value.typeDecision });
        saveCritere("decisionsJustice", critereDecisionsJustice(value.typeDecision));
        onSuivant();
      }
    },
  });

  const typeDecision = useStore(formulaire.store, (state) => state.values.typeDecision);
  const aucuneSelectionnee = typeDecision.includes(TypeDecision.Aucune);

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
        children={(field) => {
          const toggle = (decision: TypeDecision) => {
            if (decision === TypeDecision.Aucune) {
              field.handleChange(
                field.state.value.includes(TypeDecision.Aucune) ? [] : [TypeDecision.Aucune],
              );
            } else {
              const sansCetteDecision = field.state.value.filter(
                (d) => d !== decision && d !== TypeDecision.Aucune,
              );
              field.handleChange(
                field.state.value.includes(decision)
                  ? sansCetteDecision
                  : [...sansCetteDecision, decision],
              );
            }
          };

          return (
            <Checkbox
              legend="De quelles décisions disposez-vous ?"
              hintText="Pour qualifier le délai déraisonnable, l'ensemble des décisions rendues dans votre procédure est nécessaire."
              options={[...DECISIONS_DISPONIBLES, TypeDecision.Aucune].map((decision) => ({
                label: TYPE_DECISION_LABELS[decision],
                nativeInputProps: {
                  checked: field.state.value.includes(decision),
                  onChange: () => toggle(decision),
                },
              }))}
            />
          );
        }}
      />
      {aucuneSelectionnee && (
        <Alert
          className="fr-mt-2w"
          severity="error"
          title="La décision de justice est requise"
          description="La décision de justice concernée est nécessaire à l'examen de votre demande. Nous vous invitons à vous en munir avant de poursuivre votre démarche."
        />
      )}
      <formulaire.Subscribe
        selector={(state) => ({ typeDecision: state.values.typeDecision, showError: state.submissionAttempts > 0 })}
        children={({ typeDecision, showError }) =>
          showError && typeDecision.length === 0 ? (
            <Alert
              className="fr-mt-2w"
              severity="error"
              title="Veuillez sélectionner au moins une décision"
            />
          ) : null
        }
      />
      {aucuneSelectionnee && onRetour
        ? <BlockedNavButtons onRetour={onRetour} />
        : <NavButtons onPrecedent={onPrecedent} onAnnuler={onAnnuler} isLastStep={isLastStep} />
      }
    </form>
  );
}
