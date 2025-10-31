import { DossierActions } from "@/apps/agent/dossiers/components/consultation/action";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe/PieceJointe";
import { Agent, Document, DocumentType, DossierDetail } from "@/common/models";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { observer } from "mobx-react-lite";
import React, { type ReactNode, useMemo, useState } from "react";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import {
  AjoutPieceJointe,
  TelechargerPieceJointe,
} from "@/apps/agent/dossiers/components/consultation/piecejointe";
import {
  ouvrirModaleSuppressionPieceJointe,
  SuppressionPieceJointe,
} from "@/apps/agent/dossiers/components/consultation/piecejointe/SuppressionPieceJointe.tsx";
import { Button } from "@codegouvfr/react-dsfr/Button";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { dateSimple } from "@/common/services/date.ts";

export const ConsultationDossierApp = observer(
  function ConsultationDossierAppComponent({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
    // Référence vers l'onglet ouvert
    const [selectedTab, selectTab] = useState(
      window.location.hash?.replace(/^#/, "") || "infos",
    );

    const changerOnglet = (tab) => {
      selectTab(tab);
      history.replaceState({}, "", `#${tab}`);
    };

    // Pièce jointe en cours de visualisation
    const [pieceJointe, selectionnerPieceJointe] = useState<Document | null>(
      dossier.piecesJointes?.at(0) ?? null,
    );

    const pieceJointeSuivante = () =>
      selectionnerPieceJointe(
        pieceJointe
          ? (dossier.piecesJointes.at(
              (dossier.piecesJointes.indexOf(pieceJointe) + 1) %
                dossier.piecesJointes.length,
            ) as Document)
          : null,
      );

    // Modélise la prise de notes de suivi en cours
    const [notes, setNotes]: [string, (notes: string) => void] =
      useState<string>(dossier.notes ?? "");

    const courrier = useMemo<Document | null>(
      () => dossier.getCourrierDecision(),
      [dossier.getCourrierDecision()?.fileHash],
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

      if (!response.ok) {
        const message = await response.text();
        console.error(`${response.status} ${response.statusText} - ${message}`);
      }

      setSauvegarderEnCours(false);
      dossier.annoter(notes);
    };

    const ouvrirSectionCourrier = () => {
      changerOnglet("courrier");
    };

    // @ts-ignore
    // @ts-ignore
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

                <div
                  className="fr-grid-row fr-grid-row--gutters fr-my-1w"
                  style={{ gap: ".5vw" }}
                >
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
                  {dossier.declarationFDO && (
                    <Badge severity="new" small={false}>
                      Déclaration FDO
                    </Badge>
                  )}
                </div>
                {dossier.etat.estCloture() && (
                  <span
                    className="fr-tooltip fr-placement"
                    id={`tooltip-etat-dossier-${dossier.id}`}
                    role="tooltip"
                  >
                    {dossier.etat.contexte?.motifRejet || <i>Aucun motif</i>}
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

                <p className="fr-m-1w">
                  Ce dossier
                  {dossier.redacteur ? (
                    agent.equals(dossier.redacteur) ? (
                      <>
                        {" "}
                        <b>vous</b> est attribué.
                      </>
                    ) : (
                      <>
                        {" "}
                        est attribué à <u> {dossier.redacteur.nom} </u>.
                      </>
                    )
                  ) : (
                    <>
                      {" "}
                      n'est <i>pas encore attribué</i> à un rédacteur.
                    </>
                  )}
                </p>

                {/** Actions sur le dossier */}
                <DossierActions
                  dossier={dossier}
                  agent={agent}
                  onDecide={() => ouvrirSectionCourrier()}
                  onEdite={() => ouvrirSectionCourrier()}
                  onSigne={() => ouvrirSectionCourrier()}
                />

                {/* L'agent validateur génère et signe l'arrêté de paiement */}
                {dossier.enAttentePaiement && agent.estValidateur() && <></>}
              </div>

              <div className="fr-my-2w">
                <Tabs
                  selectedTabId={selectedTab}
                  tabs={[
                    ...[
                      {
                        tabId: "infos",
                        label: "Informations du dossier",
                        isDefault: true,
                      },
                      {
                        tabId: "suivi",
                        label: "Notes de suivi",
                        iconId: "fr-icon-ball-pen-line",
                      },
                      { tabId: "pieces-jointes", label: "Pièces jointes" },
                      {
                        tabId: "courrier",
                        label: "Courrier",
                        disabled: !dossier.estDecide(),
                      },
                    ],
                    // @ts-ignore
                    ...(dossier.estAccepte()
                      ? [
                          {
                            tabId: "declaration",
                            label: "Déclaration d'acceptation",
                            disabled: !dossier.getDeclarationAcceptation(),
                          },
                          {
                            tabId: "arrete",
                            label: "Arrêté de paiement",
                            disabled: !dossier.getArretePaiement(),
                          },
                        ]
                      : []),
                  ]}
                  onTabChange={changerOnglet}
                >
                  {selectedTab == "infos" && (
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
                            {dossier.enAttenteInstruction() &&
                            agent.estRedacteur() &&
                            !agent.estValidateur() &&
                            !agent.estBetagouv() ? (
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
                              <>
                                <span>{dossier.requerant.courriel}</span>
                                <Button
                                  iconId="fr-icon-clipboard-line"
                                  onClick={() =>
                                    navigator.clipboard.writeText(
                                      dossier.requerant.courriel,
                                    )
                                  }
                                  priority="tertiary no outline"
                                  title="Copier dans le presse papier"
                                  size="small"
                                />
                              </>
                            )}
                          </li>
                          <li>
                            <b>N° téléphone: </b>
                            {dossier.requerant.telephone ? (
                              <>
                                {dossier.enAttenteInstruction() &&
                                agent.estRedacteur() &&
                                !agent.estValidateur() &&
                                !agent.estBetagouv() ? (
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
                                  <>
                                    {dossier.requerant.telephone}
                                    <Button
                                      style={{ display: "inline-block" }}
                                      iconId="fr-icon-clipboard-line"
                                      onClick={() =>
                                        navigator.clipboard.writeText(
                                          dossier.requerant.telephone ?? "",
                                        )
                                      }
                                      priority="tertiary no outline"
                                      title="Copier dans le presse papier"
                                      size="small"
                                    />
                                  </>
                                )}
                              </>
                            ) : (
                              <i>non renseigné</i>
                            )}
                          </li>
                        </ul>
                      </div>
                      {
                        // Sous-section "Bris de porte"
                      }
                      <div className="fr-grid-row">
                        <div className="fr-col-6">
                          <h5>Bris de porte</h5>

                          <ul>
                            <li>
                              <b>Survenu à l'adresse: </b>{" "}
                              {dossier.adresse.estRenseignee() ? (
                                <>{dossier.adresse.libelle()}</>
                              ) : (
                                <i>non renseigné</i>
                              )}
                            </li>
                            <li>
                              <b>Le :</b>{" "}
                              {dossier.dateOperation ? (
                                <>
                                  {dateSimple(dossier.dateOperation)}
                                </>
                              ) : (
                                <i>non renseigné</i>
                              )}
                            </li>
                            <li>
                              <b>Porte blindée: </b>{" "}
                              {dossier.estPorteBlindee !== null ? (
                                <>{dossier.estPorteBlindee ? "Oui" : "Non"}</>
                              ) : (
                                <i>non renseigné</i>
                              )}
                            </li>
                            {!!dossier.testEligibilite && (
                              <>
                                <li>
                                  <b>
                                    Était visé
                                    {dossier.requerant.estFeminin()
                                      ? "e"
                                      : ""}{" "}
                                    par l'opération des Forces de l'ordre ?{" "}
                                  </b>{" "}
                                  <>
                                    {dossier.testEligibilite.estVise
                                      ? "Oui"
                                      : "Non"}
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
                                  <b>Situation par rapport au logement ? </b>
                                  {dossier.testEligibilite.rapportAuLogement}
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
                              </>
                            )}
                            {dossier.descriptionRequerant && (
                              <li>
                                <b>Commentaires du requérant:</b>
                                <blockquote
                                  dangerouslySetInnerHTML={{
                                    __html:
                                      dossier.descriptionRequerant.replaceAll(
                                        /\n/g,
                                        "</br>",
                                      ),
                                  }}
                                ></blockquote>
                              </li>
                            )}
                          </ul>
                        </div>

                        {dossier.declarationFDO && (
                          <div className="fr-col-6">
                            <h5>Déclaration des FDO</h5>

                            <ul>
                              <li>
                                <b>Survenu à l'adresse: </b>{" "}
                                {dossier.declarationFDO.adresse.libelle()}
                              </li>
                              <li>
                                <b>Le :</b>{" "}
                                {dateSimple(
                                  dossier.declarationFDO.dateOperation,
                                )}
                              </li>
                              <li>
                                <b>Agent rapporteur</b>{" "}
                                {dossier.declarationFDO.agent?.nomComplet()}
                                <ul>
                                  <li>
                                    {dossier.declarationFDO.agent?.courriel}
                                  </li>
                                  <li>
                                    {dossier.declarationFDO.agent?.telephone}
                                  </li>
                                </ul>
                              </li>
                              <li>
                                <b>Service enquêteur</b>{" "}
                                {
                                  dossier.declarationFDO.procedure
                                    .serviceEnqueteur
                                }
                              </li>
                              <li>
                                <b>Numéro de procédure</b>{" "}
                                {
                                  dossier.declarationFDO.procedure
                                    .numeroProcedure
                                }
                              </li>
                              <li>
                                <b>Juridiction ou parquet</b>{" "}
                                {
                                  dossier.declarationFDO.procedure
                                    .juridictionOuParquet
                                }
                              </li>
                              <li>
                                <b>Nom du magistrat</b>{" "}
                                {dossier.declarationFDO.procedure.nomMagistrat}
                              </li>
                              <li>
                                <b>Commentaires du requérant:</b>
                                <blockquote
                                  dangerouslySetInnerHTML={{
                                    __html:
                                      dossier.declarationFDO.commentaire.replaceAll(
                                        /\n/g,
                                        "</br>",
                                      ),
                                  }}
                                />
                              </li>
                            </ul>
                          </div>
                        )}
                      </div>
                    </section>
                  )}

                  {selectedTab == "suivi" && (
                    <section>
                      <h3>Notes de suivi</h3>

                      <ButtonsGroup
                        className="fr-mt-3w"
                        inlineLayoutWhen="always"
                        alignment="right"
                        buttonsIconPosition="right"
                        buttonsSize="small"
                        buttons={[
                          {
                            disabled:
                              sauvegarderEnCours ||
                              !notes?.trim() ||
                              dossier.notes == notes,
                            onClick: () => annoterCourrier(),
                            children: sauvegarderEnCours
                              ? "Sauvegarde en cours ..."
                              : dossier.notes == notes
                                ? "Enregistré"
                                : "Enregistrer les changements",
                            iconId: "fr-icon-save-line",
                            priority: "primary",
                          },
                        ]}
                      />

                      <div className="fr-grid-row fr-col-12">
                        <QuillEditor
                          readOnly={dossier.enAttenteInstruction()}
                          value={notes}
                          onChange={(value) => setNotes(value)}
                        />
                      </div>
                    </section>
                  )}

                  {selectedTab == "pieces-jointes" && (
                    <section className="mij-dossier-documents">
                      <h3>Pièces jointes</h3>
                      <div className="fr-grid-row">
                        <div className="fr-col-3 mij-dossier-documents-liste">
                          {/* Ajout d'une pièce jointe */}
                          <AjoutPieceJointe
                            dossier={dossier}
                            agent={agent}
                            onAjoute={(nouvellePieceJointe: Document) =>
                              selectionnerPieceJointe(nouvellePieceJointe)
                            }
                          />

                          {/* Menu latéral des pièces jointes */}
                          <ul>
                            {Document.types
                              .filter((t) => t.estPieceJointe())
                              .map((type: DocumentType) => (
                                <React.Fragment key={type.type}>
                                  <li
                                    {...(dossier.getDocumentsType(type)
                                      .length == 0
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

                                  {dossier.getDocumentsType(type).length >
                                    0 && (
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
                              <div className="fr-grid-row fr-col-12">
                                <h4>{pieceJointe.originalFilename}</h4>

                                <TelechargerPieceJointe
                                  className="fr-grid-row fr-col-12"
                                  pieceJointe={pieceJointe}
                                />

                                {pieceJointe.estEditable(dossier, agent) && (
                                  <>
                                    <SuppressionPieceJointe
                                      pieceJointe={pieceJointe}
                                      dossier={dossier}
                                      onSupprime={() =>
                                        selectionnerPieceJointe(
                                          dossier.documents
                                            .values()
                                            ?.find(
                                              (documents) =>
                                                documents.length > 0,
                                            )
                                            ?.at(0) ?? null,
                                        )
                                      }
                                    />
                                    <ButtonsGroup
                                      className="fr-grid-row fr-col-12"
                                      inlineLayoutWhen="always"
                                      alignment="right"
                                      buttonsIconPosition="right"
                                      buttonsSize="small"
                                      buttons={[
                                        {
                                          children: "Supprimer",
                                          iconId: "fr-icon-delete-bin-line",
                                          priority: "secondary",
                                          onClick: () =>
                                            ouvrirModaleSuppressionPieceJointe(),
                                        },
                                        /*
                                                                            TODO: réactiver quand la modale d'ajout sera étendue pour gérer l'édition
                                                                            {
                                                                              children: "Éditer",
                                                                              iconId: "fr-icon-edit-line",
                                                                              priority: "secondary",
                                                                            },
                                                                            */
                                      ]}
                                    />
                                  </>
                                )}

                                <PieceJointe
                                  pieceJointe={pieceJointe}
                                  className="fr-col-12"
                                />
                              </div>
                            </>
                          ) : (
                            <i>Aucune pièce jointe sélectionnée</i>
                          )}
                        </div>
                      </div>
                    </section>
                  )}

                  {selectedTab == "courrier" && (
                    <section>
                      <h3>Courrier</h3>

                      {courrier ? (
                        <>
                          <TelechargerPieceJointe
                            className="fr-grid-row fr-col-12"
                            pieceJointe={courrier}
                          />

                          <PieceJointe
                            className="fr-col-12"
                            pieceJointe={courrier}
                          />
                        </>
                      ) : (
                        <p>Pas encore de courrier</p>
                      )}
                    </section>
                  )}

                  {selectedTab == "declaration" && (
                    <section>
                      <h3>Déclaration d'acceptation</h3>
                      <TelechargerPieceJointe
                        className="fr-grid-row fr-col-12"
                        pieceJointe={
                          dossier.getDeclarationAcceptation() as Document
                        }
                      />

                      <PieceJointe
                        className="fr-col-12"
                        pieceJointe={
                          dossier.getDeclarationAcceptation() as Document
                        }
                      />
                    </section>
                  )}

                  {selectedTab == "arrete" && (
                    <section>
                      <h3>Arrêté de paiement</h3>
                      <TelechargerPieceJointe
                        className="fr-grid-row fr-col-12"
                        pieceJointe={dossier.getArretePaiement() as Document}
                      />

                      <PieceJointe
                        className="fr-col-12"
                        pieceJointe={dossier.getArretePaiement() as Document}
                      />
                    </section>
                  )}
                </Tabs>
              </div>
            </div>
          </div>
        </div>
      </>
    );
  },
);
