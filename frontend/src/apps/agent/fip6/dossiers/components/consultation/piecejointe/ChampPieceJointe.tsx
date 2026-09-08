import React, { useMemo } from "react";

import { fr } from "@codegouvfr/react-dsfr";
import { Document } from "@common/models";

export const ChampPieceJointe = function PieceJointe({
  pieceJointe,
  className,
  lienTelechargement,
}: {
  pieceJointe: Document;
  className?: string;
  lienTelechargement?: string | ((pieceJointe: Document) => string);
}) {
  const url = useMemo<string>(() => {
    if (lienTelechargement) {
      if (typeof lienTelechargement === "function") {
        return lienTelechargement(pieceJointe);
      }

      return lienTelechargement;
    }

    // @deprecated préférer la méthode `lienTelechargement`
    return pieceJointe.url;
  }, [pieceJointe.id, pieceJointe.fileHash, lienTelechargement]);

  return (
    <div className={`${fr.cx("fr-grid-row")} ${className ?? ""}`}>
      {pieceJointe.estPDF() ? (
        <object
          key={url}
          data={url}
          type="application/pdf"
          style={{
            width: "100%",
            aspectRatio: "210/297",
          }}
        ></object>
      ) : (
        <img
          key={url}
          src={url}
          alt={pieceJointe.originalFilename}
          style={{
            width: "100%",
            maxHeight: "100vh",
            objectFit: "contain",
          }}
        />
      )}
    </div>
  );
};
