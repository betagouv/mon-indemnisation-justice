import {
  Agent,
  Document,
  DocumentType,
  Redacteur,
} from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";

export const ConsultationDossierApp = observer(
  ({ dossier, agent }: { dossier: DossierDetail; agent: Agent }) => {
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

    return (
      <>
        <div className="fr-container fr-container--fluid fr-mt-2w">
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12 fr-p-3w">
              {/*  Résumé de l'état + boutons */}
              <div className="fr-dossier-etat fr-p-4w">
                <h3 className="">Dossier {dossier.reference}</h3>

                <div className="">
                  <p className="fr-badge fr-badge--info fr-badge--no-icon fr-py-1w fr-px-2w">
                    À statuer
                  </p>
                </div>

                <p className="fr-m-1w">
                  Déposé le{" "}
                  {dossier.dateDepot?.toLocaleString("fr-FR", {
                    weekday: "long",
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                  })}
                  , à{" "}
                  {dossier.dateDepot?.toLocaleString("fr-FR", {
                    hour: "numeric",
                    minute: "numeric",
                  })}{" "}
                  par <u>{dossier.requerant.nomSimple()}</u>
                </p>

                <div>
                  {modeAttribution ? (
                    <>
                      <div className="fr-select-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
                        <label
                          className="fr-label"
                          htmlFor="dossier-select-attributaire"
                        >
                          Rédacteur :
                        </label>
                        <select
                          className="fr-select"
                          id="dossier-select-attributaire"
                          disabled={sauvegarderEnCours}
                          defaultValue={attributaire || ""}
                          onChange={(e) => {
                            !!e.target.value &&
                              attribuer(
                                Redacteur.resoudre(parseInt(e.target.value)),
                              );
                          }}
                        >
                          <option value="" disabled hidden>
                            Sélectionnez un rédacteur
                          </option>
                          {Redacteur.catalog
                            .values()
                            .toArray()
                            .map((redacteur: Redacteur) => (
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
                            onClick={validerAttribution}
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
                      )}{" "}
                      {agent.estAttributeur() && (
                        <a
                          role="button"
                          className="fr-link"
                          onClick={() => {
                            setModeAttribution(true);
                            attribuer(null);
                          }}
                        >
                          <span
                            className="fr-icon-pencil-line"
                            aria-hidden="true"
                          ></span>
                        </a>
                      )}
                    </p>
                  )}
                </div>

                <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                  {/*  Actions, à adapter en fonction de l'état du dossier et des droits de l'agent */}
                  {/*}
                                <li>
                                    <button className="fr-btn fr-btn--sm fr-btn--secondary" disabled>
                                        Refuser le dossier
                                    </button>

                                </li>
                                <li>
                                    <button className="fr-btn fr-btn--sm" type="button" data-fr-opened="false"
                                            aria-controls="fr-modal-pre-valider" disabled
                                    >
                                        Accepter l'indemnisation
                                    </button>
                                </li>
                                */}
                </ul>
              </div>

              {/* Accordéon de section */}
              <div className="fr-my-2w">
                <div className="fr-accordions-group">
                  <section className="fr-accordion">
                    <h3 className="fr-accordion__title">
                      <button
                        className="fr-accordion__btn"
                        aria-expanded="true"
                        aria-controls="accordion-114"
                      >
                        Informations du dossier
                      </button>
                    </h3>
                    <div className="fr-collapse" id="accordion-114">
                      <div className="fr-grid-column">
                        <h5>Requérant</h5>

                        <ul className="fr-list">
                          <li>
                            <b>Nom: </b> {dossier.requerant.nomComplet()}
                          </li>
                          <li>
                            <b>Prénom(s): </b>{" "}
                            {dossier.requerant.prenoms
                              .filter((p) => !!p)
                              .join(", ")}
                          </li>
                          {dossier.requerant.dateNaissance && (
                            <li>
                              <b>
                                Né{dossier.requerant.estFeminin() ? "e" : ""}
                              </b>{" "}
                              le{" "}
                              {dossier.requerant.dateNaissance.toLocaleString(
                                "fr-FR",
                                {
                                  //weekday: 'long',
                                  day: "numeric",
                                  month: "long",
                                  year: "numeric",
                                },
                              )}
                              , à {dossier.requerant.communeNaissance}{" "}
                              {dossier.requerant.paysNaissance
                                ? `(${dossier.requerant.paysNaissance})`
                                : ""}
                            </li>
                          )}
                          {dossier.requerant.estPersonneMorale() && (
                            <li>
                              Représentant
                              {dossier.requerant.estFeminin() ? "e" : ""} légal
                              {dossier.requerant.estFeminin() ? "e" : ""} de la
                              société <b>{dossier.requerant.raisonSociale}</b>{" "}
                              (SIREN: {dossier.requerant.siren})
                            </li>
                          )}
                        </ul>
                      </div>
                      <div className="fr-grid-column">
                        <h5>Bris de porte</h5>

                        <ul>
                          <li>
                            <b>Survenu à l'adresse: </b>{" "}
                            {dossier.adresse.libelle()}
                          </li>
                          <li>
                            <b>Le :</b>{" "}
                            {dossier.dateOperation ? (
                              <>
                                {dossier.dateOperation?.toLocaleString(
                                  "fr-FR",
                                  {
                                    weekday: "long",
                                    day: "numeric",
                                    month: "long",
                                    year: "numeric",
                                  },
                                )}
                                , à{" "}
                                {dossier.dateOperation?.toLocaleString(
                                  "fr-FR",
                                  {
                                    hour: "numeric",
                                    minute: "numeric",
                                  },
                                )}
                              </>
                            ) : (
                              <i>non renseigné</i>
                            )}
                          </li>
                          <li>
                            <b>Porte blindée: </b>{" "}
                            {dossier.estPorteBlindee !== null ? (
                              <>{dossier.estPorteBlindee ? "oui" : "non"}</>
                            ) : (
                              <i>non renseigné</i>
                            )}
                          </li>
                        </ul>
                      </div>
                    </div>
                  </section>

                  <section className="fr-accordion">
                    <h3 className="fr-accordion__title">
                      <button
                        className="fr-accordion__btn"
                        aria-expanded="false"
                        aria-controls="accordion-115"
                      >
                        Pièces jointes
                      </button>
                    </h3>
                    <div className="fr-collapse" id="accordion-115">
                      <div className="fr-tabs">
                        <ul
                          className="fr-tabs__list"
                          role="tablist"
                          aria-label="Pièces jointes"
                        >
                          {Document.types.map(
                            (type: DocumentType, index: number) => (
                              <li role="presentation" key={type.type}>
                                <button
                                  id={`tab-document-${type.type}`}
                                  className="fr-tabs__tab"
                                  tabIndex={index == 0 ? 0 : -1}
                                  role="tab"
                                  aria-selected={index == 0}
                                  aria-controls={`tab-document-${type.type}-panel`}
                                  disabled={!dossier.documents.has(type.type)}
                                >
                                  {type.libelle}{" "}
                                  {dossier.documents.has(type.type)
                                    ? `(${dossier.documents.get(type.type)?.length})`
                                    : ""}
                                </button>
                              </li>
                            ),
                          )}
                        </ul>
                        {Document.types.map(
                          (type: DocumentType, index: number) => (
                            <div
                              key={`document-tab-${type.type}`}
                              id={`tab-document-${type.type}-panel`}
                              className={`fr-tabs__panel ${index == 0 ? "fr-tabs__panel--selected" : ""}`}
                              role="tabpanel"
                              aria-labelledby={`tab-document-${type.type}`}
                              tabIndex={0}
                            >
                              <h4>{type.libelle}</h4>

                              {dossier.documents.has(type.type) &&
                                dossier.documents
                                  .get(type.type)
                                  .map((document: Document) => (
                                    <div
                                      className="fr-grid-row"
                                      key={document.id}
                                    >
                                      <h6>{document.originalFilename}</h6>
                                      {document.mime == "application/pdf" ? (
                                        <object
                                          data={document.url}
                                          type="application/pdf"
                                          style={{
                                            width: "100%",
                                            aspectRatio: "210/297",
                                          }}
                                        ></object>
                                      ) : (
                                        <img
                                          src={document.url}
                                          style={{
                                            width: "100%",
                                            maxHeight: "100vh",
                                          }}
                                        />
                                      )}
                                    </div>
                                  ))}
                            </div>
                          ),
                        )}
                      </div>
                    </div>
                  </section>

                  <section className="fr-accordion">
                    <h3 className="fr-accordion__title">
                      <button
                        className="fr-accordion__btn"
                        aria-expanded="false"
                        aria-controls="accordion-116"
                        disabled
                      >
                        Lettre de synthèse
                      </button>
                    </h3>
                  </section>
                </div>
              </div>
            </div>
          </div>
        </div>
      </>
    );
  },
);
