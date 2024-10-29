import { Controller } from '@hotwired/stimulus';

import React from 'react';
import ReactDOM from "react-dom";
import FormulaireSimple from '../react/components/Connexion/FormulaireSimple';

import '../styles/authentification.css';


const root = ReactDOM.createRoot(document.getElementById('react-app'));
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
                  <FormulaireSimple errorMessage="" csrfToken="" />
                </div>
            </div>
          </div>
        </div>
      </>
    </React.StrictMode>
);