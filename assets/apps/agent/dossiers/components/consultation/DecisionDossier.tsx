import { Courrier, EtatDossier } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { Administration } from "@/apps/agent/gestion_agents/models";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import ReactQuill from "react-quill-new";
import "react-quill-new/dist/quill.snow.css";
import React, { useRef, useState } from "react";

type MotifRefus = "est_vise" | "est_hebergeant" | "autre";

export const DecisionDossier = observer(function DecisionDossierComponent({
  dossier,
  onDecide,
}: {
  dossier: DossierDetail;
  onDecide: null | (() => void);
}) {
  const refModale = useRef(null);

  // Mémorise la décision en cours
  const [decision, decider]: [
    boolean | null,
    (decision: boolean | null) => void,
  ] = useState(null);

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

  const demarrerInstruction = async () => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/instruction/demarrer.json`,
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
      dossier.changerEtat(data.etat);
      setSauvegarderEnCours(false);
    }
  };

  const ouvrirModaleDecision = (dec: boolean) => {
    decider(dec);
    editerCourrier(null);
    refModale.current?.classList.add("fr-modal--opened");
  };

  const fermerModaleDecision = () => {
    refModale.current?.classList.remove("fr-modal--opened");
    editerCourrier(null);
  };

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
      dossier.changerEtat(data.etat);
      dossier.setCourrier(plainToInstance(Courrier, data.courrier));

      // Déclencher le _hook_ onDecide s'il est défini
      onDecide?.();
    }

    fermerModaleDecision();

    setSauvegarderEnCours(false);
    decider(null);
  };

  return (
    <>
      <div>
        <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
          {dossier.enInstruction() ? (
            <>
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--secondary"
                  type="button"
                  onClick={() => ouvrirModaleDecision(false)}
                >
                  Rejeter
                </button>
              </li>
              <li>
                <button
                  className="fr-btn fr-btn--sm fr-btn--primary"
                  type="button"
                  onClick={() => ouvrirModaleDecision(true)}
                >
                  Accepter
                </button>
              </li>
            </>
          ) : (
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
                disabled={sauvegarderEnCours}
                onClick={() => demarrerInstruction()}
              >
                Démarrer l'instruction
              </button>
            </li>
          )}
        </ul>
      </div>

      <dialog
        aria-labelledby="modale-dossier-decision-titre"
        role="dialog"
        ref={refModale}
        id="modale-dossier-decision"
        data-fr-concealing-backdrop="true"
        className="fr-modal"
      >
        <div className="fr-container fr-container--fluid fr-container-md">
          <div className="fr-grid-row fr-grid-row--center">
            <div className="fr-col-12 fr-col-md-8 fr-col-lg-10">
              <div className="fr-modal__body">
                <div className="fr-modal__header">
                  <button
                    className="fr-btn--close fr-btn"
                    title="Fermer la fenêtre modale"
                    aria-controls="modale-dossier-decision"
                    onClick={() => fermerModaleDecision()}
                  >
                    Fermer
                  </button>
                </div>
                <div className="fr-modal__content">
                  <h1
                    id="modale-dossier-decision-titre"
                    className="fr-modal__title"
                  >
                    <span
                      className={`fr-icon-${decision ? "success" : "error"}-line fr-icon--lg fr-mr-1w`}
                    ></span>
                    {decision ? (
                      <>Accepter l'indemnisation</>
                    ) : (
                      <>Rejeter le dossier</>
                    )}
                  </h1>

                  {decision ? (
                    <>
                      {!courrier && (
                        <div className="fr-input-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
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
                              onInput={(e: InputEvent) => {
                                const input = e.target as HTMLInputElement;

                                setMontantIndemnisation(
                                  input.value?.match(/^\d+(.\d{0,2})?$/)
                                    ? parseFloat(input.value?.replace(",", "."))
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
                              <span>
                                Vous devez définir un montant d'indemnisation
                              </span>
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
                                [
                                  "bold",
                                  "italic",
                                  "underline",
                                  "strike",
                                  "blockquote",
                                ],
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
                            onClick={() => fermerModaleDecision()}
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
                              disabled={
                                !montantIndemnisation || sauvegarderEnCours
                              }
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
                              disabled={
                                !courrier.trim().length || sauvegarderEnCours
                              }
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
                        <div className="fr-select-group fr-col-offset-3 fr-col-lg-9 fr-mb-0">
                          <label
                            className="fr-label"
                            htmlFor="dossier-decision-acceptation-motif-champs"
                          >
                            Motif du refus
                          </label>

                          <select
                            className="fr-select"
                            defaultValue={motifRejet}
                            onChange={(e) => setMotifRejet(e.target.value)}
                          >
                            <option value="est_vise">
                              Le requérant était visé par l'opération
                            </option>
                            <option value="est_hebergeant">
                              Le requérant hébergeait la personne visé par
                              l'opération
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
                                [
                                  "bold",
                                  "italic",
                                  "underline",
                                  "strike",
                                  "blockquote",
                                ],
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
                            onClick={() => fermerModaleDecision()}
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
                              disabled={
                                !courrier.trim().length || sauvegarderEnCours
                              }
                            >
                              Confirmer le rejet
                            </button>
                          </li>
                        )}
                      </ul>
                    </>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </dialog>
    </>
  );
});
