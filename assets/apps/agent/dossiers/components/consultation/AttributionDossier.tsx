import { Agent, EtatDossier, Redacteur } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";

export const AttributionDossier = observer(
  function AttributionDossierComponent({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
    // Représente le rédacteur à attribuer, présentement en cours de sélection dans le menu déroulant
    const [attributaire, attribuer]: [
      Redacteur | null,
      (redacteur: Redacteur | null) => void,
    ] = useState(dossier.redacteur);

    // Indique si le mode d'édition du rédacteur attribué est activé (= clic sur l'icône "crayon" à côté du rédacteur attribué, seulement octroyé aux agents attributeur)
    const [modeAttribution, setModeAttribution]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
    const [sauvegarderEnCours, setSauvegarderEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    const validerAttribution = async () => {
      if (!attributaire.equals(dossier.redacteur)) {
        setSauvegarderEnCours(true);
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

        if (response.ok) {
          dossier.attribuer(attributaire);
        } // TODO afficher un message en cas d'erreur
        setSauvegarderEnCours(false);
      }

      attribuer(null);
      setModeAttribution(false);
    };

    const marquerDoublonPapier = async () => {
      setSauvegarderEnCours(true);
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/marquer/doublon.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(data.etat);
      }

      attribuer(null);
      setModeAttribution(false);
    };

    return (
      <div>
        {modeAttribution ? (
          <>
            <div className="fr-select-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
              <label className="fr-label" htmlFor="dossier-select-attributaire">
                Rédacteur :
              </label>
              <select
                className="fr-select"
                id="dossier-select-attributaire"
                disabled={sauvegarderEnCours}
                defaultValue={attributaire || ""}
                onChange={(e) => {
                  !!e.target.value &&
                    attribuer(Redacteur.resoudre(parseInt(e.target.value)));
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
                    setModeAttribution(false);
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
                  onClick={() => validerAttribution()}
                >
                  Attribuer
                </button>
              </li>
            </ul>
          </>
        ) : (
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
        )}
        {!modeAttribution &&
          agent.estAttributeur() &&
          dossier.estAAttribuer() && (
            <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--tertiary"
                  type="button"
                  disabled={sauvegarderEnCours}
                  onClick={() => marquerDoublonPapier()}
                >
                  Marquer doublon papier
                </button>
              </li>
              <li>
                <button
                  className="fr-btn fr-btn--sm"
                  type="button"
                  disabled={sauvegarderEnCours}
                  onClick={() => setModeAttribution(true)}
                >
                  Attribuer
                </button>
              </li>
            </ul>
          )}
      </div>
    );
  },
);
