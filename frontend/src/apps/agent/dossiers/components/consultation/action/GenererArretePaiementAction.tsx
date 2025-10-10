import { EditeurDocument } from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { plainToInstance } from "class-transformer";
import React, { useCallback, useEffect, useState } from "react";
import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/common/models";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import {
  DocumentManagerImpl,
  DocumentManagerInterface,
} from "@/common/services/agent";
import { useInjection } from "inversify-react";
import { createModal } from "@codegouvfr/react-dsfr/Modal";

const _modale = createModal({
  id: "modale-action-verifier-acceptation",
  isOpenedByDefault: false,
});

type ValidationAcceptationAction = "verification" | "generation" | "edition";

type ValidationAcceptationEtat = {
  action?: ValidationAcceptationAction;
  sauvegardeEnCours: boolean;
};

const estAVerifier = ({ dossier, agent }): boolean =>
  dossier.estAVerifier && agent.instruit(dossier);

/**
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

    const [arretePaiement, setArretePaiement] = useState<Document | null>(
      dossier.getDocumentType(DocumentType.TYPE_ARRETE_PAIEMENT),
    );

    const documentManager: DocumentManagerInterface =
      useInjection<DocumentManagerInterface>(DocumentManagerImpl);

    const genererArretePaiement = useCallback(async () => {
      const arretePaiement =
        await documentManager.genererArretePaiement(dossier);
      dossier.addDocument(arretePaiement);
      setArretePaiement(arretePaiement);
    }, [dossier.id]);

    // Si à l'étape de l'édition de l'arrêté de paiement, le document (corps et PDF) n'existe pas, on doit alors le
    // générer.
    useEffect(() => {
      if (etatValidation.action === "generation" && !arretePaiement) {
        genererArretePaiement();
      }
    }, [dossier.id, etatValidation.action]);

    const setSauvegardeEnCours = (sauvegardeEnCours: boolean = true) =>
      setEtatValidation({ ...etatValidation, sauvegardeEnCours });

    const verifierDeclarationAcceptation = () => {
      _modale.open();
      setEtatValidation({ ...etatValidation, action: "verification" });
    };

    const generer = async () => {
      setSauvegardeEnCours(true);
      setEtatValidation({ ...etatValidation, action: "generation" });
      await genererArretePaiement();
      setSauvegardeEnCours(false);

      editer();
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
              pieceJointe={
                dossier.getDocumentType(
                  DocumentType.TYPE_COURRIER_REQUERANT,
                ) as Document
              }
            />
            <ButtonsGroup
              inlineLayoutWhen="always"
              alignment="right"
              buttonsSize="small"
              buttons={[
                {
                  children: "Valider et éditer l'arrêté de paiement",
                  onClick: async () => {
                    if (
                      dossier.hasDocumentsType(
                        DocumentType.TYPE_ARRETE_PAIEMENT,
                      )
                    ) {
                      editer();
                    } else {
                      generer();
                    }
                  },
                } as ButtonProps,
              ]}
            />
          </>
        )}
        {etatValidation.action === "generation" && (
          <>
            <p>Génération de l'arrêté de paiement en cours ...</p>
            <ButtonsGroup
              inlineLayoutWhen="always"
              alignment="right"
              buttonsSize="small"
              buttons={[
                {
                  disabled: etatValidation.sauvegardeEnCours,
                  children: "Éditer l'arrêté de paiement",
                  onClick: () => editer(),
                } as ButtonProps,
              ]}
            />
          </>
        )}
        {etatValidation.action === "edition" && (
          <>
            {arretePaiement ? (
              <EditeurDocument
                className="fr-input-group fr-my-2w"
                document={arretePaiement}
                onImpression={(impressionEnCours) =>
                  setSauvegardeEnCours(impressionEnCours)
                }
                onImprime={(arretePaiement) =>
                  dossier.addDocument(arretePaiement)
                }
                lectureSeule={etatValidation.sauvegardeEnCours}
              />
            ) : (
              <>Génération de l'arrêté de paiement en cours...</>
            )}

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

export const genererArretePaiementBoutons = ({
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
