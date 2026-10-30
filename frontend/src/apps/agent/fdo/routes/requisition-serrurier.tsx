import { createFileRoute } from "@tanstack/react-router";
import React from "react";

const telephoneSerrurier = (
  import.meta.env.VITE_TELEPHONE_SERRURIER ?? ""
).trim();
const telephoneSerrurierFormate = telephoneSerrurier
  .replace(/\s+/g, "")
  .replace(/(\d{2})(?=\d)/g, "$1 ");

export const Route = createFileRoute("/requisition-serrurier")({
  loader: async ({ context }) => ({
    agent: context.agent,
  }),
  component: () => {
    const { agent } = Route.useLoaderData();

    return (
      <div>
        <h1>Réquisition d’un serrurier</h1>

        <section>
          {agent.administration.type == "GN" ? (
            <>
              <p>
                Afin de faciliter les interventions,{" "}
                <b>Mon Indemnisation Justice</b> intègre désormais une
                fonctionnalité pour la réquisition d’un serrurier labellisé.
                Cette évolution vise également à améliorer la qualité du service
                rendu aux usagers en collaborant avec des serruriers
                sensibilisés aux bonnes pratiques, membres du{" "}
                <b>Label « Serruriers de France »</b>.
              </p>
              <div className="fr-col-12 fr-grid-row fr-grid-row--center">
                <a
                  className="fr-btn"
                  href="https://www.serruriers-de-france.com/gouv/"
                  target="_blank"
                >
                  Trouver un serrurier labellisé
                </a>
              </div>{" "}
            </>
          ) : (
            <>
              <p>
                Pour faciliter vos interventions,{" "}
                <b>Mon Indemnisation Justice</b> vous permet de trouver un
                serrurier labellisé, sensibilisé aux bonnes pratiques et membres
                du Label « Serruriers de France ».
              </p>
              <p>
                🔎 Trouver un serrurier Accédez aux serruriers qualifiés
                disponibles dans votre secteur.
              </p>
              <p>
                📞 Appeler un serrurier Un serveur téléphonique est disponible
                24h/24, 7j/7 au {telephoneSerrurierFormate} (appel non surtaxé).
                Indiquez votre besoin et votre code postal : vous recevez
                immédiatement par SMS les coordonnées des serruriers disponibles
                dans votre secteur.
              </p>
              <div className="fr-col-12 fr-grid-row fr-grid-row--center">
                <a
                  className="fr-btn"
                  href={`tel:${telephoneSerrurier}`}
                  target="_blank"
                >
                  Trouver un serrurier labellisé
                </a>
              </div>{" "}
            </>
          )}
        </section>
      </div>
    );
  },
});
