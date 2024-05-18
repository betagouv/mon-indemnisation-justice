import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import TestEligibilite from '../react/components/BrisPorte/TestEligibilite';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import {trans, BRIS_PORTE_TEST_ELIGIBILITE_H1, BRIS_PORTE_TEST_ELIGIBILITE_CHAPO} from '../translator';
startReactDsfr({ defaultColorScheme: "system" });

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
            <Entete user={this.userValue} version={this.versionValue} />
            <div className="fr-container">
              <div className="fr-grid-row">
                <div className="fr-col-12">
                  <FilAriane breadcrumb={this.breadcrumbValue}/>
                  <h1>{trans(BRIS_PORTE_TEST_ELIGIBILITE_H1)}</h1>
                  <p>{trans(BRIS_PORTE_TEST_ELIGIBILITE_CHAPO)}</p>
                </div>
                <div className="fr-col-12">
                  <TestEligibilite />
                </div>
              </div>
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
