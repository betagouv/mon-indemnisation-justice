import React from "react";
import ReactDOM from "react-dom/client";
import "@/style/requerant/dossier/test_mon_eligibilite.css";
import { TestEligibiliteForm } from "@/apps/requerant/dossier/components/TestEligibiliteForm.tsx";

const args = JSON.parse(document.getElementById("react-arguments").textContent);

const root = ReactDOM.createRoot(document.getElementById("react-app"));

root.render(
  <React.StrictMode>
    <div className="fr-container fr-my-3w">
      <div className="fr-stepper">
        <h2 className="fr-stepper__title">
          Tester votre éligibilité
          <span className="fr-stepper__state">Étape 1 sur 3</span>
        </h2>
        <div
          className="fr-stepper__steps"
          data-fr-current-step="1"
          data-fr-steps="3"
        ></div>
        <p className="fr-stepper__details">
          <span className="fr-text--bold">Étape suivante :</span> Création de
          votre compte
        </p>
      </div>
      <div className="fr-col-12 fr-my-2w">
        <TestEligibiliteForm
          token={args._token}
          estIssuAttestation={args.estIssuAttestation}
        />
      </div>
    </div>
  </React.StrictMode>,
);
