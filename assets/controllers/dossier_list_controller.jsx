import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import PiedDePage from '../react/components/PiedDePage';
import BrisPortes from '../react/components/BrisPortes';
import Entete from '../react/components/Entete';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      breadcrumb: Object,
      brisPortes: Array
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
              <BrisPortes items={this.brisPortesValue} />
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
