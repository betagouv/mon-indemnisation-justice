import { ValidationDossier } from "@/apps/agent/dossiers/components/consultation/ValidationDossier";
import { Agent, Document, DocumentType } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import {
  AttributionDossier,
  DecisionDossier,
} from "@/apps/agent/dossiers/components/consultation";
import { data } from "autoprefixer";

import { observer } from "mobx-react-lite";
import { element } from "prop-types";
import React, { useState } from "react";

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
                        </ul>
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
