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
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Br } from '../react/utils/fundamental';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';
import { trans, HOMEPAGE_SUIVI_DOSSIER, STATUT_FIELD_LIBELLE,
STATUT_FIELD_DATE } from "../translator";

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      raccourci: String,
      breadcrumb: Object,
      csrfToken: String,
      statuts: Array
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
                    <h1>{trans(HOMEPAGE_SUIVI_DOSSIER)}</h1>
                    <FormulaireSimple csrfToken={this.csrfTokenValue} raccourci={this.raccourciValue} />
                    <Table
                      data={this.statutsValue}
                      headers={[trans(STATUT_FIELD_DATE), trans(STATUT_FIELD_LIBELLE)]}
                      fixed
                    />
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
