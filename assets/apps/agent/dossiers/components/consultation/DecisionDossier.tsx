import {Agent, EtatDossier, Redacteur} from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import {EtatDossierType} from "@/apps/agent/dossiers/models/EtatDossier";
import { observer } from "mobx-react-lite";
import React, {useRef, useState} from "react";

export const DecisionDossier = observer(function DecisionDossierComponent({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) {

  // Mémorise la décision en cours
  const [decision, decider]: [
    boolean | null,
    (decision: boolean | null) => void,
  ] = useState(null);

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [number, (montant: number) => void] = useState(100);

  const valider = (montant: number) => {
    dossier.changerEtat(EtatDossierType.DOSSIER_ACCEPTE);
    decider(null);
  };

  return agent.estAttribue(dossier) ? (
    <>
      {decision == null && (
        <div>
          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--secondary"
                type="button"
                onClick={() => decider(false)}
              >
                Rejeter
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
                onClick={() => decider(true)}
              >
                Accepter
              </button>
            </li>
          </ul>
        </div>
      )}
      {decision == true && (
        <>
          <div className="fr-input-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
            <label className="fr-label" htmlFor="dossier-decision-acceptation-indemnisation-champs">
              Montant de l'indemnisation
            </label>
            <div className="fr-input-wrap fr-icon-money-euro-circle-line">
              <input
                className="fr-input"
                pattern="[0-9]*"
                inputMode="numeric"
                value={montantIndemnisation}
                onKeyDown={(e: KeyboardEvent) => {
                  if (e.key.length == 1 && !e.key.match(/\d/)) {
                    e.preventDefault()
                  }}}
                onInput={(e) => setMontantIndemnisation(parseInt(e.target.value))}
                aria-describedby="dossier-decision-acceptation-indemnisation-messages"
                id="dossier-decision-acceptation-indemnisation-champs"
                type="number"
              />
            </div>
            {!montantIndemnisation && <div
              className="fr-messages-group fr-message--error fr-my-1w"
              id="dossier-decision-acceptation-indemnisation-messages"
              aria-live="polite"
            >
              <span>Vous devez définir un montant d'indemnisation</span>
            </div>}

          </div>

          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                onClick={() => decider(null)}
              >
                Annuler
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
                disabled={!montantIndemnisation}
                onClick={() => valider(montantIndemnisation)}
              >
                Confirmer l'indemnisation
              </button>
            </li>
          </ul>
        </>
      )}
      {decision == false && (
        <>
          <div className="fr-input-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
            <label className="fr-label" htmlFor="storybook-input">
              Motif du refus
            </label>
            <div className="fr-input-wrap">
              <input className="fr-input" id="" type="text" />
            </div>
          </div>

          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                onClick={() => decider(null)}
              >
                Annuler
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
              >
                Confirmer le refus
              </button>
            </li>
          </ul>
        </>
      )}
    </>
  ) : (
    <></>
  );
});
