import { Controller } from '@hotwired/stimulus';
import ReactDOM from 'react-dom';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import {default as RecapitulatifBrisPorte} from '../react/components/BrisPorte/Recapitulatif';
import Entete from '../react/components/Entete';
import FilAriane from '../react/components/FilAriane';
import BrisPortePanelView from '../react/components/BrisPortePanelView';
import {default as FormulaireSimplifie} from '../react/components/Prejudice/Traitement/FormulaireSimplifie';
import PiedDePage from '../react/components/PiedDePage';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import {trans,BRIS_PORTE_VIEW_TITLE,BRIS_PORTE_ACCEPT_OR_REJECT_TITLE} from '../translator';

var memoryNavigator={width: 0,height: 0};
const TARE_HEIGHT = 0;
const REDUCE_WINDOWS_HEIGHT = 250;
const initEditor = () => {
  const i = document.querySelector(".freeze-section");
  memoryNavigator = {
    width: i.offsetWidth,
    height: $(window).height()-REDUCE_WINDOWS_HEIGHT
  };
}

const resizeEditor = () => {
  const scrolltop = $(window).scrollTop();
  let height = memoryNavigator.height;
  if(!height)
    return;
  if (scrolltop > height+TARE_HEIGHT) {
    $(".freeze-section")
      .css('position', 'fixed')
      .css('width', memoryNavigator.width)
      .css('top',0)
      .css('z-index',1000)
      .css('background-color', 'white')
    ;
  }
  else
    $(".freeze-section").css('position', 'static');
}

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      breadcrumb: Object,
      brisPorte: Object,
      prejudice: Object
    }

    connect() {
      const container = this.element;
      const root = ReactDOMClient.createRoot(container);
      const height = $(window).height();
      const styles = { freeze_panel: { height: height } };
      $(window).scroll(() => resizeEditor());
      $(document).ready(() => initEditor());
      root.render(
        <React.StrictMode>
          <>
            <Entete user={this.userValue} version={this.versionValue} />
            <div className="fr-container">
              <FilAriane breadcrumb={this.breadcrumbValue}/>
              <h1>{trans(BRIS_PORTE_ACCEPT_OR_REJECT_TITLE).replace("%reference%",this.brisPorteValue.reference)}</h1>
              <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
                <div className="fr-col-6">
                  <RecapitulatifBrisPorte
                    brisPorte={this.brisPorteValue}
                    user={this.userValue}
                  />
                </div>
                <div className="fr-col-6">
                  <section className="pr-form-section fr-p-4w freeze-section" style={styles.freeze_panel}>
                    <FormulaireSimplifie
                      prejudice={this.prejudiceValue}
                      dimension={{height: height}}
                    />
                  </section>
                </div>
              </div>
            </div>
            <PiedDePage />
          </>
        </React.StrictMode>
      )
    }
}
