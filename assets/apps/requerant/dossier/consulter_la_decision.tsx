import "reflect-metadata";
import { DocumentType, DossierDetail } from "@/apps/agent/dossiers/models";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
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

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const args = JSON.parse(document.getElementById("react-arguments").textContent);

const dossier = plainToInstance(DossierDetail, args.dossier, {
  enableImplicitConversion: true,
});

console.dir(dossier.documents);

const ConsulterDecisionApp = observer(function ConsulterDecisionApp({
  dossier,
}: {
  dossier: DossierDetail;
}) {
  return (
    <div className="fr-container">
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>Votre demande d'indemnisation</h1>

          <p>
            La demande d'indemnisation que vous avez initié le{" "}
            {dossier.dateDepot.toLocaleString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
            })}
            , concernant un bris de porte survenu le{" "}
            {dossier.dateOperation.toLocaleString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
            })}{" "}
            au logement{" "}
            {dossier.adresse.estRenseignee() ? (
              <>
                situé{" "}
                {dossier.adresse.libelle() /*13 rue des Oliviers 49000 ANGERS*/}
              </>
            ) : (
              ""
            )}{" "}
            dont vous êtes{" "}
            {dossier.testEligibilite.estProprietaire
              ? "propriétaire"
              : "locataire"}
            , a été {dossier.estAccepte() ? "acceptée" : "refusée"}.
          </p>

          <p>
            Vous trouverez ci-dessous le courrier expliquant les motivations de
            cette décision.
          </p>

          {dossier.estAccepte() && (
            <p>
              Le montant de l'indemnisation qui vous est proposé est de{" "}
              <span className="fr-text--bold">
                {dossier.montantIndemnisation.toLocaleString()} €
              </span>
              . Pour accepter la proposition et déclencher le versement de cette
              somme, il vous faut signer le document et nous le retourner en
              cliquant sur le bouton "Accepter la proposition".
            </p>
          )}
        </div>

        {dossier.estAccepte() && (
          <div className="fr-col-lg-3 fr-col-offset-lg-9">
            <ul className="fr-btns-group fr-btns-group--right">
              <li>
                <button className="fr-btn">Accepter la proposition</button>
              </li>
            </ul>
          </div>
        )}

        <div className="fr-col-12">
          <object
            data={
              dossier
                .getDocumentsType(DocumentType.TYPE_COURRIER_MINISTERE)
                .at(0)?.url
            }
            type="application/pdf"
            style={{ width: "100%", aspectRatio: "210 / 297" }}
          ></object>
        </div>
      </div>
    </div>
  );
});

ReactDOM.createRoot(document.getElementById("react-app")).render(
  <React.StrictMode>
    <>
      <ConsulterDecisionApp dossier={dossier} />
    </>
  </React.StrictMode>,
);
