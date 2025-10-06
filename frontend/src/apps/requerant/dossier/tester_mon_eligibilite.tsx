import React from "react";
import ReactDOM from "react-dom/client";
import "@/style/requerant/dossier/test_mon_eligibilite.css";
import { TestEligibiliteForm } from "@/apps/requerant/dossier/components/TestEligibiliteForm.tsx";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
});

const args = JSON.parse(
  document.getElementById("react-arguments")?.textContent || "{}",
);

const root = document.getElementById("react-app");

if (root) {
  ReactDOM.createRoot(root).render(
    <React.StrictMode>
      <div className="fr-container fr-my-3w">
        <Stepper
          currentStep={1}
          stepCount={3}
          title="Tester votre éligibilité"
          nextTitle="Création de
          votre compte"
        />

        <div className="fr-col-12 fr-my-2w">
          <TestEligibiliteForm
            token={args._token}
            estIssuAttestation={args.estIssuAttestation}
          />
        </div>
      </div>
    </React.StrictMode>,
  );
}
