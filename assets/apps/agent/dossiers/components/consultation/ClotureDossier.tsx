import {
  Agent,
  BaseDossier,
  DossierDetail,
  EtatDossier,
} from "@/apps/agent/dossiers/models";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";

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

interface EtatCloture {
  etat?: "preselection" | "edition" | "sauvegarde";
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

console.log(Array.from(RaisonsCloture.entries()));

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

  const valider = async () => {
    setEtatCloture({ ...etatCloture, etat: "sauvegarde" } as EtatCloture);

    const etatDossier = await cloturer({ dossier, motif: "", explication: "" });

    if (etatDossier) {
      dossier.changerEtat(etatDossier);
    }

    setEtatCloture({} as EtatCloture);
  };

  return (
    <>
      {etatCloture.etat === "preselection" && (
        <div className="fr-col-12 fr-grid-row fr-grid-row--gutters">
          <div className="fr-select-group fr-col-offset-6 fr-col-lg-6 fr-mb-0">
            <label className="fr-label" htmlFor="dossier-select-attributaire">
              Choix du motif
            </label>
            <select
              className="fr-select"
              id="dossier-cloture-preselection"
              //disabled={etatCloture.etat === "sauvegarde"}
              defaultValue={""}
              onChange={(e) => {
                console.log(e.target.value);
              }}
            >
              <option value="" disabled hidden>
                Sélectionnez un motif existant
              </option>

              {Array.from(RaisonsCloture.entries()).map(([libelle, raison]) => (
                <option value={libelle} key={`raison-cloture-${libelle}`}>
                  {raison.libelle}
                </option>
              ))}
            </select>
          </div>

          <div className="fr-input-group fr-col-lg-6" id="input-group-69">
            <label className="fr-label" htmlFor="input-55">
              {" "}
              libellé input{" "}
            </label>
            <input
              className="fr-input"
              aria-describedby="input-55-messages"
              id="input-55"
              type="text"
            />
            <div
              className="fr-messages-group"
              id="input-55-messages"
              aria-live="polite"
            ></div>
          </div>

          <div className="fr-input-group fr-col-lg-6" id="input-group-82">
            <label className="fr-label" htmlFor="input-68">
              {" "}
              libellé input{" "}
            </label>
            <textarea
              className="fr-input"
              aria-describedby="input-68-messages"
              id="input-68"
              rows={5}
            ></textarea>
            <div
              className="fr-messages-group"
              id="input-68-messages"
              aria-live="polite"
            ></div>
          </div>
        </div>
      )}

      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
        {etatCloture.etat == null ? (
          <>
            <li>
              <button
                className="fr-btn fr-btn--sm fr-btn--secondary"
                type="button"
                onClick={() =>
                  setEtatCloture({
                    ...etatCloture,
                    etat: "preselection",
                  } as EtatCloture)
                }
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
                disabled={etatCloture.etat === "sauvegarde"}
                onClick={() =>
                  setEtatCloture({
                    ...etatCloture,
                    etat: null,
                  } as EtatCloture)
                }
              >
                {etatCloture.etat === "sauvegarde" ? (
                  <i>Sauvegarde en cours ...</i>
                ) : (
                  <>Annuler</>
                )}
              </button>
            </li>
            <li>
              <button
                className="fr-btn fr-btn--sm"
                type="button"
                disabled={etatCloture.etat === "sauvegarde"}
                onClick={() => console.log("Clôturer")}
              >
                Clôturer
              </button>
            </li>
          </>
        )}
      </ul>
    </>
  );
});
