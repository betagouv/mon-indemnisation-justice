import "@/apps/_init.ts";
import React, { useReducer } from "react";
import ReactDOM from "react-dom/client";
import BrisPortePanel from "@/apps/requerant/dossier/components/BrisPortePanel";
import {
  DossierContext,
  PatchDossierContext,
} from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import {
  Pays,
  PaysContext,
} from "@/apps/requerant/dossier/contexts/PaysContext.ts";
import _ from "lodash";
import {
  recursiveMerge,
  recursivePatch,
} from "@/apps/requerant/dossier/services/Object";

import * as Sentry from "@sentry/browser";

const { dossier, pays }: { dossier: any; pays: Pays[] } = JSON.parse(
  document.getElementById("react-arguments")?.textContent ?? "{}",
);

pays.sort((pays1: Pays, pays2: Pays) => {
  if (pays1.code === "FRA") {
    return -1;
  }

  if (pays2.code === "FRA") {
    return 1;
  }

  return pays1.code.localeCompare(pays2.code);
});

// Pré-population des listes de documents par type
const documents = {
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
};

for (const [type, liste] of Object.entries(documents)) {
  if (!dossier.documents.hasOwnProperty(type)) {
    dossier.documents[type] = liste;
  }
}

const root = ReactDOM.createRoot(
  document.getElementById("react-app") as HTMLElement,
);

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
    // TODO : passer cet appel an await et gérer l'erreur (Sentry + message utilisateur)
    // Run a PATCH call and store the result as state
    fetch(`/api-v1/requerant/dossier/${dossier.id}`, {
      method: "PATCH",
      redirect: "error",
      headers: { "Content-Type": "application/merge-patch+json" },
      body: JSON.stringify(queuedChanges),
    })
      .then((response) => {
        queuedChanges = {};
        window.appPendingChanges = false;
      })
      .catch((e) => {
        Sentry.captureException(e);
      });
  }
};

const debouncedApiPatch = _.debounce(apiPatch, 250);

function DossierApp({ dossier }) {
  const [_dossier, _patchDossier]: [any, (any) => void] = useReducer(
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
