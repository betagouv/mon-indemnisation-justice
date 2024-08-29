import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import PiedDePage from '../react/components/PiedDePage';
import TestEligibilite from '../react/components/BrisPorte/TestEligibilite';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/eligibilite.css';

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      breadcrumb: Object
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <div className="pr-body_header">
              <section className="pr-eligibilite_header">
                <div className="fr-container">
                  <div className="fr-pb-4w">
                    <h1>Bris de porte</h1>
                    <p>Je souhaite effectuer une demande d’indemnisation pour un bris de portes ou autres dégâts matériels causés dans le cadre d'une opération de police judiciaire (perquisition judiciaire, interpellation d'un individu, ...)</p>
                  </div>
                </div>
              </section>
            </div>
            <div className="fr-container">
              <div className="fr-grid-row">
                <div className="fr-col-8">
                  <TestEligibilite />
                </div>
                <div className="fr-col-4">
                  <div className="fr-py-2w">
                    <div className="udraw-terms-conditions"></div>
                  </div>
                </div>
              </div>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
