import React, { useMemo } from "react";

import { fr } from "@codegouvfr/react-dsfr";
import { contenuFichier } from "@common/services/fichier.ts";

export const PrevisualiserPieceJointe = ({
  fichier,
  className,
}: {
  fichier: File;
  className?: string;
}) => {
  const url = useMemo<string>(() => contenuFichier(fichier), [fichier]);

  return (
    <div className={`${fr.cx("fr-grid-row")} ${className ?? ""}`}>
      {fichier.type == "application/pdf" && (
        <object
          data={url}
          type="application/pdf"
          style={{
            width: "100%",
            aspectRatio: "210/297",
          }}
        ></object>
      )}
      {fichier.type.match(/^image\//) && (
        <img
          src={url}
          alt={fichier.name}
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
