import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { plainToInstance } from "class-transformer";
import React, { createContext, useContext, FormEvent, useState } from "react";

import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
  EtatDossier,
} from "@/apps/agent/dossiers/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { observer } from "mobx-react-lite";
import { makeAutoObservable, makeObservable } from "mobx";
import { EditeurDocument } from "@/apps/agent/dossiers/components/consultation/document/EditeurDocument.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";

const _modale = createModal({
  id: "modale-action-decider",
  isOpenedByDefault: false,
});

type EtapeDecision = "choix_motif" | "choix_montant" | "edition_courrier";
type MotifRefus = "est_vise" | "est_hebergeant" | "autre";

class EtatDecision {
  decision?: boolean;
  etape?: EtapeDecision;
  montantIndemnisation?: number;
  motifRefus?: MotifRefus;
  courrier?: Document;

  constructor() {
    makeAutoObservable(this);
  }

  opterAcceptation() {
    this.decision = true;
    this.etape = "choix_montant";
  }

  async accepter(dossier: DossierDetail, montantIndemnisation?: number) {
    if (
      this.decision !== true ||
      montantIndemnisation !== this.montantIndemnisation
    ) {
      this.decision = true;
      this.montantIndemnisation = montantIndemnisation;

      await this.genererCourrier(dossier);
    }
    this.etape = "edition_courrier";
  }

  opterRejet() {
    this.decision = false;
    this.etape = "choix_motif";
  }

  async rejeter(dossier: DossierDetail, motifRefus?: MotifRefus) {
    if (this.decision !== false || motifRefus !== this.motifRefus) {
      this.decision = false;
      this.motifRefus = motifRefus;

      await this.genererCourrier(dossier);
    }
    this.etape = "edition_courrier";
  }

  annuler() {
    this.decision = null;
  }

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
  const [sauvegarderEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState(100);

  // Mémorise le motif de rejet
  const [motifRejet, setMotifRejet]: [
    MotifRefus | null,
    (motif?: MotifRefus) => void,
  ] = useState(
    dossier.testEligibilite.estVise
      ? "est_vise"
      : dossier.testEligibilite.estHebergeant
        ? "est_hebergeant"
        : "autre",
  );

  const annuler = () => {
    _modale.close();
    etatDecision.annuler();
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
      dossier.addDocument(etatDecision.courrier);
    }

    _modale.close();

    setSauvegarderEnCours(false);

    // Déclencher le hook `onDecide` s'il est défini
    onDecide?.();
  };

  return estEnAttenteDecision({ dossier, agent }) && dossier.enInstruction() ? (
    <_modale.Component
      title={
        etatDecision ? "Accepter l'indemnisation" : "Rejeter l'indemnisation"
      }
      size="large"
    >
      {/* Choix du motif de rejet */}
      {etatDecision.etape === "choix_motif" && (
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
              <option value="est_vise">
                Le requérant était visé par l'opération
              </option>
              <option value="est_hebergeant">
                Le requérant hébergeait la personne visé par l'opération
              </option>
              <option value="autre">Autre raison</option>
            </select>
          </div>
          <ButtonsGroup
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            alignment="right"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => {
                  _modale.close();
                  etatDecision.annuler();
                },
              },
              {
                disabled: !montantIndemnisation,
                onClick: async () =>
                  await etatDecision.rejeter(dossier, motifRejet),
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {/* Choix du montant de l'indemnisation */}
      {etatDecision.etape === "choix_montant" && (
        <>
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
                  setMontantIndemnisation(
                    value?.match(/^\d+(.\d{0,2})?$/)
                      ? parseFloat(value?.replace(",", "."))
                      : null,
                  );
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
          <ButtonsGroup
            inlineLayoutWhen="always"
            buttonsIconPosition="right"
            alignment="right"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => {
                  _modale.close();
                  etatDecision.annuler();
                },
              },
              {
                disabled: !montantIndemnisation,
                onClick: async () =>
                  etatDecision.accepter(dossier, montantIndemnisation),
                children: "Éditer le courrier",
              },
            ]}
          />
        </>
      )}

      {etatDecision.etape === "edition_courrier" && (
        <>
          <EditeurDocument
            className="fr-my-2w"
            dossier={dossier}
            document={etatDecision.courrier}
            onChange={(document: Document) =>
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
                  ? "Changer le montant d'indemnisation"
                  : "Changer le motif de refus",
                priority: "secondary",
                onClick: () => {
                  if (etatDecision.decision) {
                    etatDecision.etape = "choix_montant";
                  } else {
                    etatDecision.etape = "choix_motif";
                  }
                },
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
