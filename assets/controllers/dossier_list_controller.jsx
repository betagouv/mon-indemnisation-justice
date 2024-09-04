import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import BrisPortes from '../react/components/BrisPortes';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });

export default class extends Controller {
    static values = {
      brisPortes: Array
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <div className="fr-container">
              <BrisPortes items={this.brisPortesValue} />
            </div>
          </>
        </React.StrictMode>
      )
    }
}
