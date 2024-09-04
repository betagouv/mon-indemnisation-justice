import { Controller } from '@hotwired/stimulus';

import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Users from '../react/components/Users';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });

export default class extends Controller {
    static values = {
      agents: Array
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <div className="fr-container">
            <div className="fr-grid-row">
              <div className="fr-col-12">
                <Users items={this.agentsValue}/>
              </div>
            </div>
          </div>
        </React.StrictMode>
      )
    }
}
