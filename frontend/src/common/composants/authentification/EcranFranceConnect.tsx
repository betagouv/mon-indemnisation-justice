import React from "react";

type EcranFranceConnectProps = {
  titre?: string;
  description?: string;
  avertissement?: React.ReactNode;
  erreur?: string;
  onRetour: () => void;
  bouton: React.ReactNode;
};

export function EcranFranceConnect({
  titre = "Se connecter avec FranceConnect",
  description = "FranceConnect est la solution proposée par l'État pour sécuriser et simplifier la connexion aux services en ligne.",
  avertissement,
  erreur,
  onRetour,
  bouton,
}: EcranFranceConnectProps) {
  return (
    <>
      <button
        type="button"
        className="fr-link fr-icon-arrow-left-line fr-link--icon-left"
        onClick={onRetour}
      >
        Choisir un autre moyen pour s'identifier
      </button>

      {avertissement && <div className="fr-highlight fr-my-3w">{avertissement}</div>}

      {erreur && (
        <div className="fr-alert fr-alert--error fr-alert--sm fr-mb-2w">
          <p>{erreur}</p>
        </div>
      )}

      <div className="fr-grid-row fr-grid-row--center">
        <h2 className="fr-h5">{titre}</h2>
      </div>
      <div className="fr-grid-row fr-grid-row--center">
        <p className="fr-text--sm">{description}</p>
      </div>
      <div className="fr-grid-row fr-grid-row--center">{bouton}</div>
    </>
  );
}

type TuileFranceConnectProps = {
  detail: string;
  onClick: () => void;
};

export function TuileFranceConnect({ detail, onClick }: TuileFranceConnectProps) {
  return (
    <div className="fr-tile fr-tile--horizontal fr-tile--sm fr-enlarge-link mij-tuile-franceconnect">
      <div className="fr-tile__body">
        <div className="fr-tile__content">
          <h3 className="fr-tile__title">
            <a
              href="#"
              onClick={(e) => {
                e.preventDefault();
                onClick();
              }}
            >
              Avec FranceConnect
            </a>
          </h3>
          <p className="fr-tile__detail">{detail}</p>
        </div>
      </div>
      <div className="fr-tile__header">
        <div className="fr-tile__pictogram">
          <svg aria-hidden="true" className="fr-artwork" viewBox="0 0 80 80" width="80px" height="80px">
            <use className="fr-artwork-decorative" href="/dsfr/artwork/pictograms/digital/avatar.svg#artwork-decorative"></use>
            <use className="fr-artwork-minor" href="/dsfr/artwork/pictograms/digital/avatar.svg#artwork-minor"></use>
            <use className="fr-artwork-major" href="/dsfr/artwork/pictograms/digital/avatar.svg#artwork-major"></use>
          </svg>
        </div>
      </div>
    </div>
  );
}
