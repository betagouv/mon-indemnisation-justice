import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import QuiSommesNousHeader from '../react/components/QuiSommesNous/QuiSommesNousHeader';
import CategorieDemandes from '../react/components/CategorieDemandes';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';

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
            <main role="main">
              <div className="pr-content fr-pb-6w">
                <div className="pr-content_header">
                  <QuiSommesNousHeader />
                </div>
                <div className="fr-container">
                  <div className="pr-content_body pr-mt-n6w ">
                    <CategorieDemandes />
                  </div>
                </div>
              </div>
            </main>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
