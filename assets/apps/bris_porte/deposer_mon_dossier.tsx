import React from 'react';
import ReactDOM from "react-dom/client";
import BrisPortePanel from '../../react/components/BrisPortePanel.jsx';
import Routing from 'fos-router';
import routes  from '../../../public/js/fos_js_routes.json';

Routing.setRoutingData(routes);

declare global {
    interface Window { Routing: any; }
}

window.Routing = Routing;

import '../../styles/authentification.css';

const { brisPorte, user } = JSON.parse(document.getElementById('react-arguments').textContent);

const root = ReactDOM.createRoot(document.getElementById('react-app'));
root.render(
    <React.StrictMode>
        <>
            <div className="fr-container">
                <h1>Déclarer un bris de porte</h1>
                <BrisPortePanel
                    brisPorte={brisPorte}
                    user={user}
                />
            </div>
        </>
    </React.StrictMode>
);