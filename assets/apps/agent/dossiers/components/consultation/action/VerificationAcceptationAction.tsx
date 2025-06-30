import { EditeurDocument } from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { plainToInstance } from "class-transformer";
import React, { useState } from "react";
import {
  Agent,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/apps/agent/dossiers/models";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";

const _modale = createModal({
  id: "modale-action-verifier-acceptation",
  isOpenedByDefault: false,
});

type ValidationAcceptationAction = "verification" | "edition";

type ValidationAcceptationEtat = {
  action?: ValidationAcceptationAction;
  sauvegardeEnCours: boolean;
};

const estAVerifier = ({ dossier, agent }): boolean =>
  dossier.estAVerifier && agent.instruit(dossier);

/**
 *
 * Le rédacteur vérifie la déclaration d'acceptation et la valide
 */
export const VerifierAcceptationModale =
  function VerifierAcceptationActionModale({
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
      sauvegardeEnCours: false,
      action: "verification",
    } as ValidationAcceptationEtat);

    // Actions
    const annuler = () => {
      setEtatValidation({
        corpsCourrier: dossier.getDocumentType(
          DocumentType.TYPE_COURRIER_MINISTERE,
        )?.corps,
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
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
      }

      setSauvegardeEnCours(false);
      _modale.close();
    };

    return estAVerifier({ dossier, agent }) ? (
      <_modale.Component
        title="Vérifier la déclaration d'acceptation"
        size="large"
        iconId="fr-icon-search-line"
      >
        {etatValidation.action === "verification" && (
          <>
            <p>
              Inspectez le document de déclaration d'acceptation ci-dessous et
              vérifiez que tous les champs sont correctement remplis :
            </p>
            <PieceJointe
              className="fr-my-2w"
              pieceJointe={dossier
                .getDocumentsType(DocumentType.TYPE_COURRIER_REQUERANT)
                .at(0)}
            />
            <ButtonsGroup
              inlineLayoutWhen="always"
              alignment="right"
              buttonsSize="small"
              buttons={[
                {
                  children: "Valider et éditer l'arrêté de paiement",
                  onClick: () => editer(),
                } as ButtonProps,
              ]}
            />
          </>
        )}
        {etatValidation.action === "edition" && (
          <>
            <EditeurDocument
              className="fr-input-group fr-my-2w"
              dossier={dossier}
              document={dossier.getDocumentType(
                DocumentType.TYPE_ARRETE_PAIEMENT,
              )}
              onImpression={(impressionEnCours) =>
                setSauvegardeEnCours(impressionEnCours)
              }
              onChange={(arretePaiement) => dossier.addDocument(arretePaiement)}
              lectureSeule={etatValidation.sauvegardeEnCours}
            />

            <ButtonsGroup
              inlineLayoutWhen="always"
              alignment="right"
              buttonsSize="small"
              buttons={[
                {
                  children: "Annuler",
                  priority: "tertiary no outline",
                  onClick: () => annuler(),
                } as ButtonProps,
                {
                  children: "Voir la déclaration",
                  priority: "secondary",
                  onClick: () => verifierDeclarationAcceptation(),
                } as ButtonProps,
                {
                  children: "Valider l'arrêté de paiement",
                  iconId: "fr-icon-send-plane-line",
                  priority: "primary",
                  disabled: etatValidation.sauvegardeEnCours,
                  onClick: async () => valider(),
                } as ButtonProps,
              ]}
            />
          </>
        )}
      </_modale.Component>
    ) : (
      <></>
    );
  };

export const verifierAcceptationBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estAVerifier({ dossier, agent })
    ? [
        {
          children: "Générer l'arrêté de paiement",
          iconId: "fr-icon-printer-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
