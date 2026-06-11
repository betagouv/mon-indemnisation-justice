import React, { useState } from "react";
import { useForm } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { PieceProcedure } from "../types";
import { SchemaEtapePiecesProc } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereDocumentsProc } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

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
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);
  const test = manager.get();
  const [soumis, setSoumis] = useState(false);

  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapePiecesProc },
    defaultValues: { piecesProc: test?.piecesProc ?? ([] as PieceProcedure[]) },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        manager.modifier({ piecesProc: value.piecesProc });
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
        setSoumis(true);
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
      {soumis && formulaire.state.values.piecesProc.length === 0 && (
        <Alert
          className="fr-mt-2w"
          severity="error"
          title="Veuillez sélectionner au moins une pièce de procédure"
        />
      )}
      <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
    </form>
  );
}
