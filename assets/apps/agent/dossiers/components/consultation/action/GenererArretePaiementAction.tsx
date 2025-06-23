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

const _modale = createModal({
  id: "modale-action-verifier-acceptation",
  isOpenedByDefault: false,
});

type ValidationAcceptationAction = "verification" | "edition" | "relecture";

type ValidationAcceptationEtat = {
  action?: ValidationAcceptationAction;
  corpsCourrier: string;
  sauvegardeEnCours: boolean;
  arreteDePaiement?: Document;
};

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

    // Indique l'état de la validation en cours
    const [etatValidation, setEtatValidation]: [
      ValidationAcceptationEtat,
      (etat: ValidationAcceptationEtat) => void,
    ] = useState({
      corpsCourrier: dossier.corpsCourrier,
      sauvegardeEnCours: false,
      action: "verification",
      arreteDePaiement:
        dossier.getDocumentsType(DocumentType.TYPE_ARRETE_PAIEMENT).at(0) ??
        null,
    } as ValidationAcceptationEtat);

    // Actions
    const annuler = () => {
      setEtatValidation({
        corpsCourrier: dossier.corpsCourrier,
        sauvegardeEnCours: false,
        action: "verification",
      } as ValidationAcceptationEtat);
      _modale.close();
    };

    const setSauvegardeEnCours = (sauvegardeEnCours: boolean = true) =>
      setEtatValidation({ ...etatValidation, sauvegardeEnCours });

    const verifierDeclarationAcceptation = () => {
      _modale.open();
      setEtatValidation({ ...etatValidation, action: "verification" });
    };
    const editer = () =>
      setEtatValidation({ ...etatValidation, action: "edition" });

    const editerCorpsCourrier = (corpsCourrier: string) => {
      setEtatValidation({ ...etatValidation, corpsCourrier });
    };

    const genererArretePaiement = async () => {
      // Envoi à l'API pour impression, récupération du document
      setSauvegardeEnCours(true);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/arrete-paiement/generer.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            corpsCourrier: etatValidation.corpsCourrier,
          }),
        },
      );
      let document: Document;

      if (response.ok) {
        const data = await response.json();
        document = plainToInstance(Document, data.document);
        setEtatValidation({
          ...etatValidation,
          arreteDePaiement: document,
        });
      }

      setEtatValidation({
        ...etatValidation,
        action: "relecture",
        arreteDePaiement: document,
      });
    };

    const relire = () => {
      setEtatValidation({ ...etatValidation, action: "relecture" });
    };

    const valider = async () => {
      // Appel à l'API pour valider le document
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/arrete-paiement/valider.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            document: etatValidation.arreteDePaiement.id,
          }),
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
        dossier.addDocument(etatValidation.arreteDePaiement);
      }

      setSauvegardeEnCours(false);
      _modale.close();
    };

    return estEnAttenteEditionArretePaiement({ dossier, agent }) ? (
      <_modale.Component
        title="Vérifier la déclaration d'acceptation"
        size="large"
        iconId="fr-icon-search-line"
      >
        <></>
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
          children: "Éditer l'arrêté de paiement",
          iconId: "fr-icon-printer-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
