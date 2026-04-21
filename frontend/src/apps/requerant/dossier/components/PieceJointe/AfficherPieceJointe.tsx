import { PieceJointe } from "@/apps/requerant/models";
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
        <object
          data={pieceJointe.url}
          type="application/pdf"
          style={{
            width: "100%",
            aspectRatio: "210/297",
          }}
        ></object>
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
