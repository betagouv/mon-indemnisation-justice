import { EtatDossier } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";

export const ValidationDossier = observer(function ValidationDossierComponent({
  dossier,
}: {
  dossier: DossierDetail;
}) {
  // Mémorise la décision en cours
  const [decision, decider]: [
    boolean | null,
    (decision: boolean | null) => void,
  ] = useState(null);

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState(dossier.montantIndemnisation);

  // Mémorise le motif de rejet
  const [motifRejet, setMotifRejet]: [
    string | null,
    (motif: string | null) => void,
  ] = useState();

  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegarderEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const valider = async (montant?: number, motif?: string) => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/valider/confirmer.json`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          ...(montant ? { montant } : {}),
          ...(motif ? { motif } : {}),
        }),
      },
    );

    if (response.ok) {
      dossier.montantIndemnisation = montant;

      dossier.changerEtat(
        dossier.estAccepte()
          ? EtatDossier.OK_A_SIGNER
          : EtatDossier.KO_A_SIGNER,
      );
    } // TODO afficher un message en cas d'erreur

    setSauvegarderEnCours(false);
    decider(null);
  };

  const refuser = async (motif?: string) => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/valider/decliner.json`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          motif,
        }),
      },
    );

    if (response.ok) {
      dossier.changerEtat(EtatDossier.KO_A_VALIDER);
    } // TODO afficher un message en cas d'erreur

    setSauvegarderEnCours(false);
    decider(null);
  };

  return (
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
                Décliner
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
                onClick={() => decider(true)}
              >
                Valider la décision
              </button>
            </li>
          </ul>
        </div>
      )}
      {decision == true && (
        <>
          {dossier.estAccepte() && (
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
                  pattern="[0-9]*"
                  inputMode="numeric"
                  value={montantIndemnisation}
                  onKeyDown={(e: KeyboardEvent) => {
                    if (e.key.length == 1 && !e.key.match(/\d/)) {
                      e.preventDefault();
                    }
                  }}
                  onInput={(e) =>
                    setMontantIndemnisation(parseInt(e.target.value))
                  }
                  aria-describedby="dossier-decision-acceptation-indemnisation-messages"
                  id="dossier-decision-acceptation-indemnisation-champs"
                  type="number"
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

          {dossier.estRejete() && (
            <div className="fr-input-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
              <label
                className="fr-label"
                htmlFor="dossier-decision-acceptation-motif-champs"
              >
                Motif du refus
              </label>
              <div className="fr-input-wrap">
                <input
                  className="fr-input"
                  id="dossier-decision-acceptation-motif-champs"
                  type="text"
                  onInput={(e) => setMotifRejet(e.target.value || null)}
                />
              </div>
            </div>
          )}

          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                onClick={() => decider(null)}
                disabled={sauvegarderEnCours}
              >
                {sauvegarderEnCours ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  <>Annuler</>
                )}
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
                onClick={() => valider(montantIndemnisation, motifRejet)}
                disabled={
                  (dossier.estAccepte() && !montantIndemnisation) ||
                  sauvegarderEnCours
                }
              >
                {dossier.estAccepte() ? (
                  <>Valider l'indemnisation</>
                ) : (
                  <>Confirmer le rejet</>
                )}
              </button>
            </li>
          </ul>
        </>
      )}
      {decision == false && (
        <>
          <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                onClick={() => decider(null)}
                disabled={sauvegarderEnCours}
              >
                {sauvegarderEnCours ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  <>Annuler</>
                )}
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--primary"
                type="button"
                onClick={() => refuser(motifRejet)}
                disabled={sauvegarderEnCours}
              >
                Confirmer le rejet
              </button>
            </li>
          </ul>
        </>
      )}
    </>
  );
});
