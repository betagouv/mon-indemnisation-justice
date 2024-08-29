import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import FilAriane from '../react/components/FilAriane';
import Inscription from '../react/components/Inscription';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import '../styles/authentification.css';

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
            <div className="pr-authentification">
              <div className="fr-container">

                <div className="fr-grid-row fr-mb-6w">
                  <section className="pr-keyboard-hands fr-col-6">
                    <div className="pic-keyboard-hands"></div>
                  </section>
                  <div className="fr-col-6">
                    <Inscription user={this.userValue} csrfToken={this.csrfTokenValue}/>
                  </div>
                </div>
              </div>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
