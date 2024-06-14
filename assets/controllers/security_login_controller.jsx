import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import FormulaireSimple from '../react/components/Connexion/FormulaireSimple';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import '../styles/authentification.css';

startReactDsfr({ defaultColorScheme: "system" });

export default class extends Controller {
    static values = {
      errorMessage: String,
      lastUsername: String,
      version: Object,
      csrfToken: String,
      breadcrumb: Object
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <Entete user={this.userValue} version={this.versionValue} />
            <div className="pr-authentification">
              <div className="fr-container">
                <FilAriane breadcrumb={this.breadcrumbValue}/>
                <div className="fr-grid-row fr-mb-6w">
                  <section className="pr-keyboard-hands fr-col-6">
                    <div className="pic-keyboard-hands"></div>
                  </section>
                  <div className="fr-col-6">
                    <FormulaireSimple errorMessage={this.errorMessageValue} csrfToken={this.csrfTokenValue} lastUsername={this.lastUsernameValue} />
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
