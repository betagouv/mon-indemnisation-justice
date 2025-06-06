import {
  Agent,
  Document,
  DossierDetail,
  DocumentType,
} from "@/apps/agent/dossiers/models";
import {
  AttributionDossier,
  ClotureDossier,
  DecisionDossier,
  ValidationDecisionDossier,
  ValidationAcceptationDossier,
} from "@/apps/agent/dossiers/components/consultation";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { plainToInstance } from "class-transformer";

import { observer } from "mobx-react-lite";
import React, { useRef, useLayoutEffect, useState } from "react";
import ReactQuill from "react-quill-new";

export const ConsultationDossierApp = observer(
  function ConsultationDossierAppComponent({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
    // Référence vers la modale d'ajout de pièce jointe
    const refModalePJ = useRef(null);

    // Type de document associé à la pièce jointe téléversée
    const [typePJ, setTypePj]: [DocumentType, (typePJ?: DocumentType) => void] =
      useState(null);

    // Pièce jointe à téléverser
    const [nouvellePieceJointe, setNouvellePieceJointe]: [
      File | null,
      (nouvellePieceJointe: File) => void,
    ] = useState(null);

    const ouvrirModalePieceJointe = () =>
      refModalePJ.current?.classList.add("fr-modal--opened");
    const fermerModalePieceJointe = () =>
      refModalePJ.current?.classList.remove("fr-modal--opened");

    const ajouterPieceJointe = async () => {
      setSauvegarderEnCours(true);

      const payload = new FormData();
      payload.append("file", nouvellePieceJointe);
      payload.append("type", typePJ.type);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/piece-jointe/ajouter.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: payload,
        },
      );

      if (response.ok) {
        const data = await response.json();
        const document = plainToInstance(Document, data);
        dossier.addDocument(document);
        selectionnerPieceJointe(document);
        fermerModalePieceJointe();
        setTypePj(null);
        setNouvellePieceJointe(null);
      }

      setSauvegarderEnCours(false);
      dossier.annoter(notes);
    };

    // Pièce jointe en cours de visualisation
    const [pieceJointe, selectionnerPieceJointe] = useState(
      dossier.documents
        .values()
        ?.find((documents) => documents.length > 0)
        ?.at(0) ?? null,
    );

    // Modélise la prise de notes de suivi en cours
    const [notes, setNotes]: [string, (notes?: string) => void] = useState(
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

    const refSectionCourrier = useRef(null);
    const [largeurSectionCourrier, setLargeurSectionCourrier] = useState(0);

    useLayoutEffect(() => {
      if (refSectionCourrier.current) {
        setLargeurSectionCourrier(refSectionCourrier.current.offsetWidth);
      }
    }, []);

    return (
      <>
        <div className="fr-container fr-container--fluid fr-mt-2w">
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12 fr-p-3w">
              {/*  Résumé de l'état + boutons */}
              <div
                className={`fr-dossier-etat fr-dossier-etat--${dossier.etat.etat.slug} fr-p-4w`}
              >
                <h3 className="">Dossier {dossier.reference}</h3>

                <div>
                  <p
                    className={`fr-badge fr-badge--no-icon fr-badge--dossier-etat fr-badge--dossier-etat--${dossier.etat.etat.slug} fr-py-1w fr-px-2w`}
                    {...(dossier.etat.estCloture()
                      ? {
                          "aria-describedby": `tooltip-etat-dossier-${dossier.id}`,
                        }
                      : {})}
                  >
                    {dossier.etat.etat.libelle}
                  </p>
                </div>
                {dossier.etat.estCloture() && (
                  <span
                    className="fr-tooltip fr-placement"
                    id={`tooltip-etat-dossier-${dossier.id}`}
                    role="tooltip"
                  >
                    {dossier.etat.contexte?.motif || <i>Aucun motif</i>}
                  </span>
                )}

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

                {/* Clôture du dossier */}
                {dossier.estCloturable() &&
                  (agent.estAttributeur() || agent.instruit(dossier)) && (
                    <ClotureDossier dossier={dossier} agent={agent} />
                  )}

                {/* Décision du rédacteur sur le dossier */}
                {dossier.enAttenteDecision &&
                  agent.estRedacteur() &&
                  agent.instruit(dossier) && (
                    <DecisionDossier
                      dossier={dossier}
                      onDecide={() => {
                        if (refSectionCourrier.current) {
                          setLargeurSectionCourrier(
                            refSectionCourrier.current.offsetWidth,
                          );
                        }
                        history.replaceState(undefined, undefined, "#courrier");
                      }}
                    />
                  )}

                {/* Validation du validateur sur le dossier */}
                {dossier.enAttenteValidation && agent.estValidateur() && (
                  <ValidationDecisionDossier
                    dossier={dossier}
                    onEdite={() => {
                      if (refSectionCourrier.current) {
                        setLargeurSectionCourrier(
                          refSectionCourrier.current.offsetWidth,
                        );
                      }
                      history.replaceState(undefined, undefined, "#courrier");
                    }}
                    onSigne={() => {
                      if (refSectionCourrier.current) {
                        setLargeurSectionCourrier(
                          refSectionCourrier.current.offsetWidth,
                        );
                      }
                      history.replaceState(undefined, undefined, "#courrier");
                    }}
                  />
                )}

                {/* Le rédacteur vérifie la déclaration d'acceptation et la valide */}
                {dossier.estAVerifier && agent.instruit(dossier) && (
                  <ValidationAcceptationDossier dossier={dossier} />
                )}

                {/* L'agent validateur génère et signe l'arrêté de paiement */}
                {dossier.enAttentePaiement && agent.estValidateur() && <></>}

                {/* L'agent validateur génère et signe l'arrêté de paiement */}
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
                        id="tab-infos"
                        className="fr-tabs__tab"
                        tabIndex={0}
                        role="tab"
                        aria-selected={window.location.hash == "#infos"}
                        aria-controls="tab-panel-infos"
                        onClick={() => history.replaceState({}, "", "#infos")}
                      >
                        Informations du dossier
                      </a>
                    </li>
                    <li role="presentation">
                      <a
                        id="tab-suivi"
                        className="fr-tabs__tab"
                        tabIndex={-1}
                        role="tab"
                        aria-selected={window.location.hash == "#suivi"}
                        aria-controls="tab-panel-suivi"
                        onClick={() => history.replaceState({}, "", "#suivi")}
                      >
                        Notes de suivi
                      </a>
                    </li>
                    <li role="presentation">
                      <a
                        id="tab-pieces-jointes"
                        className="fr-tabs__tab"
                        tabIndex={-1}
                        role="tab"
                        aria-selected={
                          window.location.hash == "#pieces-jointes"
                        }
                        aria-controls="tab-panel-pieces-jointes"
                        onClick={() =>
                          history.replaceState({}, "", "#pieces-jointes")
                        }
                      >
                        Pièces jointes
                      </a>
                    </li>
                    <li role="presentation">
                      <a
                        type="button"
                        id="tab-courrier"
                        className="fr-tabs__tab"
                        tabIndex={-1}
                        role="tab"
                        {...(null !== dossier.courrier ||
                        dossier.hasDocumentsType(
                          DocumentType.TYPE_COURRIER_MINISTERE,
                        )
                          ? {
                              "aria-controls": "tab-panel-courrier",
                              "aria-selected":
                                window.location.hash == "#courrier",
                            }
                          : { disabled: true })}
                        onClick={() =>
                          history.replaceState({}, "", "#courrier")
                        }
                      >
                        Décision et courrier
                      </a>
                    </li>
                  </ul>
                  <div
                    id="tab-panel-infos"
                    className={`fr-tabs__panel ${window.location.hash == "#infos" ? "fr-tabs__panel--selected" : ""}`}
                    role="tabpanel"
                    aria-labelledby="tab-infos"
                    tabIndex={0}
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
                          <li>
                            <b>Adresse courriel: </b>{" "}
                            {dossier.enAttenteInstruction() ? (
                              <>
                                <span aria-describedby="tooltip-requerant-courriel">{`${dossier.requerant.courriel.substring(0, 2)}${"*".repeat(dossier.requerant.courriel.length - 2)}`}</span>
                                <span
                                  className="fr-tooltip fr-placement"
                                  id="tooltip-requerant-courriel"
                                  role="tooltip"
                                  aria-hidden="true"
                                >
                                  Démarrer l'instruction pour révéler
                                </span>
                              </>
                            ) : (
                              <span>{dossier.requerant.courriel}</span>
                            )}
                          </li>
                          <li>
                            <b>N° téléphone: </b>
                            {dossier.requerant.telephone ? (
                              dossier.enAttenteInstruction() ? (
                                <>
                                  <span aria-describedby="tooltip-requerant-telephone">{`${dossier.requerant.telephone.substring(0, 2)}${"*".repeat(dossier.requerant.telephone.length - 2)}`}</span>
                                  <span
                                    className="fr-tooltip fr-placement"
                                    id="tooltip-requerant-telephone"
                                    role="tooltip"
                                    aria-hidden="true"
                                  >
                                    Démarrer l'instruction pour révéler
                                  </span>
                                </>
                              ) : (
                                <>{dossier.requerant.telephone}</>
                              )
                            ) : (
                              <i>non renseigné</i>
                            )}
                          </li>
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
                    className={`fr-tabs__panel ${window.location.hash == "#suivi" ? "fr-tabs__panel--selected" : ""}`}
                    role="tabpanel"
                    aria-labelledby="tab-suivi"
                    tabIndex={0}
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
                              !notes?.trim() ||
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
                          readOnly={dossier.enAttenteInstruction()}
                          value={notes}
                          onChange={(value) => setNotes(value)}
                        />
                      </div>
                    </section>
                  </div>

                  <div
                    id="tab-panel-pieces-jointes"
                    className={`fr-tabs__panel ${window.location.hash == "#pieces-jointes" ? "fr-tabs__panel--selected" : ""}`}
                    role="tabpanel"
                    aria-labelledby="tab-pieces-jointes"
                    tabIndex={0}
                  >
                    <section className="mij-dossier-documents">
                      <h3>Pièces jointes</h3>
                      <div className="fr-grid-row">
                        <div className="fr-col-3 mij-dossier-documents-liste">
                          {/* Ajout d'une pièce jointe */}
                          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline">
                            <li>
                              <button
                                className="fr-btn fr-btn--sm fr-btn--primary"
                                type="button"
                                disabled={
                                  !(
                                    agent.estAttributeur() ||
                                    agent.estValidateur() ||
                                    agent.instruit(dossier)
                                  ) ||
                                  !(
                                    dossier.enAttenteDecision ||
                                    dossier.enAttenteValidation
                                  )
                                }
                                onClick={() => ouvrirModalePieceJointe()}
                              >
                                Ajouter une pièce jointe
                              </button>
                            </li>
                          </ul>

                          <dialog
                            ref={refModalePJ}
                            className="fr-modal"
                            aria-labelledby="modale-ajout-pj-titre"
                            data-fr-concealing-backdrop="true"
                          >
                            <div className="fr-container fr-container--fluid fr-container-md">
                              <div className="fr-grid-row fr-grid-row--center">
                                <div className="fr-col-8">
                                  <div className="fr-modal__body">
                                    <div className="fr-modal__header">
                                      <button
                                        title="Fermer"
                                        type="button"
                                        className="fr-btn--close fr-btn"
                                        onClick={() =>
                                          fermerModalePieceJointe()
                                        }
                                      >
                                        Fermer
                                      </button>
                                    </div>
                                    <div className="fr-modal__content">
                                      <h1
                                        id="modale-ajout-pj-titre"
                                        className="fr-modal__title"
                                      >
                                        <span
                                          className="fr-icon-upload-2-line fr-mx-1w"
                                          aria-hidden="true"
                                        ></span>{" "}
                                        Ajouter une pièce jointe au dossier
                                      </h1>

                                      <div className="fr-select-group">
                                        <label
                                          className="fr-label"
                                          htmlFor="storybook-select-171"
                                        >
                                          {" "}
                                          Type de pièce jointe{" "}
                                        </label>
                                        <select
                                          className="fr-select"
                                          aria-describedby="storybook-select-171-messages"
                                          id="storybook-select-171"
                                          name="storybook-select-171"
                                          defaultValue={""}
                                          onChange={(e) =>
                                            setTypePj(
                                              Document.types.find(
                                                (type: DocumentType) =>
                                                  type.type == e.target.value,
                                              ),
                                            )
                                          }
                                        >
                                          <option value="" disabled hidden>
                                            Sélectionnez un type
                                          </option>
                                          {Document.types.map(
                                            (type: DocumentType) => (
                                              <option
                                                value={type.type}
                                                key={type.type}
                                              >
                                                {type.libelle}
                                              </option>
                                            ),
                                          )}
                                        </select>
                                        <div
                                          className="fr-messages-group"
                                          id="storybook-select-171-messages"
                                          aria-live="polite"
                                        ></div>
                                      </div>

                                      {typePJ && (
                                        <div className="fr-upload-group">
                                          <label
                                            className="fr-label"
                                            htmlFor="file-upload"
                                          >
                                            Document à joindre
                                            <span className="fr-hint-text">
                                              Taille maximale : 10
                                              Mo.&nbsp;Format pdf
                                              uniquement.&nbsp;
                                            </span>
                                          </label>{" "}
                                          <input
                                            className="fr-upload"
                                            type="file"
                                            id="file-upload"
                                            name="file-upload"
                                            accept="application/pdf,image/*"
                                            onChange={(e) =>
                                              setNouvellePieceJointe(
                                                e.target.files[0],
                                              )
                                            }
                                          />
                                        </div>
                                      )}

                                      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                                        <li>
                                          <button
                                            className="fr-btn fr-btn--sm fr-btn--primary"
                                            type="button"
                                            disabled={
                                              sauvegarderEnCours ||
                                              !typePJ ||
                                              !nouvellePieceJointe
                                            }
                                            onClick={() => ajouterPieceJointe()}
                                          >
                                            Ajouter
                                          </button>
                                        </li>
                                      </ul>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </dialog>

                          {/* Menu latéral des pièces jointes */}
                          <ul>
                            {Document.types.map((type: DocumentType) => (
                              <React.Fragment key={type.type}>
                                <li
                                  {...(dossier.getDocumentsType(type).length ==
                                  0
                                    ? { "data-section-vide": "" }
                                    : {})}
                                  {...(pieceJointe?.type === type
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
                        {/* Affichage de la pièce jointe sélectionnée */}
                        <div className="fr-col-9 fr-px-4w">
                          {pieceJointe ? (
                            <>
                              <ButtonsGroup
                                inlineLayoutWhen="always"
                                alignment="right"
                                buttonsIconPosition="right"
                                buttonsSize="small"
                                buttons={[
                                  {
                                    children: "Télécharger",
                                    iconId: "fr-icon-download-line",
                                    linkProps: {
                                      href: pieceJointe.url,
                                      download: true,
                                    },
                                  } as ButtonProps,
                                ]}
                              />

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
                                      objectFit: "contain",
                                    }}
                                  />
                                )}
                              </div>
                            </>
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
                    ) ||
                    dossier.hasDocumentsType(
                      DocumentType.TYPE_COURRIER_REQUERANT,
                    )) && (
                    <div
                      id="tab-panel-courrier"
                      className={`fr-tabs__panel ${window.location.hash == "#courrier" ? "fr-tabs__panel--selected" : ""}`}
                      role="tabpanel"
                      aria-labelledby="tab-courrier"
                      tabIndex={0}
                    >
                      <section>
                        <h3>Courrier</h3>
                        <div className="fr-grid-row" ref={refSectionCourrier}>
                          <object
                            data={
                              dossier.documents
                                .get(DocumentType.TYPE_COURRIER_REQUERANT.type)
                                ?.at(0)?.url ??
                              dossier.documents
                                .get(DocumentType.TYPE_COURRIER_MINISTERE.type)
                                ?.at(0)?.url ??
                              dossier.courrier.url
                            }
                            type="application/pdf"
                            width={largeurSectionCourrier || "100%"}
                            height={
                              largeurSectionCourrier
                                ? Math.floor(
                                    (297 * largeurSectionCourrier) / 210,
                                  )
                                : undefined
                            }
                            style={{
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
