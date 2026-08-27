import { Dossier, PieceJointe } from "@/apps/requerant/models";
import { TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe.ts";
import { z } from "zod";

export const listerTypesPiecesJointesRequis = (
  dossier: Dossier,
): TypePieceJointe[] => {
  return Object.values(TypePieceJointe.liste).filter((type) =>
    type.estRequis(
      dossier.rapportAuLogement,
      dossier.estPersonneMorale
        ? dossier.personneMorale?.typePersonneMorale
        : undefined,
      dossier.estLieDeclaration(),
    ),
  );
};

export const listerTypesPiecesJointesDemandes = (
  dossier: Dossier,
): TypePieceJointe[] => {
  return Object.values(TypePieceJointe.liste).filter((type) =>
    type.estDemande(
      dossier.rapportAuLogement,
      dossier.estPersonneMorale
        ? dossier.personneMorale?.typePersonneMorale
        : undefined,
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
