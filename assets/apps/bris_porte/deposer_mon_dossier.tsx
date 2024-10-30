import React from 'react';
import ReactDOM from "react-dom";
import BrisPortePanel from '../../react/components/BrisPortePanel.jsx';

import '../../styles/authentification.css';


const root = ReactDOM.createRoot(document.getElementById('react-app'));
root.render(
    <React.StrictMode>
        <>
            <div className="fr-container">
                <h1>Déclarer un bris de porte</h1>
                <BrisPortePanel
                    // id={this.userIdValue}
                    // user={this.userValue}
                    // brisPorte={this.brisPorteValue}
                />
            </div>
        </>
    </React.StrictMode>
);