import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import {
  Document,
  DocumentType,
  DossierDetail,
  EtatDossierType,
} from "@common/models";
import { DocumentManagerInterface } from "@common/services/agent/document.ts";
import { EditeurDocument } from "@fip6/dossiers/components/consultation/document/EditeurDocument.tsx";
import { ChampPieceJointe } from "@fip6/dossiers/components/consultation/piecejointe";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { DossierManagerInterface } from "@fip6/services/dossier.ts";
import { useInjection } from "inversify-react";
import React, { useCallback, useEffect, useState } from "react";

const _modale = createModal({
  id: "modale-action-verifier-acceptation",
  isOpenedByDefault: false,
});

type ValidationAcceptationAction = "verification" | "generation" | "edition";

type ValidationAcceptationEtat = {
  action?: ValidationAcceptationAction;
  sauvegardeEnCours: boolean;
};

const estArreteAEditer = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): boolean =>
  dossier.estBrisDePorte() && // TODO supprimer ce test pour élargir aux autres dossiers
  dossier.enAttenteEditionArrete &&
  agent.instruit(dossier);

/**
 * Le rédacteur vérifie la déclaration d'acceptation et la valide
 */
export const EditerArretePaiementModale =
  function GenererArretePaiementActionModale({
    dossier,
    agent,
    onGenere,
    onImprime,
  }: {
    dossier: DossierDetail;
    agent: AgentFIP6;
    onGenere: () => void | Promise<void>;
    onImprime: (document: Document) => void | Promise<void>;
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
        sauvegardeEnCours: false,
        action: "verification",
      } as ValidationAcceptationEtat);
      _modale.close();
    };

    const [arretePaiement, setArretePaiement] = useState<Document | null>(
      dossier.getArretePaiement(),
    );

    const documentManager: DocumentManagerInterface =
      useInjection<DocumentManagerInterface>(DocumentManagerInterface.$);

    const dossierManager: DossierManagerInterface =
      useInjection<DossierManagerInterface>(DossierManagerInterface.$);

    const genererArretePaiement = useCallback(async () => {
      const arretePaiement =
        await documentManager.genererArretePaiement(dossier);
      await onImprime(arretePaiement);
      setArretePaiement(arretePaiement);
    }, [dossier.id]);

    // Si à l'étape de l'édition de l'arrêté de paiement, le document (corps et PDF) n'existe pas, on doit alors le
    // générer.
    useEffect(() => {
      if (
        etatValidation.action === "generation" &&
        !dossier.getArretePaiement()
      ) {
        genererArretePaiement();
      }
    }, [dossier.id]);

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
      setSauvegardeEnCours(true);

      await dossierManager.initierArretePaiement(dossier);
      await onGenere();

      setSauvegardeEnCours(false);
    };

    return estArreteAEditer({ dossier, agent }) ? (
      <_modale.Component
        title="Vérifier la déclaration d'acceptation"
        size="large"
        iconId="fr-icon-search-line"
        concealingBackdrop={false}
      >
        {etatValidation.action === "verification" && (
          <>
            {dossier.etat.etat === EtatDossierType.OK_ACCEPTE ? (
              <p>
                L'indemnisation a été acceptée par le requérant par signature
                électronique. Voici la déclaration d'acceptation :
              </p>
            ) : (
              <p>
                Inspectez le document de déclaration d'acceptation ci-dessous et
                vérifiez que tous les champs sont correctement remplis :
              </p>
            )}
            <ChampPieceJointe
              className="fr-my-2w"
              pieceJointe={dossier.getDeclarationAcceptation() as Document}
            />
            <ButtonsGroup
              inlineLayoutWhen="always"
              alignment="right"
              buttonsSize="small"
              buttons={[
                // TODO ajouter un bouton pour renvoyer à la signature avec un message
                {
                  children:
                    dossier.etat.etat === EtatDossierType.OK_ACCEPTE
                      ? dossier.hasDocumentsType(
                          DocumentType.TYPE_ARRETE_PAIEMENT,
                        )
                        ? "Éditer l'arrêté de paiement"
                        : "Générer l'arrêté de paiement"
                      : dossier.hasDocumentsType(
                            DocumentType.TYPE_ARRETE_PAIEMENT,
                          )
                        ? "Valider et éditer l'arrêté de paiement"
                        : "Valider et générer l'arrêté de paiement",
                  priority: "secondary",
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
                  priority: "secondary",
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
                onEdite={(corps: string) => (arretePaiement.corps = corps)}
                onImprime={(arretePaiement) => {
                  setArretePaiement(arretePaiement);
                  onImprime(arretePaiement);
                }}
                document={arretePaiement}
                onImpression={(impressionEnCours) =>
                  setSauvegardeEnCours(impressionEnCours)
                }
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
  agent: AgentFIP6;
}): ButtonProps[] => {
  return estArreteAEditer({ dossier, agent })
    ? [
        {
          children: "Générer l'arrêté de paiement",
          iconId: "fr-icon-printer-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
