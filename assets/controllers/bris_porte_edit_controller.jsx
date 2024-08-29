import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import BrisPortePanel from '../react/components/BrisPortePanel';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });

import '../styles/case.css';

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
            <div className="fr-container">
              <h1>Déclarer un bris de porte</h1>
              <BrisPortePanel id={this.userIdValue} user={this.userValue} brisPorte={this.brisPorteValue}/>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
