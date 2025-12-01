import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { plainToInstance } from "class-transformer";
import React, {
  Dispatch,
  SetStateAction,
  useCallback,
  useEffect,
  useState,
} from "react";

import { Agent, Document, DossierDetail, EtatDossier } from "@/common/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import {
  EditeurDocument,
  EditeurMode,
} from "@/apps/agent/fip6/dossiers/components/consultation/document/EditeurDocument.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { useInjection } from "inversify-react";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { PieceJointe } from "@/apps/agent/fip6/dossiers/components/consultation/piecejointe";
import { observer } from "mobx-react-lite";
import { Loader } from "@/common/components/Loader.tsx";
import { DocumentManagerInterface } from "@/common/services/agent/document.ts";

const _modale = createModal({
  id: "modale-action-decider-rejet",
  isOpenedByDefault: false,
});

type IdEtape =
  | "CHOIX_MOTIF_REJET"
  | "EDITION_COURRIER_REJET"
  | "VERIFICATION_REJET";

const Etapes: {
  liste: { [key in IdEtape]: string };
  rang: (id: IdEtape) => number;
  titreEtapeSuivante: (id: IdEtape) => string | undefined;
  taille: () => number;
} = {
  liste: {
    CHOIX_MOTIF_REJET: "Définir le motif de refus",
    EDITION_COURRIER_REJET: "Éditer le courrier de rejet",
    VERIFICATION_REJET: "Vérifier et valider les informations",
  },
  rang: function (id: IdEtape): number {
    return Object.keys(this.liste).indexOf(id) + 1;
  },
  titreEtapeSuivante: function (id: IdEtape): string | undefined {
    return [...(Object.values(this.liste) as string[])].at(
      Object.keys(this.liste).indexOf(id) + 1,
    );
  },
  taille: function (): number {
    return [...Object.keys(this.liste)].length;
  },
};

type MotifRejet = "est_bailleur" | "est_vise" | "est_hebergeant" | "autre";

const estEnAttenteDecision = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) => dossier.enInstruction() && agent.instruit(dossier);

const DefinirMotifRefus = ({
  motifRejet,
  setMotifRejet,
}: {
  motifRejet: MotifRejet | null;
  setMotifRejet: Dispatch<SetStateAction<MotifRejet>>;
}) => {
  return (
    <>
      <div className="fr-select-group fr-col-12">
        <label
          className="fr-label"
          htmlFor="dossier-decision-acceptation-motif-champs"
        >
          Motif du refus
        </label>

        <select
          className="fr-select"
          defaultValue={motifRejet ?? ""}
          onChange={(e) => setMotifRejet(e.target.value as MotifRejet)}
        >
          <option value="est_bailleur">
            Le requérant est le bailleur (art. 1732)
          </option>
          <option value="est_vise">
            Le requérant était visé par l'opération
          </option>
          <option value="est_hebergeant">
            Le requérant hébergeait la personne visée par l'opération
          </option>
        </select>
      </div>
    </>
  );
};
export const DeciderRejetModale = observer(function DeciderRejetModale({
  dossier,
  agent,
  onDecide,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onDecide?: () => void;
}) {
  const [courrier, setCourrier] = useState<Document | null>(
    dossier.getCourrierDecision(),
  );

  // Actualiser le courrier de décision dès lors qu'il aurait été changé en dehors (ex : décision de rejet)
  useEffect(() => {
    setCourrier(dossier.getCourrierDecision());
  }, [dossier.id, dossier.getCourrierDecision()?.fileHash]);

  // L'étape en cours :
  const [etape, setEtape] = useState<IdEtape>("CHOIX_MOTIF_REJET");

  // Mémorise le motif de rejet
  const [motifRejet, setMotifRejet]: [
    MotifRejet | null,
    (motif: MotifRejet) => void,
  ] = useState<MotifRejet | null>(
    (courrier?.metaDonnees?.motifRejet ?? dossier.qualiteRequerant == "PRO")
      ? "est_bailleur"
      : dossier.testEligibilite
        ? dossier.testEligibilite.estVise
          ? "est_vise"
          : dossier.testEligibilite.estHebergeant
            ? "est_hebergeant"
            : null
        : null,
  );

  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Indique si une génération de courrier est en cours
  const [generationEnCours, setGenerationEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const annuler = () => {
    _modale.close();
  };

  useIsModalOpen(_modale, {
    onConceal: () => setEtape("CHOIX_MOTIF_REJET"),
  });

  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerInterface.$);

  const genererCourrierRejet = useCallback(
    async (dossier: DossierDetail, motifRejet: MotifRejet) => {
      setGenerationEnCours(true);
      const courrierRejet = await documentManager.genererCourrierRejet(
        dossier,
        motifRejet,
      );
      dossier.addDocument(courrierRejet);
      setCourrier(courrierRejet);
      setGenerationEnCours(false);
    },
    [dossier],
  );

  const deciderDossier = useCallback(
    async ({ motifRejet }: { motifRejet: MotifRejet }) => {
      setSauvegarderEnCours(true);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/decider.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({ motifRejet }),
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
      }

      annuler();

      setSauvegarderEnCours(false);

      // Déclencher le hook `onDecide` s'il est défini
      onDecide?.();
    },
    [dossier],
  );

  return estEnAttenteDecision({ dossier, agent }) ? (
    <_modale.Component
      title=" Rejeter la demande d'indemnisation"
      iconId="fr-icon-close-circle-line"
      size="large"
    >
      <Stepper
        currentStep={Etapes.rang(etape)}
        stepCount={Etapes.taille()}
        title={Etapes.liste[etape]}
        nextTitle={Etapes.titreEtapeSuivante(etape)}
      />

      {/* Choix du motif de rejet */}
      {etape === "CHOIX_MOTIF_REJET" && (
        <>
          <DefinirMotifRefus
            motifRejet={motifRejet}
            setMotifRejet={(motifRejet: MotifRejet) =>
              setMotifRejet(motifRejet)
            }
          />
          <ButtonsGroup
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            alignment="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => annuler(),
              },
              {
                disabled: !motifRejet,
                priority: "primary",
                iconId: "fr-icon-edit-box-line",
                onClick: async () => {
                  if (
                    null !== motifRejet &&
                    (motifRejet != courrier?.metaDonnees?.motifRejet ||
                      courrier?.metaDonnees?.montantIndemnisation)
                  ) {
                    genererCourrierRejet(dossier, motifRejet);
                  }

                  setEtape("EDITION_COURRIER_REJET");
                },
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {etape === "EDITION_COURRIER_REJET" && (
        <>
          {courrier ? (
            <EditeurDocument
              className="fr-my-2w"
              document={courrier as Document}
              regenererDocument={() =>
                genererCourrierRejet(dossier, motifRejet as MotifRejet)
              }
              onImprime={(document: Document) => dossier.addDocument(document)}
              onImpression={(impressionEnCours) =>
                setGenerationEnCours(impressionEnCours)
              }
            />
          ) : (
            <Loader />
          )}
          <ButtonsGroup
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            alignment="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => {
                  annuler();
                },
              },
              {
                children: "Changer le motif de refus",
                priority: "secondary",
                iconId: "fr-icon-chat-delete-line",
                onClick: () => setEtape("CHOIX_MOTIF_REJET"),
              },
              {
                disabled: !courrier || generationEnCours,
                iconId: "fr-icon-send-plane-line",
                onClick: () => {
                  setEtape("VERIFICATION_REJET");
                },
                children: "Valider et vérifier",
              },
            ]}
          />
        </>
      )}

      {etape === "VERIFICATION_REJET" && (
        <>
          <p>
            Vous vous apprêtez à rejeter la demande d'indemnisation de
            <b> {dossier.requerant.nomSimple()}</b>.
          </p>

          <p>
            Vérifiez une dernière fois le courrier de rejet avant de valider et
            envoyer pour signature.
          </p>

          <Tabs
            tabs={[
              {
                label: "Courrier de rejet",
                iconId: "fr-icon-close-circle-line",
                isDefault: true,
                content: <PieceJointe pieceJointe={courrier as Document} />,
              },
            ]}
          />

          <ButtonsGroup
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            alignment="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => {
                  annuler();
                },
              },
              {
                children: "Éditer le courrier",
                priority: "secondary",
                iconId: "fr-icon-edit-box-line",
                onClick: () => setEtape("EDITION_COURRIER_REJET"),
              },
              {
                disabled: !courrier || sauvegardeEnCours,
                iconId: "fr-icon-send-plane-fill",
                onClick: () =>
                  deciderDossier({ motifRejet: motifRejet as MotifRejet }),
                children: "Valider et envoyer pour signature",
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

export const deciderRejetBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estEnAttenteDecision({ dossier, agent })
    ? [
        {
          children: "Rejeter",
          priority: "secondary",
          disabled: false,
          iconId: "fr-icon-close-line",
          onClick: () => {
            _modale.open();
          },
        },
      ]
    : [];
};
