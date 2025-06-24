import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { plainToInstance } from "class-transformer";
import React, { useState } from "react";
import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
  EtatDossierType,
} from "@/apps/agent/dossiers/models";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { EditeurDocument } from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument.tsx";

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
export const GenererArretePaiementModale =
  function GenererArretePaiementActionModale({
    dossier,
    agent,
  }: {
    dossier: DossierDetail;
    agent: Agent;
  }) {
    const estTailleFichierOk = (fichier?: File) =>
      fichier && fichier.size < 10 * 1024 * 1024;
    const estTypeFichierOk = (fichier?: File) =>
      fichier && ["application/pdf"].includes(fichier.type);

    return estEnAttenteEditionArretePaiement({ dossier, agent }) ? (
      <_modale.Component
        title="Vérifier la déclaration d'acceptation"
        size="large"
        iconId="fr-icon-search-line"
      >
        <>
          <EditeurDocument
            document={dossier
              .getDocumentsType(DocumentType.TYPE_ARRETE_PAIEMENT)
              .at(0)}
            onChange={(document: Document) => console.dir(document)}
          />

          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition="right"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
              },
              {
                children: "Signer et envoyer",
                iconId: "fr-icon-send-plane-line",
                priority: "primary",
              },
            ]}
          />
        </>
      </_modale.Component>
    ) : (
      <></>
    );
  };

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
          children: "Valider l'arrêté de paiement",
          iconId: "fr-icon-printer-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
