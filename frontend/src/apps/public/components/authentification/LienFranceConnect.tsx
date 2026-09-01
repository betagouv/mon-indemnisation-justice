import { FranceConnectButton } from "@codegouvfr/react-dsfr/FranceConnectButton";
import React from "react";

const URL_LANCEMENT_FRANCE_CONNECT = "/connexion/usager/lancer";

/**
 * Bouton officiel FranceConnect (composant DSFR) : la cible est une route Symfony, pas une route de l'app
 * React, donc on utilise `url` (un <a> classique) plutôt que `onClick`, pour une navigation complète du
 * navigateur jamais interceptée par le routeur SPA.
 */
export function LienFranceConnect() {
  return <FranceConnectButton url={URL_LANCEMENT_FRANCE_CONNECT} />;
}
