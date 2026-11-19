import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React, { ReactNode, useState } from "react";

/**
 * Définition d'un composant `Parcours` qui étend le `Stepper` du DSFR avec une liste
 * d'étapes définies sous forme de titre et composant dans leur ordre d'apparition.
 *
 * Des primitives telles que `setEtape()` ou `suivante()` sont accessibles à chaque
 * définition d'étape ainsi qu'au composant parent, de même que des informations d'état
 * comme l'étape actuelle, le nombre d'étapes, etc...
 */

export type ComposantEtape<E extends string> =
  ReactNode | ((parcours: EtatParcours<E>) => ReactNode);

export type DefinitionEtape<E extends string> = {
  titre: string;
  composant: ComposantEtape<E>;
};

/**
 * Liste des étapes définies par ordre de déclaration, les noms d'étape en valeur numérique sont donc refusés.
 */
export type DefinitionsEtapes<E extends string> = {
  [K in E]: K extends `${number}` ? never : DefinitionEtape<E>;
};

export type EtatParcours<E extends string> = {
  /** Étape courante */
  readonly etape: E;
  /** Liste ordonnée des noms d'étapes */
  readonly etapes: readonly E[];
  /** Rang de l'étape courante, démarrant à "1" */
  readonly rang: number;
  readonly nombreEtapes: number;
  readonly titre: string;
  readonly titreSuivant?: string;
  readonly estPremiere: boolean;
  readonly estDerniere: boolean;
  readonly definitions: DefinitionsEtapes<E>;
  setEtape(etape: E): void;
  suivante(): void;
  precedente(): void;
};

/**
 * _Hook_ d'initialisation d'un parcours à partir de la définition ordonnée de ses étapes. La première étape est sélectionnée par
 * défaut.
 *
 * @example
 * const parcours = useParcours({
 *   CHOIX: { titre: "Choix", composant: ({ suivante }) => <Choix onChoisir={suivante} /> },
 *   SIGNATURE: { titre: "Signature", composant: <Signature /> },
 * });
 *
 * parcours.setEtape("SIGNATURE");
 *
 * return <Parcours parcours={parcours} />;
 */
export function useParcours<E extends string>(
  definitions: DefinitionsEtapes<E>,
): EtatParcours<E> {
  const etapes = Object.keys(definitions) as E[];

  if (etapes.length === 0) {
    throw new Error("Un parcours doit définir au moins une étape");
  }

  const [etapeCourante, setEtape] = useState<E>(etapes[0]);

  // Si les définitions changent et que l'étape courante disparaît, on se replie sur la première
  const etape = etapes.includes(etapeCourante) ? etapeCourante : etapes[0];
  const index = etapes.indexOf(etape);
  const suivante = etapes[index + 1] as E | undefined;
  const precedente = etapes[index - 1] as E | undefined;

  return {
    etape,
    etapes,
    rang: index + 1,
    nombreEtapes: etapes.length,
    titre: (definitions[etape] as DefinitionEtape<E>).titre,
    titreSuivant: suivante
      ? (definitions[suivante] as DefinitionEtape<E>).titre
      : undefined,
    estPremiere: index === 0,
    estDerniere: index === etapes.length - 1,
    definitions,
    setEtape,
    suivante: () => {
      if (suivante) {
        setEtape(suivante);
      }
    },
    precedente: () => {
      if (precedente) {
        setEtape(precedente);
      }
    },
  };
}

/**
 * Affiche le bandeau d'étapes du DSFR ainsi que l'étape courante.
 */
export function Parcours<E extends string>({
  parcours,
  className,
}: {
  parcours: EtatParcours<E>;
  className?: string;
}) {
  const composant = (parcours.definitions[parcours.etape] as DefinitionEtape<E>)
    .composant;

  return (
    <>
      <Stepper
        className={className}
        currentStep={parcours.rang}
        stepCount={parcours.nombreEtapes}
        title={parcours.titre}
        nextTitle={parcours.titreSuivant}
      />

      {typeof composant === "function" ? composant(parcours) : composant}
    </>
  );
}
