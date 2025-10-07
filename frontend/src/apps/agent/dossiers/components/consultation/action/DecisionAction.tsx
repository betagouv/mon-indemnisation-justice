import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { plainToInstance } from "class-transformer";
import React, {
  Dispatch,
  FormEvent,
  SetStateAction,
  useCallback,
  useState,
} from "react";

import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/common/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import {
  EditeurDocument,
  EditeurMode,
} from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { proxy, useSnapshot } from "valtio";
import {
  DocumentManagerImpl,
  DocumentManagerInterface,
} from "@/common/services/agent";
import { useInjection } from "inversify-react";

const _modale = createModal({
  id: "modale-action-decider",
  isOpenedByDefault: false,
});

const ouvrirModale = () => _modale.open();
const fermerModale = () => _modale.close();

type NomEtapeDecision =
  | "CHOIX_MOTIF_REJET"
  | "EDITION_COURRIER_REJET"
  | "VERIFICATION_REJET"
  | "CHOIX_MONTANT_INDEMNISATION"
  | "EDITION_COURRIER_PI"
  | "EDITION_DECLARATION_ACCEPTATION"
  | "VERIFICATION_PI";

class EtapeDecision {
  public static VERIFICATION_PI = new EtapeDecision(
    "VERIFICATION_PI",
    4,
    "Vérifier et valider les informations",
  );
  public static EDITION_DECLARATION_ACCEPTATION = new EtapeDecision(
    "EDITION_DECLARATION_ACCEPTATION",
    3,
    "Éditer la déclaration d'acceptation",
    EtapeDecision.VERIFICATION_PI,
  );
  public static EDITION_COURRIER_PI = new EtapeDecision(
    "EDITION_COURRIER_PI",
    2,
    "Éditer la proposition d'indemnisation",
    EtapeDecision.EDITION_DECLARATION_ACCEPTATION,
  );
  public static CHOIX_MONTANT_INDEMNISATION = new EtapeDecision(
    "CHOIX_MONTANT_INDEMNISATION",
    1,
    "Définir le montant de l'indemnisation",
    EtapeDecision.EDITION_COURRIER_PI,
  );
  public static VERIFICATION_REJET = new EtapeDecision(
    "VERIFICATION_REJET",
    3,
    "Vérifier et valider les informations",
  );
  public static EDITION_COURRIER_REJET = new EtapeDecision(
    "EDITION_COURRIER_REJET",
    2,
    "Éditer le courrier de rejet",
    EtapeDecision.VERIFICATION_REJET,
  );
  public static readonly CHOIX_MOTIF_REJET = new EtapeDecision(
    "CHOIX_MOTIF_REJET",
    1,
    "Définir le motif de refus",
    EtapeDecision.EDITION_COURRIER_REJET,
  );

  private constructor(
    public readonly nom: NomEtapeDecision,
    public readonly rang: number,
    public readonly titre: string,
    public readonly suivant: EtapeDecision | null = null,
  ) {}

  get longueur(): number {
    return this.rang + (this.suivant?.profondeur ?? 0);
  }

  protected get profondeur(): number {
    return 1 + (this.suivant?.profondeur ?? 0);
  }
}

//type EtapeDecision = "choix_motif" | "choix_montant" | "edition_courrier";
type MotifRejet = "est_bailleur" | "est_vise" | "est_hebergeant" | "autre";

interface EtatDecision {
  decision: boolean;
  etape: EtapeDecision;
  montantIndemnisation: number;
  motifRejet?: MotifRejet;
}

const store = proxy<EtatDecision>({
  etape: EtapeDecision.CHOIX_MOTIF_REJET,
  decision: false,
  montantIndemnisation: 100,
});

const versEtape = (etape: EtapeDecision) => (store.etape = etape);
const opterDecision = (decision: boolean) => {
  store.decision = decision;
};

const definirMotifRejet = (motifRejet: MotifRejet) => {
  store.decision = false;
  store.motifRejet = motifRejet;
};
const definirMontantIndemnisation = (montantIndemnisation: number) => {
  store.decision = true;
  store.montantIndemnisation = montantIndemnisation;
};

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
  motifRejet?: MotifRejet;
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
          defaultValue={motifRejet}
          onChange={(e) => setMotifRejet(e.target.value as MotifRejet)}
        >
          <option value="est_bailleur">
            Le requérant est le bailleur (art. 1732)
          </option>
          <option value="est_vise">
            Le requérant était visé par l'opération
          </option>
          <option value="est_hebergeant">
            Le requérant hébergeait la personne visé par l'opération
          </option>
          <option value="autre">Autre raison</option>
        </select>
      </div>
    </>
  );
};

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

export const DeciderModale = function ({
  dossier,
  agent,
  onDecide,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onDecide?: () => void;
}) {
  const etatDecision = useSnapshot(store);

  const [courrier, setCourrier] = useState<Document | null>(
    dossier.getCourrierAJour(),
  );

  const [declarationAcceptation, setDeclarationAcceptation] =
    useState<Document | null>(
      dossier.getDocumentType(DocumentType.TYPE_COURRIER_REQUERANT),
    );

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState(courrier?.metaDonnees?.montantIndemnisation ?? 100);

  // Mémorise le motif de rejet
  const [motifRejet, setMotifRejet]: [
    MotifRejet | null,
    (motif: MotifRejet) => void,
  ] = useState<MotifRejet>(
    (courrier?.metaDonnees?.motifRejet ?? dossier.qualiteRequerant == "PRO")
      ? "est_bailleur"
      : dossier.testEligibilite.estVise
        ? "est_vise"
        : dossier.testEligibilite.estHebergeant
          ? "est_hebergeant"
          : "autre",
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

  const annuler = () => fermerModale();

  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerImpl);

  const genererCourrierPropositionIndemnisation = useCallback(
    async (dossier: DossierDetail, montantIndemnisation: number) => {
      setGenerationEnCours(true);
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

  const genererDeclarationAcceptation = useCallback(
    async (dossier: DossierDetail, montantIndemnisation: number) => {
      setGenerationEnCours(true);
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
    async ({
      etatDecision,
      courrier,
    }: {
      etatDecision: EtatDecision;
      courrier: Document;
    }) => {
      setSauvegarderEnCours(true);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/decider.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(
            etatDecision.decision
              ? {
                  indemnisation: true,
                  montantIndemnisation: etatDecision.montantIndemnisation,
                }
              : { indemnisation: false, motif: etatDecision.motifRejet },
          ),
        },
      );

      if (response.ok) {
        dossier.montantIndemnisation = etatDecision.montantIndemnisation;
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
        // TODO améliorer ça en gérant le cas des documents multiple ou non
        dossier.viderDocumentParType(DocumentType.TYPE_ARRETE_PAIEMENT);
        dossier.addDocument(courrier);
      }

      fermerModale();

      setSauvegarderEnCours(false);

      // Déclencher le hook `onDecide` s'il est défini
      onDecide?.();
    },
    [dossier],
  );

  return estEnAttenteDecision({ dossier, agent }) ? (
    <_modale.Component
      title={
        etatDecision.etape.nom.endsWith("REJET")
          ? " Rejeter la demande d'indemnisation"
          : " Accepter la demande d'indemnisation"
      }
      iconId={
        etatDecision.etape.nom.endsWith("REJET")
          ? "fr-icon-close-circle-line"
          : "fr-icon-checkbox-circle-line"
      }
      size="large"
    >
      <Stepper
        currentStep={etatDecision.etape?.rang}
        stepCount={etatDecision.etape?.longueur}
        title={etatDecision.etape?.titre}
        nextTitle={etatDecision.etape?.suivant?.titre}
      />

      {/* Choix du motif de rejet */}
      {etatDecision.etape.nom === "CHOIX_MOTIF_REJET" && (
        <>
          <DefinirMotifRefus
            motifRejet={etatDecision.motifRejet}
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
                onClick: () => fermerModale(),
              },
              {
                disabled: !motifRejet,
                priority: "primary",
                iconId: "fr-icon-edit-box-line",
                onClick: () => {
                  const regenererDocument =
                    etatDecision.decision ||
                    motifRejet !== etatDecision.motifRejet;

                  definirMotifRejet(motifRejet);
                  versEtape(EtapeDecision.EDITION_COURRIER_REJET);

                  if (regenererDocument) {
                    genererCourrierRejet(dossier, motifRejet);
                  }
                },
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {/* Choix du montant de l'indemnisation */}
      {etatDecision.etape.nom === "CHOIX_MONTANT_INDEMNISATION" && (
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
                onClick: () => fermerModale(),
              },
              {
                disabled: !montantIndemnisation,
                priority: "secondary",
                iconId: "fr-icon-edit-box-line",
                onClick: () => {
                  const regenererDocument =
                    !etatDecision.decision ||
                    montantIndemnisation !== etatDecision.montantIndemnisation;

                  definirMontantIndemnisation(montantIndemnisation);
                  versEtape(EtapeDecision.EDITION_COURRIER_PI);

                  if (regenererDocument)
                    genererCourrierPropositionIndemnisation(
                      dossier,
                      montantIndemnisation,
                    );
                },
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {etatDecision.etape.nom === "EDITION_COURRIER_REJET" && (
        <>
          {generationEnCours ? (
            <>Génération en cours...</>
          ) : (
            <EditeurDocument
              className="fr-my-2w"
              document={courrier as Document}
              onImprime={(document: Document) => dossier.addDocument(document)}
              onImpression={(impressionEnCours) =>
                setSauvegarderEnCours(impressionEnCours)
              }
            />
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
                onClick: () => versEtape(EtapeDecision.CHOIX_MOTIF_REJET),
              },
              {
                disabled: !courrier,
                iconId: "fr-icon-send-plane-line",
                onClick: () => versEtape(EtapeDecision.VERIFICATION_REJET),
                children: "Valider et vérifier",
              },
            ]}
          />
        </>
      )}

      {etatDecision.etape.nom === "EDITION_COURRIER_PI" && (
        <>
          {generationEnCours ? (
            <>Génération en cours...</>
          ) : (
            <EditeurDocument
              className="fr-my-2w"
              document={courrier as Document}
              onImprime={(document: Document) => {
                setDeclarationAcceptation(document);
                dossier.addDocument(document);
              }}
              onImpression={(impressionEnCours) =>
                setSauvegarderEnCours(impressionEnCours)
              }
            />
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
                onClick: () =>
                  versEtape(EtapeDecision.CHOIX_MONTANT_INDEMNISATION),
              },
              {
                disabled: !courrier,
                iconId: "fr-icon-edit-box-line",
                onClick: () => {
                  if (
                    !declarationAcceptation ||
                    declarationAcceptation.metaDonnees?.montant !==
                      etatDecision.montantIndemnisation
                  ) {
                    genererDeclarationAcceptation(
                      dossier,
                      etatDecision.montantIndemnisation,
                    );
                  }

                  versEtape(EtapeDecision.EDITION_DECLARATION_ACCEPTATION);
                },
                children: "Éditer la déclaration d'acceptation",
              },
            ]}
          />
        </>
      )}

      {etatDecision.etape.nom === "EDITION_DECLARATION_ACCEPTATION" && (
        <>
          {generationEnCours ? (
            <>Génération de la déclaration en cours...</>
          ) : (
            <EditeurDocument
              className="fr-my-2w"
              document={declarationAcceptation as Document}
              onImprime={(document: Document) => dossier.addDocument(document)}
              onImpression={(impressionEnCours) =>
                setSauvegarderEnCours(impressionEnCours)
              }
            />
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
                onClick: () => versEtape(EtapeDecision.EDITION_COURRIER_PI),
              },
              {
                disabled: !courrier,
                iconId: "fr-icon-edit-box-line",
                onClick: () => versEtape(EtapeDecision.VERIFICATION_PI),
                children: "Éditer la déclaration d'acceptation",
              },
            ]}
          />
        </>
      )}

      {etatDecision.etape.nom === "VERIFICATION_PI" && <>Vérification PI</>}

      {etatDecision.etape.nom === "VERIFICATION_REJET" && (
        <>Vérification rejet</>
      )}
    </_modale.Component>
  ) : (
    <></>
  );
};

export const deciderBoutons = ({
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
            versEtape(EtapeDecision.CHOIX_MOTIF_REJET);
            ouvrirModale();
          },
        } as ButtonProps,
        {
          children: "Accepter",
          priority: "primary",
          disabled: false,
          iconId: "fr-icon-check-line",
          onClick: () => {
            versEtape(EtapeDecision.CHOIX_MONTANT_INDEMNISATION);
            ouvrirModale();
          },
        } as ButtonProps,
      ]
    : [];
};
