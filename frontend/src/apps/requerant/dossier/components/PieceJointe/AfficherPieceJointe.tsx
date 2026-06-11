import { PieceJointe } from "@/apps/requerant/models";
import { DocumentPDF } from "@/common/composants/document/DocumentPDF.tsx";
import Download from "@codegouvfr/react-dsfr/Download";
import { default as React } from "react";

export const AfficherPieceJointe = ({
  pieceJointe,
  telecharger = true,
}: {
  pieceJointe: PieceJointe;
  telecharger?: boolean;
}) => {
  return (
    <>
      {telecharger && (
        <Download
          className="fr-col-12"
          details={`${pieceJointe.infoFichier}`}
          label={pieceJointe.nom}
          linkProps={{
            href: `${pieceJointe.url}?download`,
          }}
        />
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
