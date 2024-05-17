import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import QuiSommesNousHeader from '../react/components/QuiSommesNousHeader';
import CategorieDemandes from '../react/components/CategorieDemandes';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
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
                <div className="fr-col-7">
                  <QuiSommesNousHeader />
                </div>
                <div className="fr-col-3">
                </div>
                <div className="fr-col-12">
                  <CategorieDemandes />
                </div>
              </div>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
