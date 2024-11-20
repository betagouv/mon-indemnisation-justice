import React, {useReducer, useState} from 'react';
import ReactDOM from "react-dom/client";
import BrisPortePanel from '../../react/components/BrisPortePanel.jsx';
import { DossierContext, PatchDossierContext } from '../../react/contexts/DossierContext.ts';
import Routing from 'fos-router';
import routes  from '../../../public/js/fos_js_routes.json';
import _ from "lodash";

Routing.setRoutingData(routes);

declare global {
    interface Window { Routing: any; }
}

window.Routing = Routing;

import '../../styles/authentification.css';

const { dossier } = JSON.parse(document.getElementById('react-arguments').textContent);


const root = ReactDOM.createRoot(document.getElementById('react-app'));


function DossierApp({dossier}) {
    console.log(dossier);

    const [_dossier, _patchDossier] = useReducer((dossier: object, update: object) => {
        // Il faut recréer un objet pour que le re-render soit déclenché
        return {
            ..._.merge(
                dossier, update
            )
        };
    }, dossier);



    // Async function that reaches the backend
    // TODO give a try to https://www.npmjs.com/package/@bitovi/use-simple-reducer
    const _patchDossierAsync = (update: object) => {
        console.log(update);
        _patchDossier(update);
        // Run a PATCH call and store the result as state
        fetch(`/api/requerant/dossier/${_dossier.id}`, {
            method: 'PATCH',
            redirect: 'error',
            headers: {'Content-Type': 'application/merge-patch+json'},
            body: JSON.stringify(update)
        }).then(
            (response) => response.json().then(
                (dossier) => {}//console.log(dossier);_patchDossier(dossier)
            )
        );
    }

    return (
        <DossierContext.Provider value={_dossier} >
            <PatchDossierContext.Provider value={_patchDossierAsync} >
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