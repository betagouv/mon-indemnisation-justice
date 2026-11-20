import { MiseEnAvant } from "@/common/composants/dsfr/MiseEnAvant";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import artworkSystemErrorUrl from "@codegouvfr/react-dsfr/dsfr/artwork/pictograms/system/error.svg?url&no-inline";
import { Chargement } from "@/common/composants/dsfr/Chargement";
import { ApplicationRechargeur } from "@/common/services/ApplicationRechargeur";
import React, { type ReactNode, useEffect, useState } from "react";

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
  // Erreur de chargement de module (ex : chunk supprimé suite à un redéploiement) : on recharge la page une seule fois
  const [rechargementEnCours, setRechargementEnCours] = useState(false);

  useEffect(() => {
    if (ApplicationRechargeur.rechargerSiErreurChargementModule(erreur)) {
      setRechargementEnCours(true);
    }
  }, [erreur]);

  if (import.meta.env.DEV) {
    console.error(erreur);
  }

  if (rechargementEnCours) {
    return (
      <Chargement
        titre="Mon Indemnisation Justice"
        message="La plateforme doit être mise à jour, rechargement de la page en cours..."
      />
    );
  }

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

          {import.meta.env.DEV && (
            <>
              <dt>Trace</dt>
              <dd>
                <ul style={{ listStyle: "none" }}>
                  {erreur.stack?.split("\n").map((entree, i) => {
                    const [cible, chemin] = entree.split("@");
                    const elements = chemin?.split(":");

                    const url = elements?.slice(0, -2).join(":") || undefined;
                    const ligne = elements?.at(-2) || undefined;
                    const async = url?.startsWith("async*") || false;

                    return (
                      <li
                        key={`stack-trace-ligne-${i}`}
                        style={{
                          padding: 0,
                          margin: 0,
                          fontFamily: "monospace",
                          fontSize: ".7rem",
                        }}
                      >
                        {url ? (
                          <>
                            <a href={url} style={{}}>
                              {async && <>async </>}
                              {cible.replace(/^async\*/, "")}
                            </a>
                            {ligne ? `,ligne ${ligne}` : ""}
                          </>
                        ) : (
                          <span>
                            {async && <>async </>}
                            {cible.replace(/^async\*/, "")}
                          </span>
                        )}
                      </li>
                    );
                  })}
                </ul>
              </dd>
            </>
          )}
        </dl>
      </Accordion>
      {retour}
    </MiseEnAvant>
  );
};
