import {
  Agent,
  BaseDossier,
  DossierDetail,
  EtatDossier,
} from "@/apps/agent/dossiers/models";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { ChangeEvent, useState } from "react";

const cloturer = async ({
  dossier,
  motif,
  explication,
}: {
  dossier: BaseDossier;
  motif: string;
  explication: string;
}): Promise<null | EtatDossier> => {
  const response = await fetch(
    `/agent/redacteur/dossier/${dossier.id}/cloturer.json`,
    {
      method: "POST",
      headers: {
        "Content-type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        motif,
        explication,
      }),
    },
  );

  if (response.ok) {
    const data = await response.json();
    return plainToInstance(EtatDossier, data.etat);
  }

  return null;
};

type ActionCloture = "preselection" | "edition" | "sauvegarde";
interface EtatCloture {
  action?: ActionCloture;
  motif?: string;
  explication?: string;
}

interface RaisonCloture {
  libelle: string;
  motif: string;
  explication: string;
}

const RaisonsCloture: Map<string, RaisonCloture> = new Map<
  string,
  RaisonCloture
>([
  [
    "doublon-papier",
    {
      libelle: "Doublon papier",
      motif: "Doublon d'un dossier déposé en version papier",
      explication:
        "Un exemplaire de ce dossier a déjà été déposé par voie postale auparavant. Le traitement continuera en dehors de la plateforme, si bien que le dossier initié en ligne est désormais clos et ne recevra plus de mise à jour à l'avenir.",
    } as RaisonCloture,
  ],
  [
    "libre",
    {
      libelle: "Personnalisé",
      motif: "",
      explication: "",
    } as RaisonCloture,
  ],
]);

export const ClotureDossier = observer(function ClotureDossierComponent({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) {
  // Indique l'état de l'action de clôture :
  const [etatCloture, setEtatCloture]: [
    EtatCloture,
    (etat: EtatCloture) => void,
  ] = useState({} as EtatCloture);

  const selectionModele = (modele: RaisonCloture) =>
    setEtatCloture({
      ...etatCloture,
      motif: modele.motif,
      explication: modele.explication,
    } as EtatCloture);

  const setAction = (action?: ActionCloture) =>
    setEtatCloture({ ...etatCloture, action } as EtatCloture);

  const annuler = () => setAction(null);

  const valider = async (motif: string, explication: string) => {
    setAction("sauvegarde");

    const etatDossier = await cloturer({ dossier, motif, explication });

    if (etatDossier) {
      dossier.changerEtat(etatDossier);
    }

    setEtatCloture({} as EtatCloture);
  };

  return (
    <>
      {etatCloture.action && (
        <div className="fr-col-12 fr-grid-row fr-grid-row--gutters fr-mt-3w fr-px-2w">
          <div className="fr-alert fr-alert--info fr-alert--sm">
            <p>
              Choisissez un modèle prédéfini de motif & explication, ou optez
              pour <q>Personnalisé</q> pour rédiger vous mêmes ces valeurs.
            </p>
          </div>
          {etatCloture.action === "preselection" && (
            <div className="fr-select-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
              <label
                className="fr-label"
                htmlFor="dossier-cloture-preselection"
              >
                Choix du motif
              </label>
              <select
                className="fr-select"
                id="dossier-cloture-preselection"
                defaultValue={""}
                onChange={(e: ChangeEvent<HTMLSelectElement>) =>
                  selectionModele(RaisonsCloture.get(e.target.value))
                }
              >
                <option value="" disabled hidden>
                  Sélectionnez un motif existant
                </option>

                {Array.from(RaisonsCloture.entries()).map(
                  ([libelle, raison]) => (
                    <option value={libelle} key={`raison-cloture-${libelle}`}>
                      {raison.libelle}
                    </option>
                  ),
                )}
              </select>
            </div>
          )}

          {["edition", "sauvegarde"].includes(etatCloture.action) && (
            <>
              <div className="fr-col-12 fr-btns-group fr-btns-group--inline fr-btns-group--right">
                <button
                  className="fr-btn fr-btn--tertiary-no-outline"
                  disabled={etatCloture.action === "sauvegarde"}
                  onClick={() => setAction("preselection")}
                >
                  Choix du modèle
                </button>
              </div>
              <div className="fr-input-group fr-col-lg-6">
                <label className="fr-label" htmlFor="dossier-cloture-motif">
                  Motif
                  <span className="fr-hint-text">
                    Destiné aux agents uniquement
                  </span>
                </label>
                <input
                  className="fr-input"
                  aria-describedby="dossier-cloture-motif-message"
                  defaultValue={etatCloture.motif}
                  disabled={etatCloture.action === "sauvegarde"}
                  onChange={(e: ChangeEvent<HTMLInputElement>) =>
                    setEtatCloture({
                      ...etatCloture,
                      motif: e.target.value,
                    })
                  }
                  id="dossier-cloture-motif"
                  type="text"
                />
                {!etatCloture.motif && (
                  <div
                    className="fr-messages-group fr-message--error"
                    id="dossier-cloture-motif-message"
                    aria-live="polite"
                  >
                    Le motif est requis
                  </div>
                )}
              </div>

              <div className="fr-input-group fr-col-lg-6" id="input-group-82">
                <label
                  className="fr-label"
                  htmlFor="dossier-cloture-explication"
                >
                  Explication détaillée
                  <span className="fr-hint-text">Destinée au requérant</span>
                </label>
                <textarea
                  className="fr-input"
                  aria-describedby="dossier-cloture-explication-message"
                  defaultValue={etatCloture.explication}
                  disabled={etatCloture.action === "sauvegarde"}
                  onChange={(e: ChangeEvent<HTMLTextAreaElement>) =>
                    setEtatCloture({
                      ...etatCloture,
                      explication: e.target.value,
                    })
                  }
                  id="dossier-cloture-explication"
                  rows={5}
                ></textarea>
                {!etatCloture.explication && (
                  <div
                    className="fr-messages-group fr-message--error"
                    id="dossier-cloture-explication-message"
                    aria-live="polite"
                  >
                    L'explication au requérant est requise
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      )}

      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
        {etatCloture.action == null ? (
          <>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--secondary"
                type="button"
                onClick={() => setAction("preselection")}
              >
                Clôturer
              </button>
            </li>
          </>
        ) : (
          <>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                type="button"
                disabled={etatCloture.action === "sauvegarde"}
                onClick={() => annuler()}
              >
                {etatCloture.action === "sauvegarde" ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  <>Annuler</>
                )}
              </button>
            </li>
            {etatCloture.action === "preselection" && (
              <li>
                <button
                  className="fr-btn fr-btn--sm"
                  type="button"
                  onClick={() => setAction("edition")}
                >
                  Sélectionner
                </button>
              </li>
            )}
            {["edition", "sauvegarde"].includes(etatCloture.action) && (
              <li>
                <button
                  className="fr-btn fr-btn--sm"
                  type="button"
                  disabled={
                    !etatCloture.motif ||
                    !etatCloture.explication ||
                    etatCloture.action === "sauvegarde"
                  }
                  onClick={() =>
                    valider(etatCloture.motif, etatCloture.explication)
                  }
                >
                  Valider la clôture
                </button>
              </li>
            )}
          </>
        )}
      </ul>
    </>
  );
});
