import { Tableau } from "@common/composants/dsfr/Tableau.tsx";
import { DossierApercu } from "@common/models";
import { dateEtHeureSimple, periode } from "@common/services/date.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { BadgesDossier } from "@fip6/dossiers/components/BadgesDossier.tsx";
import React, { type ReactNode } from "react";

export const ListeDossiers = ({
  dossiers,
  caption,
}: {
  dossiers: DossierApercu[];
  caption?: ReactNode;
}) => {
  return (
    <Tableau
      id="tableau-dossiers-usager"
      caption={caption}
      className="fr-my-2w"
      fixed={true}
      headers={[
        { children: "Référence / état", className: "fr-col-4" },
        {
          children: "Identité et adresse du requérant",
          className: "fr-col-3",
        },
        { children: "Déposé le", className: "fr-col-2" },
        { children: "Attribué à", className: "fr-col-2" },
        {
          children: <span className="fr-hidden">Action</span>,
          className: "fr-col-1",
        },
      ]}
      data={dossiers.map((dossier: DossierApercu) => [
        {
          className: "fr-col-4",
          children: (
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
            </>
          ),
        },
        {
          className: "fr-col-3",
          style: {
            wordWrap: "break-word",
            whiteSpace: "pre-wrap",
          },
          children: (
            <>
              <span className="fr-text--lg fr-text--bold">
                {dossier.requerant}
              </span>
              <br />
              <p>{dossier.adresse}</p>
            </>
          ),
        },
        {
          className: "fr-col-2",
          style: { overflow: "hidden" },
          children: (
            <>
              {dossier.dateDepot && (
                <>
                  {dateEtHeureSimple(dossier.dateDepot, {
                    masquerAnneeSiCourante: true,
                  })}
                </>
              )}
              <BadgesDossier dossier={dossier} />
            </>
          ),
        },
        {
          className: "fr-col-2",
          children: (
            <>
              {dossier.redacteur ? (
                <span className="fr-text--bold">{dossier.redacteur.nom}</span>
              ) : (
                <i>non attribué</i>
              )}
            </>
          ),
        },
        {
          className: "fr-col-1",
          children: (
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
                  className: "fr-m-0",
                  linkProps: {
                    to: "/dossier/$id",
                    params: { id: dossier.id },
                  },
                },
              ]}
            />
          ),
        },
      ])}
    />
  );
};
