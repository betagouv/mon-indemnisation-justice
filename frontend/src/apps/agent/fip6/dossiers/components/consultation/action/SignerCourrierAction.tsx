import { Alert } from "@codegouvfr/react-dsfr/Alert";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { Loader } from "@common/composants/Loader.tsx";
import { EditeurDocument } from "@fip6/dossiers/components/consultation/document/EditeurDocument.tsx";
import { useInjection } from "inversify-react";
import React, {
  InputEvent,
  useCallback,
  useEffect,
  useRef,
  useState,
} from "react";

import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Document, DossierDetail } from "@common/models";
import {
  APIReponse,
  DocumentManagerInterface,
} from "@common/services/agent/document.ts";
import { ChampPieceJointe } from "@fip6/dossiers/components/consultation/piecejointe";
import { TelechargerPieceJointe } from "@fip6/dossiers/components/consultation/piecejointe/TelechargerPieceJointe.tsx";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { DossierManagerInterface } from "@fip6/services/dossier.ts";

const _modale = createModal({
  id: "modale-action-confirmation",
  isOpenedByDefault: false,
});

// TODO tenter d'utiliser @common/composants/ListeEtapes.tsx;

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

// Étape à laquelle démarrer la modale, définie par le bouton qui l'ouvre et lue une seule fois à
// l'ouverture (cf. `onDisclose` du `useIsModalOpen` dans `SignerCourrierModale`)
let etapeInitiale: IdEtape = "EDITION_COURRIER";

const ouvrirModale = (etape: IdEtape) => {
  etapeInitiale = etape;
  _modale.open();
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
  agent: AgentFIP6;
}) =>
  dossier.estBrisDePorte() && // TODO supprimer ce test pour élargir aux autres dossiers
  dossier.enAttenteValidation &&
  agent.estValidateur();

export const SignerCourrierModale = ({
  dossier,
  agent,
  onImprime,
  onSigne,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  onImprime: (document: Document) => void | Promise<void>;
  onSigne: () => void | Promise<void>;
}) => {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );
  // Étape en cours dans le parcours de signature
  const [etape, setEtape] = useState<IdEtape>("EDITION_COURRIER");

  useIsModalOpen(_modale, {
    onDisclose: () => setEtape(etapeInitiale),
    onConceal: () => setEtape("EDITION_COURRIER"),
  });

  // Marqueur "_flag_" qui permet d'éviter de vérifier la date d'impression du
  // document qu'une seule fois :
  const verificationDateCourrier = useRef<number>(0);

  const [generationCourrierEnCours, setGenerationCourrierEnCours] =
    useState<boolean>(false);

  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerInterface.$);

  // Relancer une impression si le document n'est pas du jour
  useEffect(() => {
    const courrier = dossier.getCourrierDecision();

    if (courrier && !dossier.estEnvoye()) {
      if (
        // À l'étape d'édition du courrier...
        etape === "EDITION_COURRIER" &&
        // ... si la vérification de la date n'a pas encore été faite...
        verificationDateCourrier.current != dossier.id
      ) {
        // ... et que le courrier n'a pas été généré aujourd'hui même ...
        if (!courrier.estAJour()) {
          setGenerationCourrierEnCours(true);
          documentManager
            .imprimer(courrier, courrier.corps as string)
            .then(({ reponse, erreur }: APIReponse<Document>) => {
              if (!erreur) {
                dossier.addDocument(reponse);
              } else {
                // TODO afficher un message
              }

              setGenerationCourrierEnCours(false);
            });
        }
        verificationDateCourrier.current = dossier.id;
      }
    }
  }, [dossier.id, etape]);

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
    File | undefined,
    (fichierSigne: File) => void,
  ] = useState<File | undefined>(undefined);

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

  const envoyerAuRequerant = useCallback(
    async ({
      fichierSigne,
      montantIndemnisation = undefined,
    }: {
      fichierSigne: File;
      montantIndemnisation?: number;
    }) => {
      setSauvegardeEnCours(true);

      await dossierManager.validerLaDecision(dossier, {
        estValide: true,
        fichierSigne,
        montantIndemnisation,
      });
      await onSigne();
      _modale.close();
      setSauvegardeEnCours(false);

      setEtape("EDITION_COURRIER");
    },
    [dossier.id],
  );

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
      concealingBackdrop={false}
    >
      <Stepper
        currentStep={rangEtape(dossier, etape)}
        stepCount={dossier.estAccepte() ? 4 : 3}
        title={titreEtape(dossier, etape)}
        nextTitle={titreProchaineEtape(dossier, etape)}
      />

      {etape === "EDITION_COURRIER" && (
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
                  onInput={(e: InputEvent<HTMLInputElement>) => {
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
              onImprime={async (courrier) => {
                await onImprime(courrier);
                //dossier.addDocument(courrier);
              }}
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
                children: dossier.estAccepte()
                  ? "Valider la proposition d'indemnisation"
                  : "Valider le courrier de rejet",
                priority: "secondary",
                iconId: "fr-icon-arrow-right-line",
                onClick: () =>
                  setEtape(
                    dossier.estAccepte()
                      ? "EDITION_DECLARATION_ACCEPTATION"
                      : "SIGNATURE",
                  ),
                disabled: sauvegardeEnCours,
              },
            ]}
          />
        </>
      )}
      {/* Édition de la déclaration d'acceptation */}
      {etape === "EDITION_DECLARATION_ACCEPTATION" && (
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
                children: "Valider la déclaration d'acceptation",
                priority: "secondary",
                iconId: "fr-icon-arrow-right-line",
                onClick: () => setEtape("SIGNATURE"),
                disabled: sauvegardeEnCours,
              },
            ]}
          />
        </>
      )}
      {/* Téléversement, pour signature, du courrier */}
      {etape === "SIGNATURE" && (
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
                onClick: () => setEtape("EDITION_COURRIER"),
                disabled: sauvegardeEnCours,
                iconId: "fr-icon-edit-box-line",
                children: dossier.estAccepte()
                  ? "Éditer la proposition d'indemnisation"
                  : "Éditer le courrier de rejet",
              },
              {
                children: "Enregistrer le document signé",
                priority: "secondary",
                iconId: "fr-icon-arrow-right-line",
                disabled:
                  !fichierSigne ||
                  !estTypeFichierOk(fichierSigne) ||
                  !estTailleFichierOk(fichierSigne) ||
                  sauvegardeEnCours,
                onClick: async () => {
                  setEtape("ENVOI");
                },
              },
            ]}
          />
        </>
      )}
      {/* }Envoi au requérant */}
      {etape === "ENVOI" && (
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
                    veillez à bien relire{" "}
                    {dossier.estAccepte() ? (
                      <>les documents</>
                    ) : (
                      <>le document</>
                    )}
                  </span>{" "}
                  afin de vous assurer que tout est conforme.
                </p>
              </>
            }
          />

          {dossier.estAccepte() ? (
            <Tabs
              className="fr-my-3w"
              tabs={[
                {
                  label: "Proposition d'indemnisation",
                  iconId: "fr-icon-checkbox-circle-line",
                  isDefault: true,
                  content: (
                    <ChampPieceJointe
                      pieceJointe={dossier.getCourrierDecision() as Document}
                    />
                  ),
                },
                {
                  label: "Déclaration d'acceptation",
                  iconId: "fr-icon-chat-check-line",
                  content: (
                    <ChampPieceJointe
                      pieceJointe={
                        dossier.getDeclarationAcceptation() as Document
                      }
                    />
                  ),
                },
              ]}
            />
          ) : (
            <ChampPieceJointe
              className="fr-my-3w"
              pieceJointe={dossier.getCourrierDecision() as Document}
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
                children: sauvegardeEnCours ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  "Annuler"
                ),
                onClick: () => _modale.close(),
                disabled: sauvegardeEnCours,
              },
              {
                children: "Envoyer au requérant",
                priority: "primary",
                iconId: "fr-icon-send-plane-line",
                disabled: sauvegardeEnCours,
                onClick: () =>
                  envoyerAuRequerant({
                    fichierSigne: fichierSigne as File,
                    montantIndemnisation,
                  }),
              },
            ]}
          />
        </>
      )}
    </_modale.Component>
  ) : (
    <></>
  );
};

export const signerCourrierBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
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
          onClick: () => ouvrirModale("EDITION_COURRIER"),
        } as ButtonProps,
        {
          children: "Signer et envoyer",
          priority: "primary",
          disabled: false,
          iconId: "fr-icon-upload-line",
          onClick: () => ouvrirModale("SIGNATURE"),
        } as ButtonProps,
      ]
    : [];
};
