import {ButtonProps} from "@codegouvfr/react-dsfr/Button";
import {plainToInstance} from "class-transformer";
import React, {FormEvent, useState} from "react";

import {Agent, Courrier, DossierDetail, EtatDossier,} from "@/apps/agent/dossiers/models";
import {createModal} from "@codegouvfr/react-dsfr/Modal";
import {observer} from "mobx-react-lite";
import ReactQuill from "react-quill-new";

const _modale = createModal({
  id: "modale-action-decider",
  isOpenedByDefault: false,
});

type MotifRefus = "est_vise" | "est_hebergeant" | "autre";

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
  decision = undefined,
  onDecide,
}: {
  dossier: DossierDetail;
  agent: Agent;
  decision?: boolean;
  onDecide?: () => void;
}) {
  // Corps du courrier
  const [courrier, editerCourrier]: [
    string | null,
    (courrier: string) => void,
  ] = useState(null);

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

  const genererCourrier = async ({
    indemnisation,
    montant,
    motifRefus,
  }: {
    indemnisation: boolean;
    montant?: number;
    motifRefus?: MotifRefus;
  }) => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/courrier/generer.html`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "text/html",
        },
        body: JSON.stringify({
          indemnisation,
          ...(montant ? { montantIndemnisation: montant } : {}),
          ...(motifRefus ? { motifRefus } : {}),
        }),
      },
    );

    if (response.ok) {
      dossier.montantIndemnisation = montant;
      editerCourrier(await response.text());
    }

    setSauvegarderEnCours(false);
  };

  const deciderDossier = async ({
    indemnisation,
    courrier,
    montant,
    motif,
  }: {
    indemnisation: boolean;
    courrier: string;
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
          corpsCourrier: courrier,
          ...(montant ? { montantIndemnisation: montant } : {}),
          ...(motif ? { motif } : {}),
        }),
      },
    );

    if (response.ok) {
      dossier.montantIndemnisation = montant;
      const data = await response.json();
      dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
      dossier.setCourrier(plainToInstance(Courrier, data.courrier));
    }

    _modale.close();

    setSauvegarderEnCours(false);

    // Déclencher le hook `onDecide` s'il est défini
    onDecide?.();
  };

  return estEnAttenteDecision({ dossier, agent }) && dossier.enInstruction() ? (
    <_modale.Component
      title={decision ? "Accepter l'indemnisation" : "Rejeter l'indemnisation"}
      size="large"
    >
      {decision ? (
        <>
          {!courrier && (
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
          )}

          {courrier && (
            <div className="fr-col-12">
              <ReactQuill
                theme="snow"
                modules={{
                  toolbar: [
                    ["bold", "italic", "underline", "strike", "blockquote"],
                    [
                      { list: "ordered" },
                      { list: "bullet" },
                      { indent: "-1" },
                      { indent: "+1" },
                    ],
                    ["link"],
                  ],
                }}
                formats={[
                  "bold",
                  "italic",
                  "underline",
                  "strike",
                  "blockquote",
                  "list",
                  "indent",
                  "link",
                ]}
                value={courrier}
                onChange={editerCourrier}
                readOnly={sauvegarderEnCours}
              />
            </div>
          )}

          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                onClick={() => _modale.close()}
                disabled={sauvegarderEnCours}
              >
                {sauvegarderEnCours ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  <>Annuler</>
                )}
              </button>
            </li>
            {null === courrier ? (
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--primary"
                  type="button"
                  onClick={() =>
                    genererCourrier({
                      indemnisation: true,
                      montant: montantIndemnisation,
                    })
                  }
                  disabled={!montantIndemnisation || sauvegarderEnCours}
                >
                  Rédiger le courrier
                </button>
              </li>
            ) : (
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--primary"
                  type="button"
                  onClick={() =>
                    deciderDossier({
                      indemnisation: true,
                      montant: montantIndemnisation,
                      courrier,
                    })
                  }
                  disabled={!courrier.trim().length || sauvegarderEnCours}
                >
                  Confirmer l'indemnisation
                </button>
              </li>
            )}
          </ul>
        </>
      ) : (
        <>
          {!courrier && (
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
          )}
          {courrier && (
            <div className="fr-col-12">
              <ReactQuill
                theme="snow"
                value={courrier}
                modules={{
                  toolbar: [
                    ["bold", "italic", "underline", "strike", "blockquote"],
                    [
                      { list: "ordered" },
                      { list: "bullet" },
                      { indent: "-1" },
                      { indent: "+1" },
                    ],
                    ["link"],
                  ],
                }}
                formats={[
                  "bold",
                  "italic",
                  "underline",
                  "strike",
                  "blockquote",
                  "list",
                  "indent",
                  "link",
                ]}
                onChange={editerCourrier}
                readOnly={sauvegarderEnCours}
              />
            </div>
          )}
          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                onClick={() => _modale.close()}
                disabled={sauvegarderEnCours}
              >
                {sauvegarderEnCours ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  <>Annuler</>
                )}
              </button>
            </li>
            {null === courrier ? (
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--primary"
                  type="button"
                  onClick={() =>
                    genererCourrier({
                      indemnisation: false,
                      motifRefus: motifRejet,
                    })
                  }
                  disabled={!motifRejet || sauvegarderEnCours}
                >
                  Rédiger le courrier
                </button>
              </li>
            ) : (
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--primary"
                  type="button"
                  onClick={() =>
                    deciderDossier({
                      indemnisation: false,
                      courrier,
                      motif: motifRejet,
                    })
                  }
                  disabled={!courrier.trim().length || sauvegarderEnCours}
                >
                  Confirmer le rejet
                </button>
              </li>
            )}
          </ul>
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
  sauvegardeEnCours,
  setSauvegardeEnCours,
  setDecision,
}: {
  dossier: DossierDetail;
  agent: Agent;
  sauvegardeEnCours: boolean;
  setSauvegardeEnCours: (boolean) => void;
  setDecision: (boolean) => void;
}): ButtonProps[] => {
  return estEnAttenteDecision({ dossier, agent })
    ? dossier.enInstruction()
      ? [
          {
            children: "Rejeter",
            priority: "secondary",
            iconId: "fr-icon-close-line",
            onClick: () => {
              setDecision(false);
              _modale.open();
            },
          } as ButtonProps,
          {
            children: "Accepter",
            priority: "primary",
            iconId: "fr-icon-check-line",
            onClick: () => {
              setDecision(true);
              _modale.open();
            },
          } as ButtonProps,
        ]
      : [
          {
            children: "Démarrer l'instruction",
            priority: "primary",
            disabled: sauvegardeEnCours,
            iconId: "fr-icon-play-line",
            onClick: async () => {
              setSauvegardeEnCours(true);
              await demarrerInstruction({ dossier });
              setSauvegardeEnCours(false);
            },
          } as ButtonProps,
        ]
    : [];
};
