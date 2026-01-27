import {
  AjoutPieceJointe,
  PieceJointe,
  TelechargerPieceJointe,
} from "@/apps/agent/fip6/dossiers/components/consultation/piecejointe";
import { MenuPieceJointe } from "@/apps/agent/fip6/dossiers/components/consultation/piecejointe/MenuPieceJointe";
import {
  ouvrirModaleSuppressionPieceJointe,
  SuppressionPieceJointe,
} from "@/apps/agent/fip6/dossiers/components/consultation/piecejointe/SuppressionPieceJointe";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, { ReactNode, useCallback, useState } from "react";
import { Agent, Document, DossierDetail } from "@/common/models";

export const PiecesJointes = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ReactNode => {
  console.log(dossier.declarationFDO?.piecesJointes);

  const selectionnerPremierePieceJointe = useCallback(
    () =>
      dossier.piecesJointes.at(0) ??
      dossier.declarationFDO?.piecesJointes.at(0),
    [dossier.id],
  );

  // Pièce jointe en cours de visualisation
  const [pieceJointe, selectionnerPieceJointe]: [
    Document | undefined,
    (document: Document) => void,
  ] = useState<Document | undefined>(selectionnerPremierePieceJointe());

  return (
    <>
      <h3>Pièces jointes</h3>

      <div className="fr-grid-row">
        <div className="fr-col-3 mij-dossier-documents-liste">
          {/* Ajout d'une pièce jointe */}
          <AjoutPieceJointe
            dossier={dossier}
            agent={agent}
            onAjoute={(nouvellePieceJointe: Document) =>
              selectionnerPieceJointe(nouvellePieceJointe)
            }
          />

          <MenuPieceJointe
            dossier={dossier}
            pieceJointeSelectionnee={pieceJointe}
            onSelection={(pj: Document) => selectionnerPieceJointe(pj)}
          />
        </div>
        {/* Affichage de la pièce jointe sélectionnée */}
        <div className="fr-col-9 fr-px-4w">
          {pieceJointe ? (
            <>
              <div className="fr-grid-row fr-col-12">
                <h4>{pieceJointe.originalFilename}</h4>

                <TelechargerPieceJointe
                  className="fr-grid-row fr-col-12"
                  pieceJointe={pieceJointe}
                />

                {pieceJointe.estEditable(dossier, agent) && (
                  <>
                    <SuppressionPieceJointe
                      pieceJointe={pieceJointe}
                      dossier={dossier}
                      onSupprime={() => selectionnerPremierePieceJointe()}
                    />
                    <ButtonsGroup
                      className="fr-grid-row fr-col-12"
                      inlineLayoutWhen="always"
                      alignment="right"
                      buttonsIconPosition="right"
                      buttonsSize="small"
                      buttons={[
                        {
                          children: "Supprimer",
                          iconId: "fr-icon-delete-bin-line",
                          priority: "secondary",
                          onClick: () => ouvrirModaleSuppressionPieceJointe(),
                        },
                      ]}
                    />
                  </>
                )}

                <PieceJointe pieceJointe={pieceJointe} className="fr-col-12" />
              </div>
            </>
          ) : (
            <i>Aucune pièce jointe sélectionnée</i>
          )}
        </div>
      </div>
    </>
  );
};
