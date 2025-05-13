import { Agent, Redacteur } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";

const attribuer = async ({
  dossier,
  attributaire,
}: {
  dossier: DossierDetail;
  attributaire: Redacteur;
}) => {
  const response = await fetch(
    `/agent/redacteur/dossier/${dossier.id}/attribuer.json`,
    {
      method: "POST",
      headers: {
        "Content-type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        redacteur_id: attributaire.id,
      }),
    },
  );

  return response.ok;
};

export const AttributionDossier = observer(
  function AttributionDossierComponent({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
    // Représente le rédacteur à attribuer, présentement en cours de sélection dans le menu déroulant
    const [attributaire, setAttributaire]: [
      Redacteur | null,
      (redacteur: Redacteur | null) => void,
    ] = useState(dossier.redacteur);

    // Indique si l'attribution du rédacteur est activée (= clic sur l'icône "crayon" à côté du rédacteur attribué, seulement octroyé aux agents attributeur)
    const [attributionEnCours, setAttributionEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
    const [sauvegarderEnCours, setSauvegarderEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    const valider = async () => {
      if (!attributaire.equals(dossier.redacteur)) {
        setSauvegarderEnCours(true);
        const succes = await attribuer({ dossier, attributaire });

        if (succes) {
          dossier.attribuer(attributaire);
        }

        setAttributaire(null);
        setAttributionEnCours(false);
      }
    };

    return (
      <div>
        {attributionEnCours ? (
          <>
            <div className="fr-select-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
              <label className="fr-label" htmlFor="dossier-select-attributaire">
                Rédacteur :
              </label>
              <select
                className="fr-select"
                id="dossier-select-attributaire"
                disabled={sauvegarderEnCours}
                defaultValue={attributaire?.id || ""}
                onChange={(e) => {
                  !!e.target.value &&
                    setAttributaire(
                      Redacteur.resoudre(parseInt(e.target.value)),
                    );
                }}
              >
                <option value="" disabled hidden>
                  Sélectionnez un rédacteur
                </option>
                {Redacteur.catalog().map((redacteur: Redacteur) => (
                  <option value={redacteur.id} key={redacteur.id}>
                    {redacteur.nom}
                  </option>
                ))}
              </select>
            </div>

            <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                  type="button"
                  disabled={sauvegarderEnCours}
                  onClick={() => {
                    setAttributionEnCours(false);
                  }}
                >
                  {sauvegarderEnCours ? (
                    <i>Sauvegarde en cours ...</i>
                  ) : (
                    <>Annuler</>
                  )}
                </button>
              </li>
              <li>
                <button
                  className="fr-btn fr-btn--sm"
                  type="button"
                  disabled={sauvegarderEnCours || !attributaire}
                  onClick={() => valider()}
                >
                  Attribuer
                </button>
              </li>
            </ul>
          </>
        ) : (
          <>
            <p className="fr-m-1w">
              Ce dossier
              {dossier.redacteur ? (
                agent.equals(dossier.redacteur) ? (
                  <>
                    {" "}
                    <b>vous</b> est attribué
                  </>
                ) : (
                  <>
                    {" "}
                    est attribué à <u> {dossier.redacteur.nom} </u>
                  </>
                )
              ) : (
                <>
                  {" "}
                  n'est <i>pas encore attribué</i> à un rédacteur{" "}
                </>
              )}
            </p>
            {agent.estAttributeur() && dossier.estAAttribuer() && (
              <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                <li>
                  <button
                    className="fr-btn fr-btn--sm"
                    type="button"
                    disabled={sauvegarderEnCours}
                    onClick={() => setAttributionEnCours(true)}
                  >
                    Attribuer
                  </button>
                </li>
              </ul>
            )}
          </>
        )}
      </div>
    );
  },
);
