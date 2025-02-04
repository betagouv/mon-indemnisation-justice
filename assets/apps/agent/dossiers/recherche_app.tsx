import {RechercheDossierApp} from "@/apps/agent/dossiers/components/RechercheDossierApp";
import {EtatDossier, Redacteur} from "@/apps/agent/dossiers/models";
import {disableReactDevTools} from '@/react/services/devtools.js';
import {autorun} from "mobx";
import React from 'react';
import ReactDOM from "react-dom/client";
import {RechercheDossier} from '@/apps/agent/dossiers/models/'

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

const args = JSON.parse(document.getElementById('react-arguments').textContent);

Redacteur.charger(args.redacteurs ?? [])
EtatDossier.charger(args.etats_dossier ?? [])

console.log(EtatDossier.catalog)

const recherche = RechercheDossier.fromURL();

autorun(() => {
    history.replaceState(null, '', recherche.toURL());
})

ReactDOM
    .createRoot(document.getElementById('react-app-agent-recherche-dossiers'))
    .render(
        <RechercheDossierApp recherche={recherche}></RechercheDossierApp>
    );