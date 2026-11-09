import React, { useState } from "react";

export type RegleMotDePasse = {
  label: string;
  test: (motDePasse: string) => boolean;
};

// Règles par défaut (parcours dysfonctionnement). Le parcours bris-de-porte garde volontairement sa
// propre règle historique (8 caractères minimum, sans caractère spécial) en la passant explicitement
// via la prop `regles`, pour ne rien changer à son comportement de production.
export const REGLES_MOT_DE_PASSE_DEFAUT: RegleMotDePasse[] = [
  { label: "12 caractères minimum", test: (m) => m.length >= 12 },
  { label: "1 caractère spécial minimum", test: (m) => /[^a-zA-Z0-9]/.test(m) },
  { label: "1 chiffre minimum", test: (m) => /\d/.test(m) },
];

export function motDePasseValide(
  motDePasse: string,
  regles: RegleMotDePasse[] = REGLES_MOT_DE_PASSE_DEFAUT,
): string | undefined {
  const regleEnDefaut = regles.find((regle) => !regle.test(motDePasse));

  return regleEnDefaut ? `Le mot de passe doit contenir au moins ${regleEnDefaut.label}` : undefined;
}

type CreationDeCompteProps = {
  identifiant?: string; // si fourni : affiché en lecture seule (cas d'une invitation, identité déjà connue)
  motDePasse: string;
  confirmation: string;
  cguOk: boolean;
  cguUrl: string;
  onMotDePasseChange: (valeur: string) => void;
  onConfirmationChange: (valeur: string) => void;
  onCguOkChange: (valeur: boolean) => void;
  erreur?: string;
  libelleSubmit?: string;
  enCours?: boolean;
  regles?: RegleMotDePasse[];
};

export function CreationDeCompte({
  identifiant,
  motDePasse,
  confirmation,
  cguOk,
  cguUrl,
  onMotDePasseChange,
  onConfirmationChange,
  onCguOkChange,
  erreur,
  libelleSubmit = "Créer mon compte",
  enCours = false,
  regles = REGLES_MOT_DE_PASSE_DEFAUT,
}: CreationDeCompteProps) {
  const [motDePasseRevele, setMotDePasseRevele] = useState(false);
  const [confirmationRevelee, setConfirmationRevelee] = useState(false);

  return (
    <>
      {erreur && (
        <div className="fr-alert fr-alert--error fr-alert--sm fr-mb-2w">
          <p>{erreur}</p>
        </div>
      )}

      {identifiant && (
        <div className="fr-input-group">
          <label className="fr-label" htmlFor="creation-de-compte-identifiant">
            Identifiant
          </label>
          <input
            id="creation-de-compte-identifiant"
            className="fr-input"
            type="text"
            value={identifiant}
            disabled
            readOnly
          />
        </div>
      )}

      <div className="fr-input-group">
        <div className="fr-password" data-fr-js-password="true">
          <label className="fr-label" htmlFor="creation-de-compte-mot-de-passe">
            Mot de passe
          </label>
          <div className="fr-input-wrap">
            <input
              name="motDePasse"
              id="creation-de-compte-mot-de-passe"
              className="fr-password__input fr-input"
              type={motDePasseRevele ? "text" : "password"}
              autoComplete="new-password"
              value={motDePasse}
              onChange={(e) => onMotDePasseChange(e.target.value)}
            />
          </div>
          <div className="fr-password__checkbox fr-checkbox-group fr-checkbox-group--sm">
            <input
              aria-label="Afficher le mot de passe"
              id="creation-de-compte-mot-de-passe-toggle"
              type="checkbox"
              checked={motDePasseRevele}
              onChange={() => setMotDePasseRevele(!motDePasseRevele)}
            />
            <label className="fr-password__checkbox fr-label" htmlFor="creation-de-compte-mot-de-passe-toggle">
              {motDePasseRevele ? "Masquer" : "Afficher"}
            </label>
          </div>
        </div>
        <div className="fr-messages-group">
          <p className="fr-message">Votre mot de passe doit contenir :</p>
          {regles.map((regle) => (
            <p key={regle.label} className="fr-message fr-message--info">
              {regle.label}
            </p>
          ))}
        </div>
      </div>

      <div className="fr-input-group">
        <div className="fr-password" data-fr-js-password="true">
          <label className="fr-label" htmlFor="creation-de-compte-confirmation">
            Confirmation du mot de passe
          </label>
          <div className="fr-input-wrap">
            <input
              name="confirmation"
              id="creation-de-compte-confirmation"
              className="fr-password__input fr-input"
              type={confirmationRevelee ? "text" : "password"}
              autoComplete="new-password"
              value={confirmation}
              onChange={(e) => onConfirmationChange(e.target.value)}
            />
          </div>
          <div className="fr-password__checkbox fr-checkbox-group fr-checkbox-group--sm">
            <input
              aria-label="Afficher la confirmation du mot de passe"
              id="creation-de-compte-confirmation-toggle"
              type="checkbox"
              checked={confirmationRevelee}
              onChange={() => setConfirmationRevelee(!confirmationRevelee)}
            />
            <label className="fr-password__checkbox fr-label" htmlFor="creation-de-compte-confirmation-toggle">
              {confirmationRevelee ? "Masquer" : "Afficher"}
            </label>
          </div>
        </div>
      </div>

      <div className="fr-fieldset fr-my-2w">
        <div className="fr-fieldset__content">
          <div className="fr-checkbox-group">
            <input
              type="checkbox"
              id="creation-de-compte-cgu-ok"
              name="cguOk"
              checked={cguOk}
              onChange={(e) => onCguOkChange(e.target.checked)}
            />
            <label className="fr-label" htmlFor="creation-de-compte-cgu-ok">
              J'atteste avoir pris connaissances des&nbsp;
              <a className="fr-link" href={cguUrl} target="_blank" rel="noreferrer">
                Conditions générales d'utilisation
              </a>
            </label>
          </div>
        </div>
      </div>

      <button className="fr-btn" type="submit" disabled={enCours} style={{ width: "100%", justifyContent: "center" }}>
        {enCours ? "Création en cours" : libelleSubmit}
      </button>
    </>
  );
}
