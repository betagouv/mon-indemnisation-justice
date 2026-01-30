import React, { useMemo } from "react";
import { Document, DocumentType, DossierDetail } from "@/common/models";

export const SectionMenuPieceJointe = ({
  titre,
  piecesJointes,
  types,
  pieceJointeSelectionnee,
  onSelection,
}: {
  titre: string;
  piecesJointes: Document[];
  types: DocumentType[];
  pieceJointeSelectionnee?: Document;
  onSelection: (pieceJointe: Document) => void;
}) => {
  const documents: Map<DocumentType, Document[]> = useMemo(() => {
    return new Map(
      types.map((t) => [t, piecesJointes.filter((pj) => pj.type == t)]),
    );
  }, [piecesJointes.map((pj: Document) => pj.id).join("-")]);

  return (
    <>
      <h4 className="fr-text--lg">{titre}</h4>

      {[...documents.entries()].map(([type, pjs]) => (
        <ul key={type.type}>
          <li
            data-section-vide={pjs.length === 0 ? "" : undefined}
            data-section-active={
              pjs.find((pj) => pj.id === pieceJointeSelectionnee?.id) ||
              undefined
            }
          >
            {type.libelle}
          </li>
          <ul>
            {pjs.map((pj) => (
              <li
                key={pj.id}
                data-document-selectionne={
                  pj.id === pieceJointeSelectionnee?.id || undefined
                }
              >
                <a href={void 0} onClick={() => onSelection(pj)}>
                  {pj.originalFilename}
                </a>
              </li>
            ))}
          </ul>
        </ul>
      ))}
    </>
  );
};

export const MenuPieceJointe = ({
  dossier,
  pieceJointeSelectionnee,
  onSelection,
}: {
  dossier: DossierDetail;
  pieceJointeSelectionnee?: Document;
  onSelection: (pieceJointe: Document) => void;
}) => {
  return (
    <>
      <SectionMenuPieceJointe
        titre={"Du requérant"}
        piecesJointes={dossier.piecesJointes}
        types={Document.typesUsager}
        pieceJointeSelectionnee={pieceJointeSelectionnee}
        onSelection={onSelection}
      ></SectionMenuPieceJointe>

      {dossier.declarationFDO && (
        <SectionMenuPieceJointe
          titre={"De l'agent des FDO"}
          piecesJointes={dossier.declarationFDO.piecesJointes}
          types={Document.typesFDO}
          pieceJointeSelectionnee={pieceJointeSelectionnee}
          onSelection={onSelection}
        ></SectionMenuPieceJointe>
      )}
    </>
  );
};
