import React from "react";
import { useForm } from "@tanstack/react-form";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { PieceProcedure } from "../types";
import { SchemaEtapePiecesProc } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereDocumentsProc } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

const PIECES_LABELS: Record<PieceProcedure, string> = {
  [PieceProcedure.Assignation]: "Assignation ou requête introductive",
  [PieceProcedure.DecisionsJuge]: "Décisions du juge de la mise en état",
  [PieceProcedure.Calendrier]: "Calendrier de procédure",
  [PieceProcedure.Ecritures]: "Écritures des parties",
  [PieceProcedure.Convocations]: "Convocation aux audiences",
  [PieceProcedure.Renvoi]: "Décision de renvoi",
  [PieceProcedure.Echanges]: "Échanges avec la juridiction",
  [PieceProcedure.Appel]: "Déclaration d'appel",
};

export function StepPiecesProc({ onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapePiecesProc },
    defaultValues: { piecesProc: [] as PieceProcedure[] },
    onSubmit: async ({ formApi }) => {
      if (formApi.state.isValid) {
        saveCritere("documentsProc", critereDocumentsProc());
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
        name="piecesProc"
        children={(field) => {
          const toggle = (piece: PieceProcedure) => {
            const val = field.state.value;
            field.handleChange(
              val.includes(piece) ? val.filter((p) => p !== piece) : [...val, piece],
            );
          };

          return (
            <Checkbox
              legend="De quelles pièces de la procédure disposez-vous ?"
              hintText="Pour qualifier le délai déraisonnable, l'ensemble des décisions rendues dans votre procédure est nécessaire."
              state={!field.state.meta.isValid ? "error" : "default"}
              stateRelatedMessage={!field.state.meta.isValid ? (field.state.meta.errors.at(0)?.message ?? "") : ""}
              options={Object.values(PieceProcedure).map((piece) => ({
                label: PIECES_LABELS[piece],
                nativeInputProps: {
                  checked: field.state.value.includes(piece),
                  onChange: () => toggle(piece),
                },
              }))}
            />
          );
        }}
      />
      <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
    </form>
  );
}
