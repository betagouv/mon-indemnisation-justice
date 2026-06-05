import { MiseEnAvant } from "@/common/composants/dsfr/MiseEnAvant";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import artworkSystemErrorUrl from "@codegouvfr/react-dsfr/dsfr/artwork/pictograms/system/error.svg?url&no-inline";
import React, { type ReactNode } from "react";

export const ErreurComposant = ({
  titre,
  erreur,
  retour,
  action,
}: {
  titre?: string;
  erreur: Error;
  retour?: ReactNode;
  action?: ReactNode;
}) => {
  return (
    <MiseEnAvant pictogrammeUrl={artworkSystemErrorUrl} action={action}>
      <h1>{titre || <>Erreur technique</>}</h1>

      <p className="fr-text--lead fr-mb-3w">
        Une erreur technique est survenue. Excusez-nous pour la gène
        occasionnée.
      </p>
      <p className="fr-text--sm fr-mb-5w">
        Cet incident est automatiquement remonté aux équipes informatiques qui
        feront le nécessaire pour le résoudre au plus vite, vous n'avez donc pas
        à nous en informer
      </p>
      <Accordion label={"Détails de l'erreur"}>
        <dl>
          <dt>Message</dt>
          <dd>{erreur.message}</dd>

          <dt>Trace</dt>
          <dd>
            <pre>
              <code>
                {erreur.stack?.split("\n").map((ligne, i) => (
                  <React.Fragment key={i}>
                    {ligne}
                    {"\n"}
                  </React.Fragment>
                ))}
              </code>
            </pre>
          </dd>
        </dl>
      </Accordion>
      {retour}
    </MiseEnAvant>
  );
};
