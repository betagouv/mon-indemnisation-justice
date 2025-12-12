import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/requisition-serrurier")({
  component: () => (
    <div>
      <h1>Réquisition d’un serrurier</h1>

      <section>
        <p>
          Afin de faciliter les interventions, <b>Mon Indemnisation Justice</b>{" "}
          intègre désormais une fonctionnalité pour la réquisition d’un
          serrurier labellisé. Cette évolution vise également à améliorer la
          qualité du service rendu aux usagers en collaborant avec des
          serruriers sensibilisés aux bonnes pratiques, membres du{" "}
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
        </div>
      </section>
    </div>
  ),
});
