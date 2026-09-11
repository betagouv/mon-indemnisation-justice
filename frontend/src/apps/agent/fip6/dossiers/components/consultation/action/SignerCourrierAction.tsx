import { Alert } from "@codegouvfr/react-dsfr/Alert";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { Loader } from "@common/composants/Loader.tsx";
import { EditeurDocument } from "@fip6/dossiers/components/consultation/document/EditeurDocument.tsx";
import { useInjection } from "inversify-react";
import React, { useCallback, useEffect, useRef, useState } from "react";

import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Document, DossierDetail } from "@common/models";
import {
  APIReponse,
  DocumentManagerInterface,
} from "@common/services/agent/document.ts";
import { ChampPieceJointe } from "@fip6/dossiers/components/consultation/piecejointe";
import { PrevisualiserPieceJointe } from "@fip6/dossiers/components/consultation/piecejointe/PrevisualiserPieceJointe.tsx";
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

// Positionne l'étape courante de la modale montée (cf. l'enregistrement fait par
// `SignerCourrierModale`), afin de permettre aux boutons de `signerCourrierBoutons`
// de choisir sur quelle étape l'ouvrir.
let definirEtape: (etape: IdEtape) => void = () => {};

const ouvrirModale = (etape: IdEtape) => {
  definirEtape(etape);
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
  // Étape en cours dans le parcours de signature : modifiable aussi bien depuis
  // l'extérieur (cf. `signerCourrierBoutons` / `ouvrirModale`) que par la modale
  // elle-même au fil de sa navigation interne.
  const [etape, setEtape] = useState<IdEtape>("EDITION_COURRIER");

  useEffect(() => {
    definirEtape = setEtape;

    return () => {
      definirEtape = () => {};
    };
  }, []);

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
                onImprime(reponse);
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

  const envoyerAuRequerant = useCallback(
    async ({ fichierSigne }: { fichierSigne: File }) => {
      setSauvegardeEnCours(true);

      await dossierManager.validerLaDecision(dossier, {
        estValide: true,
        fichierSigne,
      });
      await onSigne();

      setSauvegardeEnCours(false);
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
            document={dossier.getFormulaireDeclarationAcceptation() as Document}
            onEdite={(corps) => {}}
            onImprime={onImprime}
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
      {/* Envoi au requérant */}
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
                    <PrevisualiserPieceJointe fichier={fichierSigne as File} />
                  ),
                },
                {
                  label: "Déclaration d'acceptation",
                  iconId: "fr-icon-chat-check-line",
                  content: dossier.getFormulaireDeclarationAcceptation() ? (
                    <ChampPieceJointe
                      pieceJointe={
                        dossier.getFormulaireDeclarationAcceptation() as Document
                      }
                    />
                  ) : (
                    <p>
                      La déclaration d'acceptation n'est pas encore disponible.
                    </p>
                  ),
                },
              ]}
            />
          ) : (
            <PrevisualiserPieceJointe
              className="fr-my-3w"
              fichier={fichierSigne as File}
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
