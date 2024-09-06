import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import { Br } from '../react/utils/fundamental';
import CategorieDemandes from '../react/components/CategorieDemandes';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';

export default class extends Controller {
    static values = {
      user: Object,
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
            <>
            <section className="pr-whoami">
                <div className="fr-container">
                    <div className="fr-pt-6w fr-pb-12w">
                        <h1>Nous vous aidons dans votre demande d'indemnisation</h1>
                    </div>
                </div>
            </section>
            <div className="fr-container">
                <div className="pr-content_body pr-mt-n6w ">
                    <CategorieDemandes/>
                    <Br space={2}/>
                </div>
            </div>
            </>
        </React.StrictMode>
      )
    }
}
