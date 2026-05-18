import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { PieceProcedure } from "../types";
import type { StepProps } from "../types";

export function StepPiecesProc({ reponses, togglePiece }: StepProps) {
  return (
    <Checkbox
      legend="De quelles pièces de la procédure disposez-vous ?"
      hintText="Pour qualifier le délai déraisonnable, l'ensemble des décisions rendues dans votre procédure est nécessaire."
      options={[
        {
          label: "Assignation ou requête introductive",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Assignation),
            onChange: () => togglePiece(PieceProcedure.Assignation),
          },
        },
        {
          label: "Décisions du juge de la mise en état",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.DecisionsJuge),
            onChange: () => togglePiece(PieceProcedure.DecisionsJuge),
          },
        },
        {
          label: "Calendrier de procédure",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Calendrier),
            onChange: () => togglePiece(PieceProcedure.Calendrier),
          },
        },
        {
          label: "Écritures des parties",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Ecritures),
            onChange: () => togglePiece(PieceProcedure.Ecritures),
          },
        },
        {
          label: "Convocation aux audiences",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Convocations),
            onChange: () => togglePiece(PieceProcedure.Convocations),
          },
        },
        {
          label: "Décision de renvoi",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Renvoi),
            onChange: () => togglePiece(PieceProcedure.Renvoi),
          },
        },
        {
          label: "Échanges avec la juridiction",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Echanges),
            onChange: () => togglePiece(PieceProcedure.Echanges),
          },
        },
        {
          label: "Déclaration d'appel",
          nativeInputProps: {
            checked: reponses.piecesProc.includes(PieceProcedure.Appel),
            onChange: () => togglePiece(PieceProcedure.Appel),
          },
        },
      ]}
    />
  );
}
