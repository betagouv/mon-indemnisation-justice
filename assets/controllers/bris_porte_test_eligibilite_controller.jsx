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
            <Entete user={this.userValue} version={this.versionValue} />
            <div className="pr-body_header">
              <section className="pr-breadcrumb">
                <div className="fr-container">
                  <FilAriane breadcrumb={this.breadcrumbValue}/>
                </div>
              </section>
              <section className="pr-eligibilite_header">
                <div className="fr-container">
                  <div className="fr-pb-4w">
                    <h1>{trans(BRIS_PORTE_TEST_ELIGIBILITE_H1)}</h1>
                    <p>{trans(BRIS_PORTE_TEST_ELIGIBILITE_CHAPO)}</p>
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
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
