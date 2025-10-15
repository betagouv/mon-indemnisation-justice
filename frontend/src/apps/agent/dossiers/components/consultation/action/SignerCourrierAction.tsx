import { EditeurDocument } from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument";
import { Loader } from "@/common/components/Loader";
import {
  DocumentManagerImpl,
  DocumentManagerInterface,
} from "@/common/services/agent";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { useInjection } from "inversify-react";
import React, {
  FormEvent,
  ReactNode,
  useCallback,
  useEffect,
  useRef,
  useState,
} from "react";

import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/common/models";
import { observer } from "mobx-react-lite";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { plainToInstance } from "class-transformer";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { TelechargerPieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe/TelechargerPieceJointe.tsx";
import { proxy, useSnapshot } from "valtio";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { ToggleSwitch } from "@codegouvfr/react-dsfr/ToggleSwitch";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";

const _modale = createModal({
  id: "modale-action-confirmation",
  isOpenedByDefault: false,
});

// TODO tenter d'utiliser @/common/components/ListeEtapes.tsx;

type IdEtape =
  | "EDITION_COURRIER"
  | "EDITION_DECLARATION_ACCEPTATION"
  | "SIGNATURE"
  | "ENVOI";

const rangEtape = (dossier: DossierDetail, etape: IdEtape): number => {
  if (etape === "EDITION_COURRIER") {
    return 1;
  }

  if (etape === "EDITION_DECLARATION_ACCEPTATION") {
    return 2;
  }
  if (etape === "SIGNATURE") {
    return dossier.estAccepte() ? 3 : 2;
  }

  return dossier.estAccepte() ? 4 : 3;
};

const titreEtape = (dossier: DossierDetail, etape: IdEtape): string => {
  if (etape === "EDITION_COURRIER") {
    return dossier.estAccepte()
      ? "Éditer la proposition d'indemnisation"
      : "Éditer le courrier de rejet";
  }

  if (etape === "EDITION_DECLARATION_ACCEPTATION") {
    return "Éditer la déclaration d'acceptation";
  }
  if (etape === "SIGNATURE") {
    return "Téléverser le document signé";
  }

  return "Envoyer au requérant";
};

const prochaineEtape = (
  dossier: DossierDetail,
  etape: IdEtape,
): IdEtape | undefined => {
  if (etape === "EDITION_COURRIER") {
    return dossier.estAccepte()
      ? "EDITION_DECLARATION_ACCEPTATION"
      : "SIGNATURE";
  }

  if (etape === "EDITION_DECLARATION_ACCEPTATION") {
    return "SIGNATURE";
  }
  if (etape === "SIGNATURE") {
    return "ENVOI";
  }
};

const titreProchaineEtape = (
  dossier: DossierDetail,
  etape: IdEtape,
): string | undefined => {
  const p = prochaineEtape(dossier, etape);

  return p ? titreEtape(dossier, p) : undefined;
};

const registreSignature = proxy<{ etape: IdEtape }>({
  etape: "EDITION_COURRIER",
});

const versEtape = (etape: IdEtape) => {
  registreSignature.etape = etape;
};

const estTailleFichierOk = (fichier?: File) =>
  fichier && fichier.size < 10 * 1024 * 1024;
const estTypeFichierOk = (fichier?: File) =>
  fichier && ["application/pdf"].includes(fichier.type);

const estEnAttenteSignatureCourrier = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) => dossier.enAttenteValidation && agent.estValidateur();

export const SignerCourrierModale = observer(function SignerCourrierModale({
  dossier,
  agent,
  onSigne,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onSigne?: () => void;
}) {
  // État de l"opération de signature en cours :
  const etatSignature = useSnapshot<{ etape: IdEtape }>(registreSignature);

  // Marqueur "_flag_" qui permet d'éviter de vérifier la date d'impression du
  // document qu'une seule fois :
  const verificationDateCourrier = useRef<number>(0);

  const [generationCourrierEnCours, setGenerationCourrierEnCours] =
    useState<boolean>(false);

  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerImpl);

  // Relancer une impression si le document n'est pas du jour
  useEffect(() => {
    const courrier = dossier.getCourrierDecision();

    if (courrier && !dossier.estEnvoye()) {
      if (
        // À l'étape d'édition du courrier...
        etatSignature.etape === "EDITION_COURRIER" &&
        // ... si la vérification de la date n'a pas encore été faite...
        verificationDateCourrier.current != dossier.id
      ) {
        // ... et que le courrier n'a pas été généré aujourd'hui même ...
        if (!courrier.estAJour()) {
          setGenerationCourrierEnCours(true);
          documentManager
            .imprimer(courrier, courrier.corps as string)
            .then((document: Document) => {
              dossier.addDocument(document);

              setGenerationCourrierEnCours(false);
            });
        }
        verificationDateCourrier.current = dossier.id;
      }
    }
  }, [dossier.id, etatSignature.etape]);

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState<number>(
    dossier.montantIndemnisation ||
      (dossier.getCourrierDecision()?.metaDonnees
        ?.montantIndemnisation as number),
  );

  // Mémorise le montant de l'indemnisation lu dans le courrier
  const [montantIndemnisationLu, setMontantIndemnisationLu]: [
    number | null,
    (montant: number | null) => void,
  ] = useState<number | null>(null);

  // Corps du courrier (permet de chercher le montant de l'indemnisation en
  // chiffre et le comparer à la valeur saisie
  const [corpsCourrier, setCorpsCourrier]: [
    string,
    (corpsCourrier: string) => void,
  ] = useState<string>(dossier.getCourrierDecision()?.corps || "");

  // Fichier signé à téléverser
  const [fichierSigne, setFichierSigne]: [
    File | null,
    (fichierSigne: File) => void,
  ] = useState<File | null>(null);

  // Fichier signé à téléverser
  const [estFichierSigne, marquerFichierSigne]: [
    boolean,
    (estFichierSigne: boolean) => void,
  ] = useState(false);

  // Indique que l'agent ne souhaite pas téléverser de document et envoyer le
  // PDF existant
  const [utiliserCourrierExistant, setUtiliserCourrierExistant] =
    useState<boolean>(false);

  // Indique si la sauvegarde de la décision est en cours
  const [sauvegardeEnCours, setSauvegardeEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

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

  const changerMontantIndemnisation = useCallback(
    async (montantIndemnisation: number) => {
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/proposition-indemnisation/changer-montant.json`,
        {
          method: "PUT",
          headers: {
            Accept: "application/json",
          },
          body: JSON.stringify({
            montantIndemnisation,
          }),
        },
      );

      if (response.ok) {
        dossier.setMontantIndemnisation(montantIndemnisation);
      }
    },
    [dossier.id],
  );

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
            data.append("courrier", fichier);

            return data;
          })(),
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.addDocument(plainToInstance(Document, data.document));
      }
    } catch (e) {
      console.error(e);
    } finally {
      setSauvegardeEnCours(false);
      marquerFichierSigne(true);
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
          body: JSON.stringify({
            montantIndemnisation,
          }),
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
      versEtape("EDITION_COURRIER");
      // Déclencher le _hook_ onSigne s'il est défini
      onSigne?.();
    }
  };

  return estEnAttenteSignatureCourrier({ dossier, agent }) ? (
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
      <Stepper
        currentStep={rangEtape(dossier, etatSignature.etape)}
        stepCount={dossier.estAccepte() ? 4 : 3}
        title={titreEtape(dossier, etatSignature.etape)}
        nextTitle={titreProchaineEtape(dossier, etatSignature.etape)}
      />

      {etatSignature.etape === "EDITION_COURRIER" && (
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

                    if (value?.match(/^\d+(.\d{0,2})?$/)) {
                      setMontantIndemnisation(
                        parseFloat(value?.replace(",", ".")),
                      );

                      if (dossier.estAccepte()) {
                        detecterMontantIndemnisation(corpsCourrier);
                      }
                    }
                  }}
                  onBlur={() =>
                    changerMontantIndemnisation(montantIndemnisation)
                  }
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

          {generationCourrierEnCours ? (
            <>
              <Alert
                severity="info"
                title="Patience"
                description={
                  <>
                    Le courrier de décision est en train d'être re-généré pour
                    mettre à jour la date.
                  </>
                }
              />
              <Loader />
            </>
          ) : (
            <EditeurDocument
              className="fr-input-group fr-col-12"
              document={dossier.getCourrierDecision() as Document}
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
          )}

          <ButtonsGroup
            className="fr-mt-3w"
            alignment="right"
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            buttonsSize="small"
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
                priority: "primary",
                onClick: () =>
                  versEtape(
                    dossier.estAccepte()
                      ? "EDITION_DECLARATION_ACCEPTATION"
                      : "SIGNATURE",
                  ),
                disabled: sauvegardeEnCours,
                iconId: dossier.estAccepte()
                  ? "fr-icon-edit-box-line"
                  : "fr-icon-upload-line",
                children: dossier.estAccepte()
                  ? "Éditer la déclaration d'acceptation"
                  : "Téléverser le document signé",
              },
            ]}
          />
        </>
      )}
      {/* Édition de la déclaration d'acceptation */}
      {etatSignature.etape === "EDITION_DECLARATION_ACCEPTATION" && (
        <>
          <EditeurDocument
            className="fr-input-group fr-col-12"
            document={dossier.getDeclarationAcceptation() as Document}
            onEdite={(corps) => {}}
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
            buttonsSize="small"
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
                priority: "primary",
                onClick: () => versEtape("SIGNATURE"),
                disabled: sauvegardeEnCours,
                iconId: "fr-icon-upload-line",
                children: "Téléverser le document signé",
              },
            ]}
          />
        </>
      )}
      {/* Téléversement, pour signature, du courrier */}
      {etatSignature.etape === "SIGNATURE" && (
        <>
          <TelechargerPieceJointe
            pieceJointe={dossier.getCourrierDecision() as Document}
          />

          <Upload
            label="Téléverser le fichier pour signature"
            hint={
              <>
                <span
                  className={`${fichierSigne && !estTailleFichierOk(fichierSigne) ? "fr-text-default--error" : ""}`}
                >
                  Taille maximale : 10 Mo.&nbsp;
                </span>
                <span
                  className={`${fichierSigne && !estTypeFichierOk(fichierSigne) ? "fr-text-default--error" : ""}`}
                >
                  Format pdf uniquement.&nbsp;
                </span>
              </>
            }
            state="default"
            nativeInputProps={{
              accept: "application/pdf",
              onChange: (e) => {
                if (e.target.files && e.target.files.length > 0) {
                  setFichierSigne(e.target.files.item(0) as File);
                }
              },
            }}
          />

          <ToggleSwitch
            className="fr-my-3w"
            label="Utiliser le document existant"
            helperText="Évite le téléversement si le document PDF est déjà signé"
            inputTitle="utliser-document-existant"
            labelPosition="right"
            showCheckedHint={false}
            onChange={(checked) => setUtiliserCourrierExistant(checked)}
            checked={utiliserCourrierExistant}
          />

          <ButtonsGroup
            className="fr-mt-3w"
            alignment="right"
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            buttonsSize="small"
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
                onClick: () => versEtape("EDITION_COURRIER"),
                disabled: sauvegardeEnCours,
                iconId: "fr-icon-edit-box-line",
                children: "Éditer le courrier",
              },
              {
                disabled:
                  (!fichierSigne && !utiliserCourrierExistant) ||
                  (fichierSigne &&
                    (!estTypeFichierOk(fichierSigne) ||
                      !estTailleFichierOk(fichierSigne))) ||
                  sauvegardeEnCours,
                onClick: async () => {
                  if (!utiliserCourrierExistant) {
                    await signerCourrier(fichierSigne as File);
                  }
                  versEtape("ENVOI");
                },
                iconId: "fr-icon-send-plane-line",
                children: "Vérifier avant d'envoyer au requérant",
              },
            ]}
          />
        </>
      )}
      {/* }Envoi au requérant */}
      {etatSignature.etape === "ENVOI" && (
        <>
          <Alert
            small={false}
            closable={false}
            severity="info"
            title="Envoi imminent"
            description={
              <>
                <p>
                  Vous vous apprêtez à faire part de votre décision au requérant
                  via l'envoi du courrier dûment signé.
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

          <PieceJointe
            className="fr-my-3w"
            pieceJointe={dossier.getCourrierDecision() as Document}
          />

          <ButtonsGroup
            className="fr-mt-3w"
            alignment="right"
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            buttonsSize="small"
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
      )}
    </_modale.Component>
  ) : (
    <></>
  );
});

export const signerCourrierBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estEnAttenteSignatureCourrier({ dossier, agent })
    ? [
        {
          children: dossier.estAccepte()
            ? "Éditer la proposition d'indemnisation"
            : "Éditer le courrier de rejet",
          priority: "secondary",
          disabled: false,
          iconId: "fr-icon-edit-box-line",
          onClick: () => {
            versEtape("EDITION_COURRIER");
            _modale.open();
          },
        } as ButtonProps,
        {
          children: "Signer et envoyer",
          priority: "primary",
          disabled: false,
          iconId: "fr-icon-upload-line",
          onClick: () => {
            versEtape("SIGNATURE");
            _modale.open();
          },
        } as ButtonProps,
      ]
    : [];
};
