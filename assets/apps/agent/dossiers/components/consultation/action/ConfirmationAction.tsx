import {
  EditeurDocument,
  EditeurMode,
} from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Download from "@codegouvfr/react-dsfr/Download";
import React, { FormEvent, useState } from "react";

import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/apps/agent/dossiers/models";
import { observer } from "mobx-react-lite";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { makeAutoObservable } from "mobx";
import { plainToInstance } from "class-transformer";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { TelechargerPieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe/TelechargerPieceJointe.tsx";

const _modale = createModal({
  id: "modale-action-confirmation",
  isOpenedByDefault: false,
});

class EtatConfirmation {
  decision?: boolean;

  constructor() {
    makeAutoObservable(this);
  }

  setDecision(decision: boolean) {
    this.decision = decision;
    _modale.open();
  }
}

const etatConfirmation = new EtatConfirmation();

const estEnAttenteConfirmation = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) => dossier.enAttenteValidation && agent.estValidateur();

export const ConfirmerModale = observer(function ConfirmerActionModale({
  dossier,
  agent,
  etat = etatConfirmation,
  onEdite = null,
  onSigne = null,
}: {
  dossier: DossierDetail;
  agent: Agent;
  etat?: EtatConfirmation;
  onEdite?: () => void;
  onSigne?: () => void;
}) {
  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState(dossier.montantIndemnisation);

  // Mémorise le montant de l'indemnisation lu dans le courrier
  const [montantIndemnisationLu, setMontantIndemnisationLu]: [
    number | null,
    (montant: number | null) => void,
  ] = useState(null);

  // Corps du courrier
  const [corpsCourrier, setCorpsCourrier]: [
    string,
    (corpsCourrier: string) => void,
  ] = useState(
    dossier.getDocumentType(DocumentType.TYPE_COURRIER_MINISTERE)?.corps,
  );

  // Fichier signé à téléverser
  const [fichierSigne, setFichierSigne]: [
    File | null,
    (fichierSigne: File) => void,
  ] = useState(null);

  // Fichier signé à téléverser
  const [estFichierSigne, marquerFichierSigne]: [
    boolean,
    (estFichierSigne: boolean) => void,
  ] = useState(false);

  const estTailleFichierOk = (fichier?: File) =>
    fichier && fichier.size < 10 * 1024 * 1024;
  const estTypeFichierOk = (fichier?: File) =>
    fichier && ["application/pdf"].includes(fichier.type);

  // Indique si la sauvegarde de la décision est en cours
  const [sauvegardeEnCours, setSauvegardeEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Le mode en cours sur l'éditeur de document
  const [editeurMode, setEditeurMode] = useState<EditeurMode>("edition");

  // Actions

  const detecterMontantIndemnisation = (texte: string) => {
    const montantBrut = texte
      .match(new RegExp("(\\s?\\d)+(,[0-9]{1,2})\\s*EUR", "g"))
      ?.at(0);

    if (montantBrut) {
      setMontantIndemnisationLu(
        parseFloat(
          montantBrut
            .replace(" ", "")
            .replace("EUR", "")
            .trim()
            .replace(",", "."),
        ),
      );
    }
  };

  const signerCourrier = async (fichier: File) => {
    setSauvegardeEnCours(true);

    try {
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/signer-courrier.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: (() => {
            const data = new FormData();
            data.append("fichierSigne", fichier);

            return data;
          })(),
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
      }
    } catch (e) {
      console.error(e);
    } finally {
      _modale.close();
      setSauvegardeEnCours(false);
      marquerFichierSigne(true);
      etat.setDecision(null);
      // Déclencher le _hook_ onSigne s'il est défini
      onSigne?.();
    }
  };

  const envoyerAuRequerant = async () => {
    setSauvegardeEnCours(true);

    try {
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/envoyer.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
      }
    } catch (e) {
      console.error(e);
    } finally {
      setSauvegardeEnCours(false);
      marquerFichierSigne(true);
      etat.setDecision(null);
      // Déclencher le _hook_ onSigne s'il est défini
      onSigne?.();
    }
  };

  return estEnAttenteConfirmation({ dossier, agent }) ? (
    <_modale.Component
      title={
        dossier.estAccepte()
          ? " Confirmer l'indemnisation"
          : " Confirmer le rejet"
      }
      iconId={
        dossier.estAccepte()
          ? "fr-icon-checkbox-circle-line"
          : "fr-icon-close-circle-line"
      }
      size="large"
    >
      {!etat.decision && (
        <>
          {dossier.estAccepte() && (
            <div
              className="fr-input-group fr-my-2w fr-grid-row"
              style={{ alignItems: "center" }}
            >
              <label
                className="fr-label fr-col-6"
                htmlFor="dossier-decision-acceptation-indemnisation-champs"
              >
                Montant de l'indemnisation
              </label>
              <div className="fr-input-wrap fr-icon-money-euro-circle-line fr-col-6">
                <input
                  className="fr-input"
                  defaultValue={montantIndemnisation}
                  onInput={(e: FormEvent<HTMLInputElement>) => {
                    const value = (e.target as HTMLInputElement).value;

                    setMontantIndemnisation(
                      value?.match(/^\d+(.\d{0,2})?$/)
                        ? parseFloat(value?.replace(",", "."))
                        : null,
                    );
                    setEditeurMode("edition");

                    if (dossier.estAccepte()) {
                      detecterMontantIndemnisation(corpsCourrier);
                    }
                  }}
                  aria-describedby="dossier-decision-acceptation-indemnisation-messages"
                  id="dossier-decision-acceptation-indemnisation-champs"
                  type="number"
                  step=".01"
                  inputMode="numeric"
                />
              </div>

              {dossier.estAccepte() &&
                montantIndemnisationLu &&
                montantIndemnisation !== montantIndemnisationLu && (
                  <Alert
                    className="fr-my-2w"
                    small={false}
                    closable={false}
                    severity="warning"
                    title="Attention : risque d'ambigüité sur le montant de
                      l'indemnisation"
                    description={
                      <>
                        <p>
                          Vous indiquez indemniser à hauteur de{" "}
                          <span className={"fr-text--bold"}>
                            {montantIndemnisation} €
                          </span>
                          , pourtant le courrier mentionne un montant
                          <i> en chiffres</i> de{" "}
                          <span className={"fr-text--bold"}>
                            {montantIndemnisationLu} €
                          </span>
                          .
                        </p>
                        <p>
                          Puisque la valeur déclarée dans le champs "Montant de
                          l'indemnisation" sera également mentionnée sur le
                          formulaire de déclaration d'acceptation, il y a un
                          risque d'ambigüité pour le requérant.
                        </p>
                        <p>
                          Veillez donc à bien accorder les montants dans le
                          courrier (en chiffres ainsi qu'en toutes lettres).
                        </p>
                      </>
                    }
                  />
                )}

              {!montantIndemnisation && (
                <div
                  className="fr-messages-group fr-message--error fr-my-1w"
                  id="dossier-decision-acceptation-indemnisation-messages"
                  aria-live="polite"
                >
                  <span>Vous devez définir un montant d'indemnisation</span>
                </div>
              )}
            </div>
          )}

          <EditeurDocument
            className="fr-input-group fr-col-12"
            mode={editeurMode}
            document={dossier.getDocumentType(
              DocumentType.TYPE_COURRIER_MINISTERE,
            )}
            onEdite={(corps) => {
              if (dossier.estAccepte()) {
                setCorpsCourrier(corps);
                detecterMontantIndemnisation(corps);
              }
            }}
            onImprime={(courrier) => dossier.addDocument(courrier)}
            onImpression={(impressionEnCours) =>
              setSauvegardeEnCours(impressionEnCours)
            }
          />

          <ButtonsGroup
            className="fr-mt-3w"
            alignment="right"
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            buttons={[
              {
                priority: "tertiary no outline",
                onClick: () => _modale.close(),
                disabled: sauvegardeEnCours,
                children: sauvegardeEnCours ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  "Annuler"
                ),
              },
              ...(editeurMode === "edition"
                ? ([
                    {
                      iconId: "fr-icon-eye-line",
                      children: "Visualiser",
                      priority: "secondary",
                      disabled: sauvegardeEnCours,
                      onClick: () => setEditeurMode("visualisation"),
                    },
                  ] as ButtonProps[])
                : ([
                    {
                      iconId: "fr-icon-edit-box-line",
                      children: "Éditer",
                      disabled: sauvegardeEnCours,
                      priority: "secondary",
                      onClick: () => setEditeurMode("edition"),
                    },
                    {
                      iconId: "fr-icon-edit-line",
                      onClick: () => etat.setDecision(true),
                      children: "Passer à la signature",
                    },
                  ] as ButtonProps[])),
            ]}
          />
        </>
      )}
      {
        // Téléversement, pour signature, du courrier
        etat.decision && !estFichierSigne && (
          <>
            <TelechargerPieceJointe
              pieceJointe={dossier.getDocumentType(
                DocumentType.TYPE_COURRIER_MINISTERE,
              )}
            />

            <div className="fr-upload-group">
              <label className="fr-label" htmlFor="file-upload">
                Téléverser le fichier pour signature
                <span className="fr-hint-text">
                  <span
                    className={`${!estTailleFichierOk(fichierSigne) ? "fr-text-default--error" : ""}`}
                  >
                    Taille maximale : 10 Mo.&nbsp;
                  </span>
                  <span
                    className={`${!estTypeFichierOk(fichierSigne) ? "fr-text-default--error" : ""}`}
                  >
                    Format pdf uniquement.&nbsp;
                  </span>
                </span>
              </label>{" "}
              <input
                className="fr-upload"
                type="file"
                id="file-upload"
                name="file-upload"
                accept="application/pdf"
                onChange={(e) => {
                  setFichierSigne(e.target.files[0] ?? null);
                }}
              />
            </div>

            <ButtonsGroup
              className="fr-mt-3w"
              alignment="right"
              inlineLayoutWhen="always"
              buttonsIconPosition="right"
              buttons={[
                {
                  priority: "tertiary no outline",
                  onClick: () => _modale.close(),
                  disabled: sauvegardeEnCours,
                  children: sauvegardeEnCours ? (
                    <i>Sauvegarde en cours ...</i>
                  ) : (
                    "Annuler"
                  ),
                },
                {
                  priority: "secondary",
                  onClick: () => etat.setDecision(false),
                  disabled: sauvegardeEnCours,
                  iconId: "fr-icon-edit-box-line",
                  children: "Éditer le courrier",
                },
                {
                  disabled:
                    !fichierSigne ||
                    !estTypeFichierOk(fichierSigne) ||
                    !estTailleFichierOk(fichierSigne) ||
                    sauvegardeEnCours,
                  onClick: () => signerCourrier(fichierSigne),
                  iconId: "fr-icon-send-plane-line",
                  children: "Téléverser et envoyer au requérant",
                },
              ]}
            />
          </>
        )
      }
      {
        // Envoi au requérant
        etat.decision && estFichierSigne && (
          <>
            <Alert
              small={false}
              closable={false}
              severity="info"
              title="Envoi imminent"
              description={
                <>
                  <p>
                    Vous vous apprêtez à faire part de votre décision au
                    requérant via l'envoi du courrier dûment signé.
                  </p>
                  <p>
                    Cette action est définitive: une fois le courrier transmis,
                    vous n'aurez plus la possibilité d'éditer votre réponse.
                  </p>
                  <p>
                    Aussi,{" "}
                    <span className="fr-text--bold">
                      veillez à bien relire le document
                    </span>{" "}
                    afin de vous assurer que tout est conforme.
                  </p>
                </>
              }
            />

            <ButtonsGroup
              className="fr-mt-3w"
              alignment="right"
              inlineLayoutWhen="always"
              buttonsIconPosition="right"
              buttons={[
                {
                  priority: "tertiary no outline",
                  children: sauvegardeEnCours ? (
                    <i>Sauvegarde en cours ...</i>
                  ) : (
                    "Annuler"
                  ),
                  onClick: () => _modale.close(),
                  disabled: sauvegardeEnCours,
                },
                {
                  disabled: sauvegardeEnCours,
                  onClick: () => envoyerAuRequerant(),
                  priority: "primary",
                  children: "Envoyer au requérant",
                },
              ]}
            />
          </>
        )
      }
    </_modale.Component>
  ) : (
    <></>
  );
});

export const confirmerBoutons = ({
  dossier,
  agent,
  etat = etatConfirmation,
}: {
  dossier: DossierDetail;
  agent: Agent;
  etat?: EtatConfirmation;
}): ButtonProps[] => {
  return estEnAttenteConfirmation({ dossier, agent })
    ? [
        {
          children: dossier.estAccepte()
            ? "Éditer la proposition d'indemnisation"
            : "Éditer le courrier de rejet",
          priority: "secondary",
          disabled: false,
          iconId: "fr-icon-edit-box-line",
          onClick: () => etat.setDecision(false),
        } as ButtonProps,
        {
          children: "Signer et envoyer",
          priority: "primary",
          disabled: false,
          iconId: "fr-icon-upload-line",
          onClick: () => etat.setDecision(true),
        } as ButtonProps,
      ]
    : [];
};
