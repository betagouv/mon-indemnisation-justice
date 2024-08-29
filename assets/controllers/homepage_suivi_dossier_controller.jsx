import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import FormulaireSimple from '../react/components/Prejudice/Suivi/FormulaireSimple';
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Br } from '../react/utils/fundamental';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';

export default class extends Controller {
    static values = {
      raccourci: String,
      csrfToken: String,
      statuts: Array
    }
    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <div className="fr-container">
              <div className="fr-grid-row">
                <div className="fr-col-12">
                  <Br space={2}/>
                  <section className="pr-form-section fr-p-4w">
                    <h1>Suivre mon dossier</h1>
                    <FormulaireSimple csrfToken={this.csrfTokenValue} raccourci={this.raccourciValue} />
                    <Table
                      data={this.statutsValue}
                      headers={["Date", "Statut"]}
                      fixed
                    />
                  </section>
                  <Br space={2}/>
                </div>
              </div>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
