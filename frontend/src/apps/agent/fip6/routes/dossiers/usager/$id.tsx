import { BadgesDossier } from "@/apps/agent/fip6/dossiers/components/BadgesDossier.tsx";
import { DossierApercu, Usager } from "@/common/models";
import { dateEtHeureSimple, periode } from "@/common/services/date.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Table from "@codegouvfr/react-dsfr/Table";
import { createFileRoute } from "@tanstack/react-router";
import { plainToInstance } from "class-transformer";
import React, { useMemo } from "react";

export const Route = createFileRoute("/dossiers/usager/$id")({
  component: RouteComponent,
  loader: async ({ params }) => {
    const reponse = await fetch(`/api/agent/fip6/dossiers/usager/${params.id}`);
    const data = await reponse.json();

    return {
      usager: plainToInstance(Usager, data.usager),
      dossiers: plainToInstance(DossierApercu, data.dossiers as any[]),
    };
  },
});

function RouteComponent() {
  const { usager, dossiers }: { usager: Usager; dossiers: DossierApercu[] } =
    Route.useLoaderData();

  console.log(dossiers.map((dossier) => dossier.etat.dateEntree));

  const s = useMemo(() => (dossiers.length > 1 ? "s" : ""), [usager.id]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h1>Dossiers déposés par {usager.nomSimple()}</h1>
      </div>
      <div className="fr-col-12">
        {/* C'est le seul moyen que rapide pour "pondérer" la largeur des colonnes du tableau... */}
        <style>{`
          #tableau-dossiers-usager table th:nth-child(1) { width: 30%; }
          #tableau-dossiers-usager table th:nth-child(2) { width: 25%;word-wrap:break-word;white-space: pre-wrap; }
          #tableau-dossiers-usager table td:nth-child(2) { width: 25%;word-wrap:break-word;white-space: pre-wrap; }
          #tableau-dossiers-usager table th:nth-child(3) { width: 15%;overflow: "hidden }
          #tableau-dossiers-usager table td:nth-child(3) { width: 15%;overflow: "hidden }
          #tableau-dossiers-usager table th:nth-child(4) { width: 15%; }
          #tableau-dossiers-usager table th:nth-child(5) { width: 5%; }
        `}</style>
        <Table
          id="tableau-dossiers-usager"
          caption={`${dossiers.length} dossier${s} correspondant${s}`}
          className="fr-m-0"
          fixed={true}
          headers={[
            "Référence / état",
            "Identité et adresse du requérant",
            "Déposé le",
            "Attribué à",
            <span className="fr-hidden">Action</span>,
          ]}
          data={dossiers.map((dossier: DossierApercu) => [
            <>
              <span className="fr-text--lg fr-text--bold">
                {dossier.reference}
              </span>
              <br />
              <p
                className={`fr-badge fr-badge--no-icon fr-badge--dossier-etat fr-badge--dossier-etat--${dossier.etat.etat.slug} fr-mb-1v`}
                {...(dossier.etat.estCloture()
                  ? {
                      "aria-describedby": `tooltip-etat-dossier-${dossier.id}`,
                    }
                  : {})}
              >
                {dossier.etat.libelle}
              </p>
              {dossier.etat.estCloture() && (
                <span
                  className="fr-tooltip fr-placement"
                  id={`tooltip-etat-dossier-${dossier.id}`}
                  role="tooltip"
                >
                  {dossier.etat.contexte?.motifRejet || <i>Aucun motif</i>}
                </span>
              )}
              <br />
              depuis {periode(dossier.etat.dateEntree)}
            </>,
            <>
              <span className="fr-text--lg fr-text--bold">
                {dossier.requerant}
              </span>
              <br />
              <p>{dossier.adresse}</p>
            </>,
            <>
              {dossier.dateDepot && (
                <>
                  {dateEtHeureSimple(dossier.dateDepot, {
                    masquerAnneeSiCourante: true,
                  })}
                </>
              )}
              <BadgesDossier dossier={dossier} />
            </>,
            <>
              {dossier.redacteur ? (
                <span className="fr-text--bold">{dossier.redacteur.nom}</span>
              ) : (
                <i>non attribué</i>
              )}
            </>,

            <ButtonsGroup
              alignment="right"
              inlineLayoutWhen="always"
              buttonsEquisized={true}
              buttonsSize={"small"}
              buttons={[
                {
                  children: "Voir",
                  priority: "secondary",
                  iconId: "fr-icon-eye-line",
                  iconPosition: "right",
                  linkProps: {
                    to: "/dossier/$id",
                    params: { id: dossier.id },
                  },
                },
              ]}
            />,
          ])}
        />
      </div>
    </div>
  );
}
