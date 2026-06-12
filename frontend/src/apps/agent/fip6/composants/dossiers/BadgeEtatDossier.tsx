import { EtatDossier } from "@/common/models";
import React from "react";

export const BadgeEtatDossier = ({
  etat,
  className,
  grand = true,
}: {
  etat: EtatDossier;
  className?: string | undefined;
  grand?: boolean;
}) => (
  <>
    <div className={`fr-grid-row ${className}`} style={{ gap: ".5vw" }}>
      <p
        className={`fr-badge fr-badge--no-icon fr-badge--dossier-etat fr-badge--dossier-etat--${etat.etat.slug} ${grand ? "fr-py-1w" : ""} fr-px-2w`}
        {...(etat.estCloture()
          ? {
              "aria-describedby": `tooltip-etat-dossier-${etat.id}`,
            }
          : {})}
      >
        {etat.etat.libelle}
      </p>
    </div>
    {etat.estCloture() && (
      <span
        className="fr-tooltip fr-placement"
        id={`tooltip-etat-dossier-${etat.id}`}
        role="tooltip"
      >
        {etat.contexte?.motifRejet || <i>Aucun motif</i>}
      </span>
    )}
  </>
);
