import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { useCallback, useState } from "react";
import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
  EtatDossierType,
} from "@/common/models";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import {
  EditeurDocument,
  EditeurMode,
} from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument.tsx";
import { TelechargerPieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import { Upload } from "@codegouvfr/react-dsfr/Upload";

const _modale = createModal({
  id: "modale-action-generer-arrete-paiement",
  isOpenedByDefault: false,
});

const estEnAttenteEditionArretePaiement = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): boolean =>
  dossier.etat.etat == EtatDossierType.OK_VERIFIE && agent.estValidateur();

/**
 *
 * Le rédacteur vérifie la déclaration d'acceptation et la valide
 */
export const GenererArretePaiementModale = observer(
  function GenererArretePaiementActionModale({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
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

    const [sauvegardeEnCours, setSauvegardeEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    // Le mode en cours sur l'éditeur de document
    const [editeurMode, setEditeurMode] = useState<EditeurMode>("edition");

    const envoyer = useCallback(async () => {
      if (fichierSigne) {
        setSauvegardeEnCours(true);

        const response = await fetch(
          `/agent/redacteur/dossier/${dossier.id}/arrete-paiement/signer.json`,
          {
            method: "POST",
            headers: {
              Accept: "application/json",
            },
            body: (() => {
              const data = new FormData();
              data.append("fichierSigne", fichierSigne);

              return data;
            })(),
          },
        );

        if (response.ok) {
          const data = await response.json();
          dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
          dossier.addDocument(plainToInstance(Document, data.document));
        }
        setSauvegardeEnCours(false);
      }
    }, [dossier.id, fichierSigne]);

    return estEnAttenteEditionArretePaiement({ dossier, agent }) ? (
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
            <EditeurDocument
              className="fr-my-2w"
              mode={editeurMode}
              document={dossier
                .getDocumentsType(DocumentType.TYPE_ARRETE_PAIEMENT)
                .at(0)}
              onImprime={(document: Document) => dossier.addDocument(document)}
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
                        children: "Signer et envoyer",
                        priority: "secondary",
                        disabled: sauvegardeEnCours,
                        iconId: "fr-icon-send-plane-line",
                        onClick: () => setEdition(false),
                      },
                    ] as ButtonProps[])),
              ]}
            />
          </>
        ) : (
          <>
            <TelechargerPieceJointe
              pieceJointe={dossier.getDocumentType(
                DocumentType.TYPE_ARRETE_PAIEMENT,
              )}
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
                  onClick: () => envoyer(),
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
  },
);

export const genererArretePaiementBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estEnAttenteEditionArretePaiement({ dossier, agent })
    ? [
        {
          children: "Signer l'arrêté de paiement",
          iconId: "fr-icon-printer-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
