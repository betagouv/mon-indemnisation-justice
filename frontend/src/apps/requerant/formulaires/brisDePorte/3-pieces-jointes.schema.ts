import { Dossier, PieceJointe } from "@/apps/requerant/models";
import { TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe.ts";
import { z } from "zod";

export const listerTypesPiecesJointesRequis = (dossier: Dossier) => {
  return Object.values(TypePieceJointe.liste).filter((type) =>
    type.estRequis(
      dossier.rapportAuLogement,
      dossier.personneMorale?.typePersonneMorale,
      dossier.estLieDeclaration(),
    ),
  );
};

export const getSchemaValidationPiecesJointes = (dossier: Dossier) =>
  z.object({
    piecesJointes: z
      .array(z.instanceof(PieceJointe))
      .superRefine((piecesJointes, contexte) => {
        listerTypesPiecesJointesRequis(dossier).forEach(
          (typePieceJointeRequis: TypePieceJointe) => {
            if (
              piecesJointes.filter((pieceJointe: PieceJointe) =>
                pieceJointe.type.equals(typePieceJointeRequis),
              ).length == 0
            ) {
              contexte.addIssue({
                code: "custom",
                message: typePieceJointeRequis.type,
              });
            }
          },
        );
      }),
  });

export const estDossierOkPiecesJointes = (dossier: Dossier): boolean =>
  getSchemaValidationPiecesJointes(dossier).safeParse(dossier.piecesJointes)
    .success;
