import React from "react";
import { BaseDossier } from "@/common/models";
import Badge from "@codegouvfr/react-dsfr/Badge";

export const BadgesDossier = ({
  dossier,
  inline = false,
}: {
  dossier: BaseDossier;
  inline?: boolean;
}) => {
  return (
    <ul
      style={{
        padding: 0,
        listStyleType: "none",
        listStylePosition: "outside",
      }}
    >
      {dossier.issuDeclarationFDO ? (
        <li style={inline ? { display: "inline", marginRight: ".5vw" } : {}}>
          <Badge severity="new" as="span" small={true}>
            Déclaration FDO
          </Badge>
        </li>
      ) : (
        <>
          {dossier.typeAttestation === "NOUVELLE_ATTESTATION" && (
            <li
              style={inline ? { display: "inline", marginRight: ".5vw" } : {}}
            >
              <Badge severity="success" as="span" small={true}>
                Nouv. attestation
              </Badge>
            </li>
          )}

          {dossier.estEligible && (
            <li
              style={inline ? { display: "inline", marginRight: ".5vw" } : {}}
            >
              <Badge noIcon severity="info" as="span" small={true}>
                Éligible
              </Badge>
            </li>
          )}
        </>
      )}
    </ul>
  );
};
