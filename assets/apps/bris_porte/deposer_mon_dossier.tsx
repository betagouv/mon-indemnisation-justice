import React, {useReducer} from 'react';
import ReactDOM from "react-dom/client";
import { disableReactDevTools } from '@/react/services/devtools.js';
import BrisPortePanel from '@/react/components/BrisPortePanel.jsx';
import { DossierContext, PatchDossierContext } from '@/react/contexts/DossierContext.ts';
import _ from "lodash";
import {recursiveMerge, recursivePatch} from "@/react/services/Object.js";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on(
    "vite:beforeUpdate",
    () => console.clear()
  );
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
    disableReactDevTools();
}

const { dossier } = JSON.parse(document.getElementById('react-arguments').textContent);

const root = ReactDOM.createRoot(document.getElementById('react-app'));

let queuedChanges = {};


const apiPatch = () => {
    if (Object.keys(queuedChanges).length > 0) {
        // Run a PATCH call and store the result as state
        fetch(`/api/requerant/dossier/${dossier.id}`, {
            method: 'PATCH',
            redirect: 'error',
            headers: {'Content-Type': 'application/merge-patch+json'},
            body: JSON.stringify(queuedChanges)
        }).then(
            (response) => {
                queuedChanges = {};
            }
        )//.catch((e) => alert(e))
    }
}

const debouncedApiPatch = _.debounce(apiPatch, 500);


function DossierApp({dossier}) {
    const [_dossier, _patchDossier] = useReducer((dossier: object, changes: any) => {
        const { patch = true, ...diff } = changes;

        if (patch) {
            // Ajouter les changements à la file d'attente
            queuedChanges = recursiveMerge(queuedChanges, diff);
            debouncedApiPatch();
        }

        // Il faut recréer un objet pour que le re-render soit déclenché
        return recursivePatch(dossier, diff);
    }, dossier);

    return (
        <DossierContext.Provider value={_dossier} >
            <PatchDossierContext.Provider value={_patchDossier} >
                <div className="fr-container">
                    <h1>Déclarer un bris de porte</h1>
                    <BrisPortePanel />
                </div>
            </PatchDossierContext.Provider>
        </DossierContext.Provider>
    )
}

root.render(
    <React.StrictMode>
        <>
            <DossierApp dossier={dossier} />
        </>
    </React.StrictMode>
);