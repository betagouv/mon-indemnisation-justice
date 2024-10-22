import { Controller } from '@hotwired/stimulus';

import * as ReactDOMClient from 'react-dom/client';
import React from 'react';
import FormulaireSimple from '../react/components/Connexion/FormulaireSimple';
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";

import '../styles/authentification.css';

startReactDsfr({ defaultColorScheme: "system" });


    /*{
      errorMessage: String,
      lastUsername: String,
      version: Object,
      csrfToken: String,
    }

     */

const errorMessage = "";
const lastUsername = "";
const csrfToken = "";

const container = document.getElementById('react-app');
const root = ReactDOMClient.createRoot(container);
root.render(
<React.StrictMode>
  <>
    <div className="pr-authentification">
      <div className="fr-container">
        <div className="fr-grid-row fr-mb-6w">
            <section className="pr-keyboard-hands fr-col-lg-6 fr-hidden fr-unhidden-lg">
              <div className="pic-keyboard-hands"></div>
            </section>
            <div className="fr-col-12 fr-col-lg-6">
              <FormulaireSimple errorMessage={errorMessage} csrfToken={csrfToken} lastUsername={lastUsername} />
            </div>
        </div>
      </div>
    </div>
  </>
</React.StrictMode>
);
