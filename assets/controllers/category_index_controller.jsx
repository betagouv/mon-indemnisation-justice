import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import { Br } from '../react/utils/fundamental';
import Entete from '../react/components/Entete';
import PiedDePage from '../react/components/PiedDePage';
import QuiSommesNousHeader from '../react/components/QuiSommesNous/QuiSommesNousHeader';
import CategorieDemandes from '../react/components/CategorieDemandes';
import ServiceNational from '../react/components/ServiceNational/ServiceNational';
import FilAriane from '../react/components/FilAriane';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';
import {trans,PREJUDICE_SUBTITLE} from '../translator';

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
            <Entete user={this.userValue} version={this.versionValue} />
            <main role="main">
              <div className=".pr-content_header pr-content fr-pb-6w">
                <section className="pr-whoami">
                  <div className="fr-container">
                    <div className="fr-pt-6w fr-pb-12w">
                      <h1>{trans(PREJUDICE_SUBTITLE)}</h1>
                    </div>
                  </div>
                </section>
                <div className="fr-container">
                  <div className="pr-content_body pr-mt-n6w ">
                    <CategorieDemandes />
                    <Br space={2}/>
                  </div>
                </div>
              </div>
            </main>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
