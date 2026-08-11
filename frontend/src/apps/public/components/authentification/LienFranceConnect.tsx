import React from "react";

const URL_LANCEMENT_FRANCE_CONNECT = "/connexion/usager/lancer";

type LienFranceConnectProps = {
  action: "S’identifier avec" | "S’inscrire avec";
};

/**
 * Bouton officiel FranceConnect.
 * Un <a> classique (pas de Link Tanstack) : la cible est une route Symfony, pas une route de l'app React,
 * donc on veut une navigation complète du navigateur, jamais interceptée par le routeur SPA.
 */
export function LienFranceConnect({ action }: LienFranceConnectProps) {
  return (
    <>
      <div className="fr-notice fr-notice--info fr-mb-2w">
        <div className="fr-container">
          <div className="fr-notice__body">
            <p>
              FranceConnect est la solution proposée par l’État pour sécuriser et simplifier la connexion à vos
              services en ligne
            </p>
          </div>
        </div>
      </div>

      <div className="fr-connect-group">
        <a href={URL_LANCEMENT_FRANCE_CONNECT} target="_self" className="fr-btn fr-connect">
          <span className="fr-connect__login">{action}</span>
          <span className="fr-connect__brand">FranceConnect</span>
        </a>
        <p>
          <a
            href="https://franceconnect.gouv.fr/"
            target="_blank"
            rel="noopener noreferrer"
            className="fr-link--sm"
          >
            Qu’est-ce que FranceConnect ?
          </a>
        </p>
      </div>
    </>
  );
}
