import React, { useReducer } from "react";
import ReactDOM from "react-dom/client";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";
import BrisPortePanel from "@/apps/requerant/dossier/components/BrisPortePanel";
import {
  DossierContext,
  PatchDossierContext,
} from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import { PaysContext } from "@/apps/requerant/dossier/contexts/PaysContext.ts";
import _ from "lodash";
import {
  recursiveMerge,
  recursivePatch,
} from "@/apps/requerant/dossier/services/Object";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const { dossier, pays } = {
  ...{
    // Pré-population des listes de documents par type
    dossier: {
      documents: {
        attestation_information: [],
        photo_prejudice: [],
        carte_identite: [],
        facture: [],
        rib: [],
        titre_propriete: [],
        contrat_location: [],
        non_prise_en_charge_bailleur: [],
        non_prise_en_charge_assurance: [],
        courrier_ministere: [],
        courrier_requerant: [],
      },
    },
    ...JSON.parse(document.getElementById("react-arguments").textContent),
  },
};

console.dir(dossier);

const root = ReactDOM.createRoot(document.getElementById("react-app"));

let queuedChanges = {};

// Déclaration d'une variable globale indiquant si des opérations sont en attentes
declare global {
  interface Window {
    appPendingChanges: boolean;
  }
}

window.appPendingChanges = window.appPendingChanges || false;

const apiPatch = () => {
  if (Object.keys(queuedChanges).length > 0) {
    window.appPendingChanges = true;
    // Run a PATCH call and store the result as state
    fetch(`/api/requerant/dossier/${dossier.id}`, {
      method: "PATCH",
      redirect: "error",
      headers: { "Content-Type": "application/merge-patch+json" },
      body: JSON.stringify(queuedChanges),
    }).then((response) => {
      queuedChanges = {};
      window.appPendingChanges = false;
    }); //.catch((e) => alert(e))
  }
};

const debouncedApiPatch = _.debounce(apiPatch, 500);

function DossierApp({ dossier }) {
  const [_dossier, _patchDossier] = useReducer(
    (dossier: object, changes: any) => {
      const { patch = true, ...diff } = changes;

      if (patch) {
        // Ajouter les changements à la file d'attente
        queuedChanges = recursiveMerge(queuedChanges, diff);
        debouncedApiPatch();
      }

      // Il faut recréer un objet pour que le re-render soit déclenché
      return recursivePatch(dossier, diff);
    },
    dossier,
  );

  return (
    <DossierContext.Provider value={_dossier}>
      <PaysContext.Provider value={pays}>
        <PatchDossierContext.Provider value={_patchDossier}>
          <div className="fr-container">
            <h1>Déclarer un bris de porte</h1>
            <BrisPortePanel />
          </div>
        </PatchDossierContext.Provider>
      </PaysContext.Provider>
    </DossierContext.Provider>
  );
}

root.render(
  <React.StrictMode>
    <>
      <DossierApp dossier={dossier} />
    </>
  </React.StrictMode>,
);
