import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import { Br } from '../react/utils/fundamental';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import InscriptionSuccess from '../react/components/InscriptionSuccess';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";

startReactDsfr({ defaultColorScheme: "system" });

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
            <div className="fr-container">
              <InscriptionSuccess user={this.userValue}/>
              <Br space={2}/>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
