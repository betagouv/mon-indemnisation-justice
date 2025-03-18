import { ValidationDossier } from "@/apps/agent/dossiers/components/consultation/ValidationDossier";
import {
  Agent,
  Document,
  DossierDetail,
  DocumentType,
  Courrier,
} from "@/apps/agent/dossiers/models";
import {
  AttributionDossier,
  DecisionDossier,
} from "@/apps/agent/dossiers/components/consultation";
import { plainToInstance } from "class-transformer";

import { observer } from "mobx-react-lite";
import React, { useState } from "react";
import ReactQuill from "react-quill-new";

export const ConsultationDossierApp = observer(
  function ConsultationDossierAppComponent({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
    const [pieceJointe, selectionnerPieceJointe] = useState(
      dossier.getDocumentParIndex(0),
    );

    // Modélise la prise de notes de suivi en cours
    const [notes, setNotes]: [string, (notes: string) => void] = useState(
      dossier.notes,
    );

    // Indique si la sauvegarde des notes de suivi est en cours
    const [sauvegarderEnCours, setSauvegarderEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    const annoterCourrier = async () => {
      setSauvegarderEnCours(true);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/annoter.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            notes,
          }),
        },
      );

      setSauvegarderEnCours(false);
      dossier.annoter(notes);
    };

    return (
      <>
        <div className="fr-container fr-container--fluid fr-mt-2w">
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12 fr-p-3w">
              {/*  Résumé de l'état + boutons */}
              <div
                className={`fr-dossier-etat fr-dossier-etat--${dossier.etat.slug} fr-p-4w`}
              >
                <h3 className="">Dossier {dossier.reference}</h3>

                <div>
                  <p
                    className={`fr-badge fr-badge--no-icon fr-badge--dossier-etat fr-badge--dossier-etat--${dossier.etat.slug} fr-py-1w fr-px-2w`}
                  >
                    {dossier.etat.libelle}
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

                {/* Attribution du rédacteur */}
                <AttributionDossier dossier={dossier} agent={agent} />

                {/* Décision du rédacteur sur le dossier */}
                {dossier.enAttenteDecision &&
                  agent.estRedacteur() &&
                  agent.estAttribue(dossier) && (
                    <DecisionDossier
                      dossier={dossier}
                      onDecide={() => (window.location.hash = "courrier")}
                    />
                  )}

                {/* Validation du validateur sur le dossier */}
                {dossier.enAttenteValidation && agent.estValidateur() && (
                  <ValidationDossier
                    dossier={dossier}
                    onEdite={() => {
                      window.location.hash = "courrier";
                    }}
                    onSigne={() => (window.location.hash = "courrier")}
                  />
                )}
              </div>

              <div className="fr-my-2w">
                <div className="fr-tabs">
                  <ul
                    className="fr-tabs__list"
                    role="tablist"
                    aria-label="Détails du dossier"
                  >
                    <li role="presentation">
                      <a
                        href="#infos"
                        id="tab-infos"
                        className="fr-tabs__tab"
                        tabIndex="0"
                        role="tab"
                        aria-selected={document.location.hash == "#infos"}
                        aria-controls="tab-panel-infos"
                      >
                        Informations du dossier
                      </a>
                    </li>
                    <li role="presentation">
                      <a
                        href="#suivi"
                        id="tab-suivi"
                        className="fr-tabs__tab"
                        tabIndex="-1"
                        role="tab"
                        aria-selected={document.location.hash == "#suivi"}
                        aria-controls="tab-panel-suivi"
                      >
                        Notes de suivi
                      </a>
                    </li>
                    <li role="presentation">
                      <a
                        href="#pieces-jointes"
                        id="tab-pieces-jointes"
                        className="fr-tabs__tab"
                        tabIndex="-1"
                        role="tab"
                        aria-selected={
                          document.location.hash == "#pieces-jointes"
                        }
                        aria-controls="tab-panel-pieces-jointes"
                      >
                        Pièces jointes
                      </a>
                    </li>
                    <li role="presentation">
                      <a
                        type="button"
                        id="tab-courrier"
                        className="fr-tabs__tab"
                        tabIndex="-1"
                        role="tab"
                        {...(null !== dossier.courrier ||
                        dossier.hasDocumentsType(
                          DocumentType.TYPE_COURRIER_MINISTERE,
                        )
                          ? {
                              "aria-controls": "tab-panel-courrier",
                              "aria-selected":
                                document.location.hash == "#courrier",
                            }
                          : { disabled: true })}
                      >
                        Décision et courrier
                      </a>
                    </li>
                  </ul>
                  <div
                    id="tab-panel-infos"
                    className={`fr-tabs__panel ${document.location.hash == "#infos" ? "fr-tabs__panel--selected" : ""}`}
                    role="tabpanel"
                    aria-labelledby="tab-infos"
                    tabIndex="0"
                  >
                    <section>
                      <div className="fr-grid-column">
                        <h3>Informations sur le dossier </h3>
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
                          <li>
                            <b>Né{dossier.requerant.estFeminin() ? "e" : ""}</b>{" "}
                            le{" "}
                            {dossier.requerant.dateNaissance ? (
                              <>
                                {dossier.requerant.dateNaissance.toLocaleString(
                                  "fr-FR",
                                  {
                                    //weekday: 'long',
                                    day: "numeric",
                                    month: "long",
                                    year: "numeric",
                                  },
                                )}
                              </>
                            ) : (
                              <i>non renseigné</i>
                            )}
                            , à{" "}
                            {dossier.requerant.communeNaissance ? (
                              <>
                                {dossier.requerant.communeNaissance}{" "}
                                {dossier.requerant.paysNaissance
                                  ? `(${dossier.requerant.paysNaissance})`
                                  : ""}
                              </>
                            ) : (
                              <i>non renseigné</i>
                            )}
                          </li>
                          {dossier.requerant.estPersonneMorale() && (
                            <li>
                              Représentant
                              {dossier.requerant.estFeminin() ? "e" : ""} légal
                              {dossier.requerant.estFeminin() ? "e" : ""} de la
                              société <b>{dossier.requerant.raisonSociale}</b>{" "}
                              (SIREN: <b>{dossier.requerant.siren}</b>)
                            </li>
                          )}
                        </ul>
                      </div>
                      {
                        // Sous-section "Bris de porte"
                      }
                      <div className="fr-grid-column">
                        <h5>Bris de porte</h5>

                        <ul>
                          <li>
                            <b>Survenu à l'adresse: </b>{" "}
                            {dossier.adresse.estRenseignee() ? (
                              <>{dossier.adresse.libelle()}</>
                            ) : (
                              <i>non renseigné</i>
                            )}
                            {}
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
                          <li>
                            <b>
                              Était visé
                              {dossier.requerant.estFeminin() ? "e" : ""} par
                              l'opération des Forces de l'ordre ?{" "}
                            </b>{" "}
                            <>
                              {dossier.testEligibilite.estVise ? "Oui" : "Non"}
                            </>
                          </li>
                          <li>
                            <b>Hébergeait la personne recherchée ?</b>{" "}
                            <>
                              {dossier.testEligibilite.estHebergeant
                                ? "Oui"
                                : "Non"}
                            </>
                          </li>
                          <li>
                            <b>Situation par rapport au logement ?</b>{" "}
                            <>
                              {dossier.testEligibilite.estProprietaire
                                ? "Propriétaire"
                                : "Locataire"}
                            </>
                          </li>
                          <li>
                            <b>A contacté l'assurance ?</b>{" "}
                            <>
                              {dossier.testEligibilite.aContacteAssurance
                                ? "Oui"
                                : "Non"}
                            </>
                          </li>
                          {null !==
                            dossier.testEligibilite.aContacteBailleur && (
                            <li>
                              <b>A contacté le bailleur ?</b>{" "}
                              <>
                                {dossier.testEligibilite.aContacteBailleur
                                  ? "Oui"
                                  : "Non"}
                              </>
                            </li>
                          )}
                        </ul>
                      </div>
                    </section>
                  </div>

                  <div
                    id="tab-panel-suivi"
                    className={`fr-tabs__panel ${document.location.hash == "#suivi" ? "fr-tabs__panel--selected" : ""}`}
                    role="tabpanel"
                    aria-labelledby="tab-suivi"
                    tabIndex="0"
                  >
                    <section>
                      <h3>Notes de suivi</h3>

                      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--primary"
                            type="button"
                            disabled={
                              sauvegarderEnCours ||
                              !notes.trim() ||
                              dossier.notes == notes
                            }
                            onClick={() => annoterCourrier()}
                          >
                            {sauvegarderEnCours ? (
                              <>Sauvegarde en cours ...</>
                            ) : (
                              <>Enregistrer les changements</>
                            )}
                          </button>
                        </li>
                      </ul>

                      <div className="fr-grid-row fr-col-12">
                        <ReactQuill
                          theme="snow"
                          value={notes}
                          onChange={(value) => setNotes(value)}
                        />
                      </div>
                    </section>
                  </div>

                  <div
                    id="tab-panel-pieces-jointes"
                    className={`fr-tabs__panel ${document.location.hash == "#pieces-jointes" ? "fr-tabs__panel--selected" : ""}`}
                    role="tabpanel"
                    aria-labelledby="tab-pieces-jointes"
                    tabIndex="0"
                  >
                    <section className="mij-dossier-documents">
                      <h3>Pièces jointes</h3>
                      <div className="fr-grid-row">
                        <div className="fr-col-3 mij-dossier-documents-liste">
                          <ul>
                            {Document.types.map((type: DocumentType) => (
                              <React.Fragment key={type.type}>
                                <li
                                  {...(dossier.getDocumentsType(type).length ==
                                  0
                                    ? { "data-section-vide": "" }
                                    : {})}
                                  {...(pieceJointe?.type === type.type
                                    ? { "data-section-active": true }
                                    : {})}
                                >
                                  {type.libelle}{" "}
                                  {dossier.getDocumentsType(type).length > 0
                                    ? `(${dossier.getDocumentsType(type).length})`
                                    : ""}
                                </li>

                                {dossier.getDocumentsType(type).length > 0 && (
                                  <ul>
                                    {dossier
                                      .getDocumentsType(type)
                                      .map((doc: Document) => (
                                        <li
                                          key={doc.id}
                                          {...(pieceJointe == doc
                                            ? {
                                                "data-document-selectionne":
                                                  true,
                                              }
                                            : {})}
                                        >
                                          <a
                                            href={void 0}
                                            onClick={() =>
                                              selectionnerPieceJointe(doc)
                                            }
                                          >
                                            {doc.originalFilename}
                                          </a>
                                        </li>
                                      ))}
                                  </ul>
                                )}
                              </React.Fragment>
                            ))}
                          </ul>
                        </div>
                        <div className="fr-col-9 fr-px-4w">
                          {pieceJointe ? (
                            <div className="fr-grid-row">
                              <h6>{pieceJointe.originalFilename}</h6>
                              {pieceJointe.mime == "application/pdf" ? (
                                <object
                                  data={pieceJointe.url}
                                  type="application/pdf"
                                  style={{
                                    width: "100%",
                                    aspectRatio: "210/297",
                                  }}
                                ></object>
                              ) : (
                                <img
                                  src={pieceJointe.url}
                                  alt={pieceJointe.originalFilename}
                                  style={{
                                    width: "100%",
                                    maxHeight: "100vh",
                                  }}
                                />
                              )}
                            </div>
                          ) : (
                            <i>Aucune pièce jointe sélectionnée</i>
                          )}
                        </div>
                      </div>
                    </section>
                  </div>

                  {(null !== dossier.courrier ||
                    dossier.hasDocumentsType(
                      DocumentType.TYPE_COURRIER_MINISTERE,
                    )) && (
                    <div
                      id="tab-panel-courrier"
                      className={`fr-tabs__panel ${document.location.hash == "#courrier" ? "fr-tabs__panel--selected" : ""}`}
                      role="tabpanel"
                      aria-labelledby="tab-courrier"
                      tabIndex="0"
                    >
                      <section>
                        <h3>Courrier</h3>
                        <div className="fr-grid-row">
                          <object
                            data={
                              dossier.documents
                                .get(DocumentType.TYPE_COURRIER_MINISTERE.type)
                                ?.at(0)?.url ?? dossier.courrier.url
                            }
                            type="application/pdf"
                            style={{
                              width: "100%",
                              aspectRatio: "210/297",
                            }}
                          ></object>
                        </div>
                      </section>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </>
    );
  },
);
