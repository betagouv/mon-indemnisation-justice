import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import FilAriane from '../react/components/FilAriane';
import Inscription from '../react/components/Inscription';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";

startReactDsfr({ defaultColorScheme: "system" });

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      breadcrumb: Object,
      csrfToken: String
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
                </div>
                <div className="fr-col-6">
                </div>
                <div className="fr-col-6">
                  <Inscription user={this.userValue} csrfToken={this.csrfTokenValue}/>
                </div>
              </div>
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
