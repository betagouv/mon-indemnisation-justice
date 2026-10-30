import { EtatDeclaration } from "@fdo/modeles/EtatDeclaration.ts";
import React from "react";
import "./BadgeEtatDeclaration.css";

export const BadgeEtatDeclaration = ({
  etat,
  className,
}: {
  etat: EtatDeclaration;
  className?: string;
}) => (
  <p
    className={`fr-badge fr-badge--sm fr-badge--no-icon fr-badge--declaration-etat fr-badge--declaration-etat--${etat.slug} ${className ?? ""}`}
  >
    {etat.libelle}
  </p>
);
