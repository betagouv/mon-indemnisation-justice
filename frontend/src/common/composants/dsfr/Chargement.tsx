import { Tourniquet } from "@/common/composants/dsfr/Tourniquet";
import React from "react";
import "./Chargement.css";
export const Chargement = ({
  titre,
  message,
}: {
  titre?: string;
  message?: string;
}) => (
  <div className="fr-chargement">
    {titre && <h3 className="fr-m-0">{titre}</h3>}
    {message && <span>{message}</span>}
    <Tourniquet />
  </div>
);
