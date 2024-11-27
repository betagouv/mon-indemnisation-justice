import React, {useReducer} from 'react';
import ReactDOM from "react-dom/client";
import BrisPortePanel from '@/react/components/BrisPortePanel.jsx';
import { DossierContext, PatchDossierContext } from '@/react/contexts/DossierContext.ts';
import Routing from 'fos-router';
import routes  from '../../../public/js/fos_js_routes.json';
import _ from "lodash";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on(
    "vite:beforeUpdate",
    () => console.clear()
  );
}

Routing.setRoutingData(routes);

declare global {
    interface Window { Routing: any; }
}

window.Routing = Routing;

import '../../styles/authentification.css';

const { dossier } = JSON.parse(document.getElementById('react-arguments').textContent);


const root = ReactDOM.createRoot(document.getElementById('react-app'));

let queuedChanges = {};


const apiPatch = () => {
    if (Object.keys(queuedChanges).length > 0) {
        console.log("Ça patche les petits potches", JSON.stringify(queuedChanges));
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
        )
    }
}

const debouncedApiPatch = _.debounce(apiPatch, 1000);


function DossierApp({dossier}) {
    const [_dossier, _patchDossier] = useReducer((dossier: object, changes: any) => {
        const { patch = true, ...diff } = changes;
        if (patch) {
            // Ajouter les changements à la file d'attente
            queuedChanges = {
                ..._.merge(
                    queuedChanges, diff
                )
            };
            debouncedApiPatch();
        }

        // Il faut recréer un objet pour que le re-render soit déclenché
        return {
            ..._.merge(
                dossier, diff
            )
        };
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