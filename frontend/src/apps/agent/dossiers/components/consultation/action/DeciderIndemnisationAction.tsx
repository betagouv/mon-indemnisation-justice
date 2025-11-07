import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { plainToInstance } from "class-transformer";
import React, {
  Dispatch,
  FormEvent,
  SetStateAction,
  useCallback,
  useEffect,
  useState,
} from "react";

import { Agent, Document, DossierDetail, EtatDossier } from "@/common/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { EditeurDocument } from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { useInjection } from "inversify-react";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { observer } from "mobx-react-lite";
import { Loader } from "@/common/components/Loader.tsx";
import { DocumentManagerInterface } from "@/common/services/agent/document.ts";

const _modale = createModal({
  id: "modale-action-decider-indemnisation",
  isOpenedByDefault: false,
});

type IdEtape =
  | "CHOIX_MONTANT_INDEMNISATION"
  | "EDITION_COURRIER_PI"
  | "EDITION_DECLARATION_ACCEPTATION"
  | "VERIFICATION_PI";

const Etapes: {
  liste: { [key in IdEtape]: string };
  rang: (id: IdEtape) => number;
  titreEtapeSuivante: (id: IdEtape) => string | undefined;
  taille: () => number;
} = {
  liste: {
    CHOIX_MONTANT_INDEMNISATION: "Définir le montant de l'indemnisation",
    EDITION_COURRIER_PI: "Éditer la proposition d'indemnisation",
    EDITION_DECLARATION_ACCEPTATION: "Éditer la déclaration d'indemnisation",
    VERIFICATION_PI: "Vérifier et valider les informations",
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

const estEnAttenteDecision = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) => dossier.enInstruction() && agent.instruit(dossier);

const DefinirMontantIndemnisation = ({
  montantIndemnisation,
  setMontantIndemnisation,
}: {
  montantIndemnisation: number;
  setMontantIndemnisation: Dispatch<SetStateAction<number>>;
}) => {
  return (
    <div className="fr-input-group fr-col-12">
      <label
        className="fr-label"
        htmlFor="dossier-decision-acceptation-indemnisation-champs"
      >
        Montant de l'indemnisation
      </label>
      <div className="fr-input-wrap fr-icon-money-euro-circle-line">
        <input
          className="fr-input"
          defaultValue={montantIndemnisation}
          onInput={(e: FormEvent<HTMLInputElement>) => {
            const value = (e.target as HTMLInputElement).value;
            if (value?.match(/^\d+(.\d{0,2})?$/)) {
              setMontantIndemnisation(parseFloat(value?.replace(",", ".")));
            }
          }}
          aria-describedby="dossier-decision-acceptation-indemnisation-messages"
          id="dossier-decision-acceptation-indemnisation-champs"
          type="number"
          step=".01"
          inputMode="numeric"
        />
      </div>
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
  );
};

export const DeciderIndemnisationModale = observer(
  function DeciderIndemnisationModale({
    dossier,
    agent,
    onDecide,
  }: {
    dossier: DossierDetail;
    agent: Agent;
    onDecide?: () => void;
  }) {
    // Le courrier de décision
    const [courrier, setCourrier] = useState<Document | null>(
      dossier.getCourrierDecision(),
    );

    // Actualiser le courrier de décision dès lors qu'il aurait été changé en dehors (ex : décision de rejet)
    useEffect(() => {
      setCourrier(dossier.getCourrierDecision());
    }, [dossier.id, dossier.getCourrierDecision()?.fileHash]);

    // La déclaration d'acceptation
    const [declarationAcceptation, setDeclarationAcceptation] =
      useState<Document | null>(dossier.getDeclarationAcceptation());

    // L'étape en cours :
    const [etape, setEtape] = useState<IdEtape>("CHOIX_MONTANT_INDEMNISATION");

    // Mémorise le montant de l'indemnisation
    const [montantIndemnisation, setMontantIndemnisation]: [
      number,
      (montant: number) => void,
    ] = useState(courrier?.metaDonnees?.montantIndemnisation ?? 100);

    // Indique si une génération de courrier est en cours
    const [generationEnCours, setGenerationEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
    const [sauvegardeEnCours, setSauvegarderEnCours]: [
      boolean,
      (mode: boolean) => void,
    ] = useState(false);

    const annuler = () => {
      _modale.close();
      setEtape("CHOIX_MONTANT_INDEMNISATION");
    };

    const documentManager: DocumentManagerInterface =
      useInjection<DocumentManagerInterface>(DocumentManagerInterface.$);

    const genererCourrierPropositionIndemnisation = useCallback(
      async (dossier: DossierDetail, montantIndemnisation: number) => {
        setGenerationEnCours(true);
        setCourrier(null);
        const courrierPI =
          await documentManager.genererCourrierPropositionIndemnisation(
            dossier,
            montantIndemnisation,
          );
        dossier.addDocument(courrierPI);
        setCourrier(courrierPI);
        setGenerationEnCours(false);
      },
      [dossier.id],
    );

    const genererDeclarationAcceptation = useCallback(
      async (dossier: DossierDetail, montantIndemnisation: number) => {
        setGenerationEnCours(true);
        setDeclarationAcceptation(null);
        const document = await documentManager.genererDeclarationAcceptation(
          dossier,
          montantIndemnisation,
        );
        dossier.addDocument(document);
        setDeclarationAcceptation(document);
        setGenerationEnCours(false);
      },
      [dossier],
    );

    const deciderDossier = useCallback(
      async ({ montantIndemnisation }: { montantIndemnisation: number }) => {
        setSauvegarderEnCours(true);

        const response = await fetch(
          `/agent/redacteur/dossier/${dossier.id}/decider.json`,
          {
            method: "POST",
            headers: {
              "Content-type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify({ montantIndemnisation }),
          },
        );

        if (response.ok) {
          dossier.montantIndemnisation = montantIndemnisation;
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
        title=" Accepter la demande d'indemnisation"
        iconId="fr-icon-checkbox-circle-line"
        size="large"
      >
        <Stepper
          currentStep={Etapes.rang(etape)}
          stepCount={Etapes.taille()}
          title={Etapes.liste[etape]}
          nextTitle={Etapes.titreEtapeSuivante(etape)}
        />

        {/* Choix du montant de l'indemnisation */}
        {etape === "CHOIX_MONTANT_INDEMNISATION" && (
          <>
            <DefinirMontantIndemnisation
              montantIndemnisation={montantIndemnisation}
              setMontantIndemnisation={(montantIndemnisation: number) =>
                setMontantIndemnisation(montantIndemnisation)
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
                  disabled: !montantIndemnisation || generationEnCours,
                  iconId: "fr-icon-edit-box-line",
                  onClick: async () => {
                    if (
                      montantIndemnisation !==
                        courrier?.metaDonnees?.montantIndemnisation ||
                      courrier?.metaDonnees?.motifRejet
                    ) {
                      genererCourrierPropositionIndemnisation(
                        dossier,
                        montantIndemnisation,
                      );
                    }

                    setEtape("EDITION_COURRIER_PI");
                  },
                  children: "Éditer le courrier",
                },
              ]}
            />
          </>
        )}

        {etape === "EDITION_COURRIER_PI" && (
          <>
            {courrier ? (
              <EditeurDocument
                className="fr-my-2w"
                document={courrier as Document}
                onImprime={(document: Document) => {
                  setCourrier(document);
                  dossier.addDocument(document);
                }}
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
                  children: "Changer le montant",
                  priority: "secondary",
                  iconId: "fr-icon-money-euro-circle-line",
                  onClick: () => setEtape("CHOIX_MONTANT_INDEMNISATION"),
                },
                {
                  disabled: !courrier || generationEnCours,
                  iconId: "fr-icon-edit-box-line",
                  onClick: () => {
                    setEtape("EDITION_DECLARATION_ACCEPTATION");

                    if (
                      declarationAcceptation?.metaDonnees
                        ?.montantIndemnisation !== montantIndemnisation
                    ) {
                      genererDeclarationAcceptation(
                        dossier,
                        montantIndemnisation,
                      );
                    }
                  },
                  children: "Éditer la déclaration d'acceptation",
                },
              ]}
            />
          </>
        )}

        {etape === "EDITION_DECLARATION_ACCEPTATION" && (
          <>
            {declarationAcceptation ? (
              <EditeurDocument
                className="fr-my-2w"
                document={declarationAcceptation as Document}
                onImprime={(document: Document) => {
                  setDeclarationAcceptation(document);
                  dossier.addDocument(document);
                }}
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
                  children: "Éditer la PI",
                  priority: "secondary",
                  iconId: "fr-icon-edit-box-line",
                  onClick: () => setEtape("EDITION_COURRIER_PI"),
                },
                {
                  disabled: !declarationAcceptation || generationEnCours,
                  iconId: "fr-icon-edit-box-line",
                  onClick: () => setEtape("VERIFICATION_PI"),
                  children: "Vérifier les informations",
                },
              ]}
            />
          </>
        )}

        {etape === "VERIFICATION_PI" && (
          <>
            <p>
              Vous vous apprêtez à proposer une indemnisation d'un montant de{" "}
              <b>{montantIndemnisation} €</b> à{" "}
              <b>{dossier.requerant.nomSimple()}</b>.
            </p>

            <p>
              Vérifiez une dernière fois le courrier de proposition
              d'indemnisation ainsi que la déclaration d'acceptation avant de
              valider et envoyer pour signature.
            </p>

            <Tabs
              tabs={[
                {
                  label: "Proposition d'indemnisation",
                  iconId: "fr-icon-checkbox-circle-line",
                  isDefault: true,
                  content: (
                    <PieceJointe
                      pieceJointe={dossier.getCourrierDecision() as Document}
                    />
                  ),
                },
                {
                  label: "Déclaration d'acceptation",
                  iconId: "fr-icon-chat-check-line",
                  content: (
                    <PieceJointe
                      pieceJointe={
                        dossier.getDeclarationAcceptation() as Document
                      }
                    />
                  ),
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
                  children: "Éditer la déclaration d'acceptation",
                  priority: "secondary",
                  iconId: "fr-icon-edit-box-line",
                  onClick: () => setEtape("EDITION_DECLARATION_ACCEPTATION"),
                },
                {
                  disabled: !courrier,
                  iconId: "fr-icon-send-plane-fill",
                  onClick: () => deciderDossier({ montantIndemnisation }),
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
  },
);

export const deciderIndemnisationBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estEnAttenteDecision({ dossier, agent })
    ? [
        {
          children: "Accepter",
          priority: "primary",
          disabled: false,
          iconId: "fr-icon-check-line",
          onClick: () => {
            _modale.open();
          },
        } as ButtonProps,
      ]
    : [];
};
