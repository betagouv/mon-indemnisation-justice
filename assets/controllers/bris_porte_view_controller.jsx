import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import {default as RecapitulatifBrisPorte} from '../react/components/BrisPorte/Recapitulatif';
import {default as FormulaireSimplifie} from '../react/components/Prejudice/Traitement/FormulaireSimplifie';
import {default as Signature} from '../react/components/Prejudice/Traitement/Signature';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });


var memoryNavigator={width: 0,height: 0};
const TARE_HEIGHT = 0;
const REDUCE_WINDOWS_HEIGHT = 250;
const ROLE_CHEF_PRECONTENTIEUX = 'ROLE_CHEF_PRECONTENTIEUX';
const ROLE_REDACTEUR_PRECONTENTIEUX = 'ROLE_REDACTEUR_PRECONTENTIEUX';

const initEditor = () => {
  const i = document.querySelector(".freeze-section");
  if(i)
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
  $(".freeze-section").css('overflow-y', 'scroll');
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
    $(".freeze-section")
      .css('position', 'static')
    ;
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
            <div className="fr-container">
              <h1>Traitement du bris de porte { this.brisPorteValue.reference }</h1>
              <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
                <div className="fr-col-6">
                  <RecapitulatifBrisPorte
                    brisPorte={this.brisPorteValue}
                    user={this.userValue}
                  />
                </div>
                <div className="fr-col-6">
                  {(this.userValue.plaintextRole === ROLE_REDACTEUR_PRECONTENTIEUX) &&
                  <section className="pr-form-section fr-p-4w freeze-section" style={styles.freeze_panel}>
                    <FormulaireSimplifie
                      prejudice={this.prejudiceValue}
                      dimension={{height: height}}
                    />
                  </section>
                  }
                  {(this.userValue.plaintextRole === ROLE_CHEF_PRECONTENTIEUX) &&
                  <section className="pr-form-section fr-p-4w">
                    <Signature
                      prejudice={this.prejudiceValue}
                      dimension={{height: height}}
                    />
                  </section>
                  }
                </div>
              </div>
            </div>
          </>
        </React.StrictMode>
      )
    }
}
