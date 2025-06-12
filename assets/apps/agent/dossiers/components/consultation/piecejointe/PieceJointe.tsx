import React from "react";

import {Document} from "@/apps/agent/dossiers/models";
//import { useStyles } from "tss-react/dsfr";
import {fr} from "@codegouvfr/react-dsfr";

export const PieceJointe = function PieceJointe({
  pieceJointe,
  className,
}: {
  pieceJointe: Document;
  className?: string;
}) {
  //const { cx } = useStyles();

  return (
    <div className={`${fr.cx("fr-grid-row")} ${className ?? ''}`}>
      {pieceJointe.isPDF() ? (
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
