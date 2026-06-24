import {
  SupprimerPieceJointeModale,
  SupprimerPiecesJointesModaleRef,
} from "@/apps/requerant/composants/piecesJointes/SupprimerPieceJointeModale";
import { PieceJointe } from "@/apps/requerant/models";
import { DocumentPDF } from "@/common/composants/document/DocumentPDF.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Download from "@codegouvfr/react-dsfr/Download";
import { default as React, useRef } from "react";

export const AfficherPieceJointe = ({
  pieceJointe,
  telecharger = true,
  supprimer = undefined,
}: {
  pieceJointe: PieceJointe;
  telecharger?: boolean;
  supprimer?: (pieceJointe: PieceJointe) => void | Promise<void> | undefined;
}) => {
  // La référence vers la modale de suppression de pièce jointe
  const refModaleSuppressionPieceJointe =
    useRef<SupprimerPiecesJointesModaleRef>(null);

  return (
    <>
      {supprimer && (
        <SupprimerPieceJointeModale
          ref={refModaleSuppressionPieceJointe}
          pieceJointe={pieceJointe}
          onConfirme={async () => {
            await supprimer(pieceJointe);
            refModaleSuppressionPieceJointe.current?.fermer();
          }}
        />
      )}

      {(telecharger || supprimer) && (
        <div className="fr-grid-row fr-col-12">
          {telecharger && (
            <Download
              className="fr-col-6"
              details={`${pieceJointe.infoFichier}`}
              label={pieceJointe.nom}
              linkProps={{
                href: `${pieceJointe.url}?download`,
              }}
            />
          )}

          {supprimer && (
            <ButtonsGroup
              className={telecharger ? "fr-col-6" : "fr-col-12"}
              inlineLayoutWhen="always"
              alignment="right"
              buttonsIconPosition="right"
              buttonsSize="small"
              buttons={[
                {
                  children: "Supprimer cette pièce jointe",
                  iconId: "fr-icon-delete-line",
                  priority: "tertiary no outline",
                  onClick: () =>
                    refModaleSuppressionPieceJointe.current?.ouvrir(),
                },
              ]}
            />
          )}
        </div>
      )}

      {pieceJointe.estPDF() ? (
        <>
          <DocumentPDF url={`${window.location.origin}${pieceJointe.url}`} />
        </>
      ) : (
        <img
          src={pieceJointe.url}
          alt={pieceJointe.nom}
          style={{
            width: "100%",
            maxHeight: "100vh",
            objectFit: "contain",
          }}
        />
      )}
    </>
  );
};
