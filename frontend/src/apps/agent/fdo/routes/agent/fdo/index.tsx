import { router } from "@/apps/agent/fdo/_init/_router.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Card from "@codegouvfr/react-dsfr/Card";
import React from "react";
import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/")({
  component: RouteComponent,
});

function RouteComponent() {
  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  return (
    <div>
      <div
        className="fr-col-12 fr-my-3w"
        style={{ border: "1px solid var(--border-default-grey)" }}
      >
        <div
          className="fr-grid-row fr-py-2w fr-px-4w"
          style={{ alignItems: "center" }}
        >
          <div className="fr-col-6">
            <h4 className="fr-text-label--blue-france fr-m-0">
              Déclarer une erreur opérationelle
            </h4>
          </div>

          <div className="fr-col-6">
            <ButtonsGroup
              alignment="right"
              buttons={[
                {
                  children:
                    "Déclarer une erreur opérationnelle de bris de porte",
                  className: "fr-m-0",
                  onClick: () =>
                    naviguer({
                      to: "/agent/fdo/erreur-operationnelle/nouvelle-declaration",
                    }),
                },
              ]}
            />
          </div>
        </div>
        <div
          className="fr-grid-row fr-py-2w fr-px-4w"
          style={{
            background:
              "var(--light-decisions-artwork-artwork-decorative-blue-france, #ECECFE)",
          }}
        >
          <p>
            <strong>Qu’est-ce qu’une erreur opérationnelle ? </strong>
          </p>

          <p className="fr-text--sm">
            Une erreur opérationnelle sur un bris de porte correspond à une
            <b> intervention de police judiciaire</b> effectuée par erreur à un
            domicile <b>non concerné par l’enquête</b>, entraînant la
            <b> détérioration de la porte d’entrée</b> (ou de ses éléments).
          </p>

          <div className="fr-col-12 fr-grid-row fr-grid-row--right">
            <Link
              to="/agent/fdo/foire-aux-questions"
              hash="question-quand-remettre-attestation"
              className="fr-link fr-icon-arrow-right-line fr-link--icon-right fr-text--sm"
            >
              <b>Vous avez un doute ?</b> Visitez la foire aux questions pour
              voir les différents cas de figure.
            </Link>
          </div>
        </div>
      </div>

      <div
        className="fr-col-12 fr-my-3w"
        style={{
          display: "flex",
          flexDirection: "row",
          gap: "3vw",
          alignItems: "center",
          justifyItems: "stretch",
        }}
      >
        <Card
          style={{ flex: "1 1 0px" }}
          title="Réquisitionner un serrurier"
          titleAs={"h5"}
          linkProps={{
            href: "https://www.serruriers-de-france.com/gouv/",
            target: "_blank",
          }}
          enlargeLink
          horizontal={false}
          desc="Retrouvez les numéros utiles des serruriers. "
          border
        />

        <Card
          style={{ flex: "1 1 0px" }}
          title="Mes déclarations"
          titleAs={"h5"}
          linkProps={{
            to: "/agent/fdo/erreur-operationnelle/mes-declarations",
          }}
          enlargeLink
          horizontal={false}
          desc="Ici retrouvez vos déclarations (brouillon, envoyées,...) "
          border
        />
      </div>

      <div
        className="fr-col-12 fr-my-3w"
        style={{
          display: "flex",
          flexDirection: "row",
          gap: "3vw",
          alignItems: "center",
          justifyItems: "stretch",
        }}
      >
        <Card
          style={{ flex: "1 1 0px" }}
          title="Foire aux questions"
          titleAs={"h5"}
          linkProps={{
            to: "/agent/fdo/foire-aux-questions",
          }}
          enlargeLink
          horizontal={false}
          desc="Ici retrouvez les questions les plus fréquentes."
          border
        />

        <Card
          style={{ flex: "1 1 0px" }}
          title="Mes documents"
          titleAs={"h5"}
          linkProps={{
            to: "/agent/fdo/les-documents",
          }}
          enlargeLink
          horizontal={false}
          desc="Ici retrouvez les guides utilisateurs. "
          border
        />
      </div>
    </div>
  );
}
