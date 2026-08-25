import { dateEtHeureSimple, periode } from "@/common/services/date.ts";
import React, { ReactNode } from "react";
import "./Frise.css";

export type StatutEvenement = "passe" | "actuel" | "futur";
export type CoteEvenement = "gauche" | "droite";

export type EvenementFrise = {
  libelle: ReactNode;
  date: Date;
  dateFin?: Date;
  auteur?: ReactNode;
  statut: StatutEvenement;
  cote?: CoteEvenement;
  details?: ReactNode;
  afficherDuree?: boolean;
};

const FriseContenu = ({
  evenement,
  alignDroit = false,
}: {
  evenement: EvenementFrise;
  alignDroit?: boolean;
}) => {
  return (
    <div
      className={`frise__contenu${alignDroit ? " frise__contenu--aligne-droite" : ""}`}
    >
      <span className="frise__libelle">{evenement.libelle}</span>
      {evenement.date && (
        <span className="frise__date">
          {dateEtHeureSimple(evenement.date)}{" "}
          {evenement.afficherDuree && (
            <>
              {" "}
              - <b>({periode(evenement.date, evenement.dateFin)})</b>
            </>
          )}
          <br />
          {evenement.auteur && <>par {evenement.auteur}</>}
        </span>
      )}
      {evenement.details && (
        <div className="frise__details">{evenement.details}</div>
      )}
    </div>
  );
};

export const Frise = ({ evenements }: { evenements: EvenementFrise[] }) => {
  return (
    <div className="frise fr-col-12">
      {evenements.map((evenement, index) => {
        const estGauche = evenement.cote === "gauche";
        const estDernier = index === evenements.length - 1;

        return (
          <React.Fragment key={index}>
            {/* Colonne gauche */}
            <div
              className={`frise__cellule frise__cellule--gauche${estDernier ? " frise__cellule--derniere" : ""}`}
            >
              {estGauche && <FriseContenu evenement={evenement} alignDroit />}
            </div>

            {/* Indicateur central */}
            <div
              className={`frise__indicateur frise__evenement--${evenement.statut}${estDernier ? " frise__indicateur--dernier" : ""}`}
            >
              <span className="frise__noeud" />
              {!estDernier && <span className="frise__ligne" />}
            </div>

            {/* Colonne droite */}
            <div
              className={`frise__cellule frise__cellule--droite${estDernier ? " frise__cellule--derniere" : ""}`}
            >
              {!estGauche && <FriseContenu evenement={evenement} />}
              {/* Affiché uniquement sur mobile pour les évènements côté gauche */}
              {estGauche && (
                <div className="frise__repli-mobile">
                  <FriseContenu evenement={evenement} />
                </div>
              )}
            </div>
          </React.Fragment>
        );
      })}
    </div>
  );
};
