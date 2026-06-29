import React from "react";
import { useForm, useStore } from "@tanstack/react-form";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { PieceProcedure } from "../types";
import { SchemaEtapePiecesProc } from "../formulaires/eligibilite.schemas";
import { saveCritere, critereDocumentsProc } from "@/apps/public/services/eligibiliteStore";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";
import { BlockedNavButtons } from "./BlockedNavButtons";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

const PIECES_LABELS: Record<PieceProcedure, string> = {
  [PieceProcedure.ActeIntroductif]:
    "Acte introductif de la procédure (requête, assignation, citation, déclaration d'appel, etc.)",
  [PieceProcedure.Ecritures]: "Conclusions, mémoires ou autres écritures",
  [PieceProcedure.Convocations]: "Convocations, avis d'audience ou notifications",
  [PieceProcedure.Echanges]: "Courriers ou échanges avec la juridiction ou le greffe",
  [PieceProcedure.DocumentsProcedure]:
    "Documents relatifs au déroulement de la procédure (renvois, calendrier de procédure, mise en état, etc.)",
  [PieceProcedure.Aucune]: "Je ne dispose d'aucun de ces documents",
};

const PIECES_DISPONIBLES = [
  PieceProcedure.ActeIntroductif,
  PieceProcedure.Ecritures,
  PieceProcedure.Convocations,
  PieceProcedure.Echanges,
  PieceProcedure.DocumentsProcedure,
];

export function StepPiecesProc({ onPrecedent, onSuivant, onAnnuler, onRetour, isLastStep, test }: StepProps) {
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);
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

  const piecesProc = useStore(formulaire.store, (state) => state.values.piecesProc);
  const aucuneSelectionnee = piecesProc.includes(PieceProcedure.Aucune);

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
            if (piece === PieceProcedure.Aucune) {
              field.handleChange(
                field.state.value.includes(PieceProcedure.Aucune) ? [] : [PieceProcedure.Aucune],
              );
            } else {
              const sansCettePiece = field.state.value.filter(
                (p) => p !== piece && p !== PieceProcedure.Aucune,
              );
              field.handleChange(
                field.state.value.includes(piece)
                  ? sansCettePiece
                  : [...sansCettePiece, piece],
              );
            }
          };

          return (
            <Checkbox
              legend="De quelles pièces de la procédure disposez-vous ?"
              hintText="Ces documents permettent d'apprécier le déroulement de la procédure et d'examiner votre demande."
              options={[...PIECES_DISPONIBLES, PieceProcedure.Aucune].map((piece) => ({
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
      {aucuneSelectionnee && (
        <Alert
          className="fr-mt-2w"
          severity="error"
          title="Démarche non éligible"
          description="L'examen de la demande nécessite la production de documents permettant de retracer le déroulement de la procédure concernée."
        />
      )}
      <formulaire.Subscribe
        selector={(state) => ({ piecesProc: state.values.piecesProc, showError: state.submissionAttempts > 0 })}
        children={({ piecesProc, showError }) =>
          showError && piecesProc.length === 0 ? (
            <Alert
              className="fr-mt-2w"
              severity="error"
              title="Veuillez sélectionner au moins une pièce de procédure"
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
