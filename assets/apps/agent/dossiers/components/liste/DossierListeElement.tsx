import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";

export function DossierListeElement() {
  return (
    <div className="fr-grid-row mij-dossier-liste-element">
      <div className="fr-col-3">
        <strong className="fr-text--bold fr-text--lg">BRI/20250123/001</strong>
      </div>

      <div className="fr-col-7 mij-dossier-details">
        <ul>
          <li>Mr Jean DUPONT</li>
          <li>1 732,56 €</li>
          <li>validé le 15 août par Sylvie VOKO</li>
        </ul>
      </div>

      <div className="fr-col-2">
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="left"
          buttonsEquisized={false}
          buttonsSize="small"
          buttons={[
            {
              size: "small",
              priority: "tertiary no outline",
              iconId: "fr-icon-download-line",
              children: "Télécharger",
              className: "fr-mb-0",
            },
            {
              size: "small",
              priority: "tertiary no outline",
              iconId: "fr-icon-eye-line",
              children: "Voir",
              className: "fr-mb-0",
            },
          ]}
        />
      </div>
    </div>
  );
}
