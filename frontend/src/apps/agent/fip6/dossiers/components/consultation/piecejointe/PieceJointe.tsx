import React, { useMemo } from "react";

import { Agent, Document } from "@/common/models";
import { fr } from "@codegouvfr/react-dsfr";

export const PieceJointe = function PieceJointe({
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
  }, [pieceJointe.id, lienTelechargement]);

  return (
    <div className={`${fr.cx("fr-grid-row")} ${className ?? ""}`}>
      {pieceJointe.estPDF() ? (
        <object
          data={url}
          type="application/pdf"
          style={{
            width: "100%",
            aspectRatio: "210/297",
          }}
        ></object>
      ) : (
        <img
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
