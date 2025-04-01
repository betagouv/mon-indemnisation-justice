import { DocumentType } from "@/apps/agent/dossiers/models";
import React from "react";
import ReactDOM from "react-dom/client";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const args = JSON.parse(document.getElementById("react-arguments").textContent);

const ConsulterDecisionApp = function ConsulterDecisionApp() {
  return (
    <div className="fr-container">
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>Consulter la décision</h1>

          <p>
            La demande d'indemnisation que vous avez initié le jeudi 15 avril
            2024, concernant un bris de porte survenu le dimanche 30 juillet
            2023 au logement situé 13 rue des Oliviers 49000 ANGERS donc vous
            êtes locataire, a été acceptée.
          </p>

          <p>
            Le montant de l'indemnisation qui vous est proposé est de{" "}
            <span className="fr-text--bold">1234, 56 €</span>. Pour l'accepter
            et déclencher le versement de cette somme, il vous faut signer le
            document et nous le retourner en cliquant sur le bouton "Accepter la
            proposition".
          </p>
        </div>

        <div className="fr-col-lg-3 fr-col-offset-lg-9">
          <ul class="fr-btns-group fr-btns-group--right">
            <li>
              <button className="fr-btn">Accepter la proposition</button>
            </li>
          </ul>
        </div>

        <div className="fr-col-12">
          <object
            data="https://static.boutique.orange.fr/medias/pdf/divers/pdf-lettre-type-resiliation.pdf"
            type="application/pdf"
            style={{ width: "100%", aspectRatio: "210 / 297" }}
          ></object>
        </div>
      </div>
    </div>
  );
};

ReactDOM.createRoot(document.getElementById("react-app")).render(
  <React.StrictMode>
    <>
      <ConsulterDecisionApp />
    </>
  </React.StrictMode>,
);
