import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import {default as RecapitulatifBrisPorte} from '../react/components/BrisPorte/Recapitulatif';
import Entete from '../react/components/Entete';
import FilAriane from '../react/components/FilAriane';
import BrisPortePanelView from '../react/components/BrisPortePanelView';
import PiedDePage from '../react/components/PiedDePage';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import {trans,BRIS_PORTE_VIEW_TITLE,BRIS_PORTE_ACCEPT_OR_REJECT_TITLE} from '../translator';
export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      breadcrumb: Object,
      brisPorte: Object
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <Entete user={this.userValue} version={this.versionValue} />
            <div className="fr-container">
              <FilAriane breadcrumb={this.breadcrumbValue}/>
              <h1>{trans(BRIS_PORTE_ACCEPT_OR_REJECT_TITLE).replace("%reference%",this.brisPorteValue.reference)}</h1>
              <div className="fr-grid-row">
                <div className="fr-col-6">
                  <RecapitulatifBrisPorte
                    brisPorte={this.brisPorteValue}
                    user={this.userValue}
                  />
                </div>
                <div className="fr-col-6">
                </div>
              </div>
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
