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
    <>
      <h1>Consulter la décision</h1>
    </>
  );
};

ReactDOM.createRoot(document.getElementById("react-app")).render(
  <React.StrictMode>
    <>
      <ConsulterDecisionApp />
    </>
  </React.StrictMode>,
);
