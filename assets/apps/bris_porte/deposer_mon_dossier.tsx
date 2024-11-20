import React, {useState} from 'react';
import ReactDOM from "react-dom/client";
import BrisPortePanel from '../../react/components/BrisPortePanel.jsx';
import DossierContext from '../../react/contexts/DossierContext.ts';
import Routing from 'fos-router';
import { Dossier } from '../../models/Dossier.ts';
import routes  from '../../../public/js/fos_js_routes.json';

Routing.setRoutingData(routes);

declare global {
    interface Window { Routing: any; }
}

window.Routing = Routing;

import '../../styles/authentification.css';

const { dossier, user } = JSON.parse(document.getElementById('react-arguments').textContent);

const root = ReactDOM.createRoot(document.getElementById('react-app'));


function DossierApp({dossier, user}) {
    const [d, setDossier] = useState(dossier);

    return <DossierContext.Provider value={{dossier: d, setDossier}} >
        <div className="fr-container">
            <h1>Déclarer un bris de porte</h1>
            <BrisPortePanel
                user={user}
            />
        </div>
    </DossierContext.Provider>
}

root.render(
    <React.StrictMode>
        <>
            <DossierApp dossier={dossier} user={user} />
        </>
    </React.StrictMode>
);