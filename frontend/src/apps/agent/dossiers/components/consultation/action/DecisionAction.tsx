import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { plainToInstance } from "class-transformer";
import React, { Dispatch, FormEvent, SetStateAction, useState } from "react";

import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/common/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { observer } from "mobx-react-lite";
import { makeAutoObservable } from "mobx";
import {
  EditeurDocument,
  EditeurMode,
} from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";

const _modale = createModal({
  id: "modale-action-decider",
  isOpenedByDefault: false,
});

// TODO convertir en class https://stackoverflow.com/a/47443987/4558679 pour connaitre le rang de l'étape
class EtapeDecision {
  public static VERIFICATION_PI = new EtapeDecision(
    4,
    "Vérifier et valider les informations",
  );
  public static EDITION_DECLARATION_ACCEPTATION = new EtapeDecision(
    3,
    "Éditer la déclaration d'acceptation",
    EtapeDecision.VERIFICATION_PI,
  );
  public static EDITION_COURRIER_PI = new EtapeDecision(
    2,
    "Éditer de la proposition d'indemnisation",
    EtapeDecision.EDITION_DECLARATION_ACCEPTATION,
  );
  public static CHOIX_MONTANT_INDEMNISATION = new EtapeDecision(
    1,
    "Définir le montant de l'indemnisation",
    EtapeDecision.EDITION_COURRIER_PI,
  );
  public static VERIFICATION_REJET = new EtapeDecision(
    3,
    "Vérifier et valider les informations",
  );
  public static EDITION_COURRIER_REJET = new EtapeDecision(
    2,
    "Éditer du courrier de rejet",
    EtapeDecision.VERIFICATION_REJET,
  );
  public static readonly CHOIX_MOTIF_REJET = new EtapeDecision(
    1,
    "Définir le motif de refus",
    EtapeDecision.EDITION_COURRIER_REJET,
  );

  private constructor(
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
type MotifRefus = "est_bailleur" | "est_vise" | "est_hebergeant" | "autre";

class EtatDecision {
  decision: boolean;
  etape: EtapeDecision;
  montantIndemnisation?: number;
  motifRefus?: MotifRefus;
  courrier?: Document;

  constructor() {
    makeAutoObservable(this);
  }

  opterAcceptation() {
    this.decision = true;
    this.etape = EtapeDecision.CHOIX_MONTANT_INDEMNISATION;
  }

  async accepter(dossier: DossierDetail, montantIndemnisation?: number) {
    if (!this.decision || montantIndemnisation !== this.montantIndemnisation) {
      this.decision = true;
      this.montantIndemnisation = montantIndemnisation;

      await this.genererCourrier(dossier);
    }

    if (this.etape.suivant) this.etape = this.etape.suivant;
  }

  opterRejet() {
    this.decision = false;
    this.etape = EtapeDecision.CHOIX_MOTIF_REJET;
  }

  async rejeter(dossier: DossierDetail, motifRefus?: MotifRefus) {
    if (this.decision || motifRefus !== this.motifRefus) {
      this.decision = false;
      this.motifRefus = motifRefus;

      await this.genererCourrier(dossier);
    }
    if (this.etape.suivant) this.etape = this.etape.suivant;
  }

  annuler() {}

  setCourrier(courrier: Document): void {
    this.courrier = courrier;
  }

  protected async genererCourrier(dossier: DossierDetail) {
    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/courrier/generer.json`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "text/html",
        },
        body: JSON.stringify({
          indemnisation: this.decision,
          ...(this.montantIndemnisation
            ? { montantIndemnisation: this.montantIndemnisation }
            : {}),
          ...(this.motifRefus ? { motifRefus: this.motifRefus } : {}),
        }),
      },
    );

    if (response.ok) {
      const data = await response.json();

      this.setCourrier(plainToInstance(Document, data));
    }
  }
}

const _etatDecision = new EtatDecision();

const demarrerInstruction = async ({ dossier }: { dossier: DossierDetail }) => {
  const response = await fetch(
    `/agent/redacteur/dossier/${dossier.id}/demarrer-instruction.json`,
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
};

const estEnAttenteDecision = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) =>
  dossier.enAttenteDecision && agent.estRedacteur() && agent.instruit(dossier);

const DefinirMotifRefus = ({
  motifRejet,
  setMotifRejet,
}: {
  motifRejet?: MotifRefus;
  setMotifRejet: Dispatch<SetStateAction<MotifRefus>>;
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
          onChange={(e) => setMotifRejet(e.target.value as MotifRefus)}
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

export const DeciderModale = observer(function DeciderActionModale({
  dossier,
  agent,
  etatDecision = _etatDecision,
  onDecide,
}: {
  dossier: DossierDetail;
  agent: Agent;
  etatDecision?: EtatDecision;
  onDecide?: () => void;
}) {
  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState(100);

  // Mémorise le motif de rejet
  const [motifRejet, setMotifRejet] = useState<MotifRefus>(
    dossier.qualiteRequerant == "PRO"
      ? "est_bailleur"
      : dossier.testEligibilite?.estVise
        ? "est_vise"
        : dossier.testEligibilite?.estHebergeant
          ? "est_hebergeant"
          : "autre",
  );

  // Le mode en cours sur l'éditeur de document
  const [editeurMode, setEditeurMode] = useState<EditeurMode>("edition");

  const annuler = () => {
    _modale.close();
  };

  const deciderDossier = async ({
    indemnisation,
    montant,
    motif,
  }: {
    indemnisation: boolean;
    montant?: number;
    motif?: string;
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
        body: JSON.stringify({
          indemnisation,
          ...(montant ? { montantIndemnisation: montant } : {}),
          ...(motif ? { motif } : {}),
        }),
      },
    );

    if (response.ok) {
      dossier.montantIndemnisation = montant;
      const data = await response.json();
      dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
      // TODO améliorer ça en gérant le cas des documents multiple ou non
      dossier.viderDocumentParType(DocumentType.TYPE_ARRETE_PAIEMENT);
      if (etatDecision.courrier) dossier.addDocument(etatDecision.courrier);
    }

    _modale.close();

    setSauvegarderEnCours(false);

    // Déclencher le hook `onDecide` s'il est défini
    onDecide?.();
  };

  return estEnAttenteDecision({ dossier, agent }) && dossier.enInstruction() ? (
    <_modale.Component
      title={
        etatDecision.decision
          ? " Accepter l'indemnisation"
          : " Rejeter l'indemnisation"
      }
      iconId={
        etatDecision.decision
          ? "fr-icon-checkbox-circle-line"
          : "fr-icon-close-circle-line"
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
      {etatDecision.etape === EtapeDecision.CHOIX_MOTIF_REJET && (
        <>
          <DefinirMotifRefus
            motifRejet={etatDecision.motifRefus}
            setMotifRejet={etatDecision.opterRejet}
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
                  _modale.close();
                },
              },
              {
                disabled: !montantIndemnisation,
                priority: "secondary",
                iconId: "fr-icon-edit-box-line",
                onClick: async () => {
                  await etatDecision.rejeter(dossier, motifRejet);
                  setEditeurMode("edition");
                },
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {/* Choix du montant de l'indemnisation */}
      {etatDecision.etape === EtapeDecision.CHOIX_MONTANT_INDEMNISATION && (
        <>
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
                  _modale.close();
                },
              },
              {
                disabled: !montantIndemnisation,
                priority: "secondary",
                iconId: "fr-icon-edit-box-line",
                onClick: async () => {
                  await etatDecision.accepter(dossier, montantIndemnisation);
                  setEditeurMode("edition");
                },
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {etatDecision.etape === EtapeDecision.EDITION_COURRIER_PI && (
        <>
          <EditeurDocument
            className="fr-my-2w"
            document={etatDecision.courrier}
            onImprime={(document: Document) =>
              etatDecision.setCourrier(document)
            }
            onImpression={(impressionEnCours) =>
              setSauvegarderEnCours(impressionEnCours)
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
                onClick: () => {
                  annuler();
                },
              },
              {
                children: etatDecision.decision
                  ? "Changer le montant"
                  : "Changer le motif de refus",
                priority: "secondary",
                iconId: etatDecision.decision
                  ? "fr-icon-money-euro-circle-line"
                  : "fr-icon-chat-delete-line",
                onClick: () => {
                  if (etatDecision.decision) {
                    etatDecision.etape =
                      EtapeDecision.CHOIX_MONTANT_INDEMNISATION;
                  } else {
                    etatDecision.etape = EtapeDecision.CHOIX_MOTIF_REJET;
                  }
                },
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
                      disabled: !montantIndemnisation || !etatDecision.courrier,
                      iconId: "fr-icon-send-plane-line",
                      onClick: async () =>
                        deciderDossier({
                          indemnisation: etatDecision.decision,
                          ...(etatDecision.decision
                            ? { montant: etatDecision.montantIndemnisation }
                            : {}),
                          ...(etatDecision.decision
                            ? {}
                            : { motif: etatDecision.motifRefus }),
                        }),
                      children: "Valider la décision",
                    },
                  ] as ButtonProps[])),
            ]}
          />
        </>
      )}
    </_modale.Component>
  ) : (
    <></>
  );
});

export const deciderBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
  etat?: EtatDecision;
}): ButtonProps[] => {
  return estEnAttenteDecision({ dossier, agent })
    ? dossier.enInstruction()
      ? [
          {
            children: "Rejeter",
            priority: "secondary",
            disabled: false,
            iconId: "fr-icon-close-line",
            onClick: () => {
              _etatDecision.opterRejet();
              _modale.open();
            },
          } as ButtonProps,
          {
            children: "Accepter",
            priority: "primary",
            disabled: false,
            iconId: "fr-icon-check-line",
            onClick: () => {
              _etatDecision.opterAcceptation();
              _modale.open();
            },
          } as ButtonProps,
        ]
      : [
          {
            children: "Démarrer l'instruction",
            priority: "primary",
            iconId: "fr-icon-play-line",
            onClick: async (e) => {
              (e.target as HTMLButtonElement).disabled = true;
              await demarrerInstruction({ dossier });
            },
          } as ButtonProps,
        ]
    : [];
};
