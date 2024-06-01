import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import FilAriane from '../react/components/FilAriane';
import BrisPortePanel from '../react/components/BrisPortePanel';
import PiedDePage from '../react/components/PiedDePage';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import {trans,BRIS_PORTE_EDIT_TITLE} from '../translator';
export default class extends Controller {
    static values = {
      userId: Number,
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
              <div className="fr-grid-row">
                <div className="fr-col-12">
                  <FilAriane breadcrumb={this.breadcrumbValue}/>
                </div>
                <div className="fr-col-12">
                  <h1>{trans(BRIS_PORTE_EDIT_TITLE)}</h1>
                </div>
                <div className="fr-col-12">
                  <BrisPortePanel id={this.userIdValue} user={this.userValue} brisPorte={this.brisPorteValue}/>
                </div>
              </div>
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
