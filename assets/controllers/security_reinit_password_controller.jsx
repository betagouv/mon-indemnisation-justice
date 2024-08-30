import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import ReinitialisationMotDePasse from '../react/components/Connexion/ReinitialisationMotDePasse';

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";

startReactDsfr({ defaultColorScheme: "system" });

export default class extends Controller {
    static values = {
      user: Object,
      successMsg: String,
      csrfToken: String,
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <div className="fr-container">
              <div className="fr-grid-row">
                <div className="fr-col-12">
                  <ReinitialisationMotDePasse
                    user={this.userValue}
                    csrfToken={this.csrfTokenValue}
                    successMsg={this.successMsgValue}
                  />
                </div>
              </div>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
