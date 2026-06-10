import { Document } from "@/common/models";
import React from "react";
import Download from "@codegouvfr/react-dsfr/Download";

const component = function TelechargerPieceJointe({
  pieceJointe,
  className,
}: {
  pieceJointe: Document;
  className?: string;
}) {
  return (
    <Download
      className={className}
      details={pieceJointe?.infoFichier}
      label={pieceJointe.originalFilename}
      linkProps={{
        href: `${window.location.origin}${pieceJointe.url}`,
        target: "_self",
      }}
    />
  );
};

export { component as TelechargerPieceJointe };
