import { Controller } from '@hotwired/stimulus';
import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import { Br } from '../react/utils/fundamental';
import CategorieDemandes from '../react/components/CategorieDemandes';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
startReactDsfr({ defaultColorScheme: "system" });
import '../styles/homepage.css';

export default class extends Controller {
    static values = {
      user: Object,
      version: Object,
      breadcrumb: Object
    }
    connect() {
      const container = this.element;
      console.log(container);
      const root = ReactDOMClient.createRoot(container);
      root.render(
        <React.StrictMode>
          <>
            <CategorieDemandes />
            <Br space={2}/>
          </>
        </React.StrictMode>
      )
    }
}
