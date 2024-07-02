import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import FormulaireSimple from '../react/components/Prejudice/Suivi/FormulaireSimple';
import CategorieDemandes from '../react/components/CategorieDemandes';
import ServiceNational from '../react/components/ServiceNational/ServiceNational';
import FilAriane from '../react/components/FilAriane';
import { Br } from '../react/utils/fundamental';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      raccourci: String,
      breadcrumb: Object
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
                  <Br space={2}/>
                  <section className="pr-form-section fr-p-4w">
                    <FormulaireSimple raccourci={this.raccourciValue} />
                  </section>
                  <Br space={2}/>
                </div>
              </div>
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
