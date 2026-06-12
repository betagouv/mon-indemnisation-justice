import { dateEtHeureSimple, periode } from "@/common/services/date.ts";
import React, { ReactNode } from "react";
import "./Frise.css";

export type StatutEvenement = "passe" | "actuel" | "futur";
export type CoteEvenement = "gauche" | "droite";

export type EvenementFrise = {
  libelle: string;
  date: Date;
  dateFin?: Date;
  auteur?: ReactNode;
  statut: StatutEvenement;
  cote?: CoteEvenement;
  details?: ReactNode;
};

const FriseContenu = ({
  evenement,
  alignDroit = false,
  afficherDuree = false,
}: {
  evenement: EvenementFrise;
  alignDroit?: boolean;
  afficherDuree?: boolean;
}) => {
  console.log(
    afficherDuree,
    evenement.dateFin,
    afficherDuree && !!evenement.dateFin,
    afficherDuree && !!evenement.dateFin
      ? periode(evenement.date, evenement.dateFin)
      : undefined,
  );
  return (
    <div
      className={`frise__contenu${alignDroit ? " frise__contenu--aligne-droite" : ""}`}
    >
      <span className="frise__libelle">{evenement.libelle}</span>
      {evenement.date && (
        <span className="frise__date">
          {dateEtHeureSimple(evenement.date)}{" "}
          {afficherDuree && (
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

export const Frise = ({
  evenements,
  afficherDurees = false,
}: {
  evenements: EvenementFrise[];
  afficherDurees?: boolean;
}) => {
  console.log(afficherDurees);

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
              {estGauche && (
                <FriseContenu
                  evenement={evenement}
                  afficherDuree={afficherDurees}
                  alignDroit
                />
              )}
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
              {!estGauche && (
                <FriseContenu
                  evenement={evenement}
                  afficherDuree={afficherDurees}
                />
              )}
              {/* Affiché uniquement sur mobile pour les évènements côté gauche */}
              {estGauche && (
                <div className="frise__repli-mobile">
                  <FriseContenu
                    evenement={evenement}
                    afficherDuree={afficherDurees}
                  />
                </div>
              )}
            </div>
          </React.Fragment>
        );
      })}
    </div>
  );
};
