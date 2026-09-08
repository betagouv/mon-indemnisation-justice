import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Loader } from "@common/composants/Loader.tsx";
import { Document, DossierDetail, EtatDossierType } from "@common/models";
import { DocumentManagerInterface } from "@common/services/agent/document.ts";
import { EditeurDocument } from "@fip6/dossiers/components/consultation/document/EditeurDocument.tsx";
import { TelechargerPieceJointe } from "@fip6/dossiers/components/consultation/piecejointe";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { DossierManagerInterface } from "@fip6/services/dossier.ts";
import { useInjection } from "inversify-react";
import React, { useCallback, useEffect, useRef, useState } from "react";

const _modale = createModal({
  id: "modale-action-generer-arrete-paiement",
  isOpenedByDefault: false,
});

const estEnAttenteSignatureArretePaiement = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): boolean =>
  dossier.etat.etat == EtatDossierType.OK_VERIFIE && agent.estValidateur();

/**
 *
 * Le rédacteur vérifie la déclaration d'acceptation et la valide
 */
export const SignerArretePaiementModale = ({
  dossier,
  agent,
  onSigne,
  onImprime,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  onSigne: () => void | Promise<void>;
  onImprime: (document: Document) => void | Promise<void>;
}) => {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Est-ce que l'édition de l'arrêté de paiement est en cours
  const [estEdition, setEdition] = useState(true);

  // Fichier signé à téléverser
  const [fichierSigne, setFichierSigne]: [
    File | null,
    (fichierSigne: File) => void,
  ] = useState<File | null>(null);

  const estTailleFichierOk = (fichier?: File) =>
    fichier && fichier.size < 10 * 1024 * 1024;
  const estTypeFichierOk = (fichier?: File) =>
    fichier && ["application/pdf"].includes(fichier.type);

  // Marqueur "_flag_" qui permet d'éviter de vérifier la date d'impression du
  // document qu'une seule fois :
  const verificationDateCourrier = useRef<number>(0);

  const [generationCourrierEnCours, setGenerationCourrierEnCours] =
    useState<boolean>(false);

  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerInterface.$);

  // Relancer une impression si le document n'est pas du jour
  useEffect(() => {
    const arrete = dossier.getArretePaiement();

    if (arrete && estEnAttenteSignatureArretePaiement({ dossier, agent })) {
      if (
        // À l'étape d'édition de l'arrêté...
        estEdition &&
        // ... si la vérification de la date n'a pas encore été faite...
        verificationDateCourrier.current != dossier.id
      ) {
        // ... et que l'arrêté n'a pas été généré aujourd'hui même ...
        if (!arrete.estAJour()) {
          // ... alors on le ré-imprime
          setGenerationCourrierEnCours(true);
          documentManager.imprimer(arrete).then(({ reponse, erreur }) => {
            if (reponse) {
              onImprime(reponse);
            }

            setGenerationCourrierEnCours(false);
          });
        }
        verificationDateCourrier.current = dossier.id;
      }
    }
  }, [dossier.id, estEdition]);

  const [sauvegardeEnCours, setSauvegardeEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const signerEtEnvoyer = useCallback(async () => {
    if (fichierSigne) {
      setSauvegardeEnCours(true);
      await dossierManager.validerArretePaiement(dossier, fichierSigne);
      await onSigne();
      setSauvegardeEnCours(false);
    }
  }, [dossier.id, fichierSigne]);

  return estEnAttenteSignatureArretePaiement({ dossier, agent }) ? (
    <_modale.Component
      title={
        estEdition
          ? " Éditer l'arrêté de paiement"
          : " Signer l'arrêté de paiement"
      }
      size="large"
      iconId="fr-icon-printer-line"
    >
      {estEdition ? (
        <>
          {generationCourrierEnCours ? (
            <>
              <Alert
                severity="info"
                title="Patience"
                description={
                  <>
                    L'arrêté de paiement est en train d'être re-généré pour
                    mettre à jour la date.
                  </>
                }
              />
              <Loader />
            </>
          ) : (
            <EditeurDocument
              className="fr-my-2w"
              document={dossier.getArretePaiement() as Document}
              onImprime={onImprime}
            />
          )}

          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => _modale.close(),
              },
              {
                children: "Signer et envoyer",
                priority: "secondary",
                disabled: sauvegardeEnCours,
                iconId: "fr-icon-send-plane-line",
                onClick: () => setEdition(false),
              },
            ]}
          />
        </>
      ) : (
        <>
          <TelechargerPieceJointe
            pieceJointe={dossier.getArretePaiement() as Document}
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
            stateRelatedMessage="Text de validation / d'explication de l'erreur"
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
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => _modale.close(),
              },
              {
                children: "Éditer l'arrêté de paiement",
                iconId: "fr-icon-pencil-line",
                onClick: () => setEdition(true),
                priority: "secondary",
              },
              {
                children: "Signer et envoyer pour paiement",
                iconId: "fr-icon-send-plane-line",
                disabled:
                  sauvegardeEnCours ||
                  !fichierSigne ||
                  !estTailleFichierOk(fichierSigne) ||
                  !estTypeFichierOk(fichierSigne),
                onClick: () => signerEtEnvoyer(),
                priority: "primary",
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

export const signerArretePaiementBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): ButtonProps[] => {
  return estEnAttenteSignatureArretePaiement({ dossier, agent })
    ? [
        {
          children: "Signer l'arrêté de paiement",
          iconId: "fr-icon-printer-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
