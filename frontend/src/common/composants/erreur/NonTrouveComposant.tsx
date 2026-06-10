import { MiseEnAvant } from "@/common/composants/dsfr/MiseEnAvant";
import artworkSystemTechnicalErrorUrl from "@codegouvfr/react-dsfr/dsfr/artwork/pictograms/system/technical-error.svg?url&no-inline";
import React, { ReactNode } from "react";

export const NonTrouveComposant = ({
  titre,
  retour,
}: {
  titre?: string;
  retour?: ReactNode;
}) => {
  return (
    <MiseEnAvant pictogrammeUrl={artworkSystemTechnicalErrorUrl}>
      <h1>{titre || <>Page non trouvée</>}</h1>

      <p className="fr-text--lead fr-mb-3w">
        La page que vous cherchez est introuvable. Excusez-nous pour la gène
        occasionnée.
      </p>
      <p className="fr-text--sm fr-mb-5w">
        Si vous avez tapé l'adresse web dans le navigateur, vérifiez qu'elle est
        correcte. La page n’est peut-être plus disponible.
        <br />
        Dans ce cas, pour continuer votre visite vous pouvez consulter notre
        page d’accueil ou suivre un des liens du menu. <br />
      </p>
      {retour}
    </MiseEnAvant>
  );
};
