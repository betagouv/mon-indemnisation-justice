/**
 * Logique des champs « segmentés » : une valeur découpée en plusieurs champs de longueur fixe (ex : les 4 champs d'un
 * RIB), qui se comportent comme une seule zone de saisie.
 */

export type PositionCurseur = { index: number; position: number };

/**
 * Applique la saisie faite dans le segment `index` : ce qui dépasse sa longueur déborde sur les segments suivants (en
 * remplaçant leurs premiers caractères), ce qui permet de coller une valeur complète dans n'importe quel segment.
 *
 * @param valeurs   valeurs actuelles des segments
 * @param longueurs longueur de chaque segment
 * @param index     segment modifié
 * @param saisie    nouvelle valeur (déjà filtrée) du segment modifié, éventuellement plus longue que celui-ci
 * @param curseur   position du curseur dans `saisie` après modification
 *
 * @return les nouvelles valeurs, et le segment et la position où placer le curseur
 */
export const repartirSaisie = (
  valeurs: string[],
  longueurs: number[],
  index: number,
  saisie: string,
  curseur: number,
): { valeurs: string[]; curseur: PositionCurseur } => {
  const resultat = [...valeurs];
  resultat[index] = saisie.slice(0, longueurs[index]);

  let reste = saisie.slice(longueurs[index]);
  for (let j = index + 1; j < longueurs.length && reste.length > 0; j++) {
    const morceau = reste.slice(0, longueurs[j]);
    resultat[j] = (morceau + (resultat[j] ?? "").slice(morceau.length)).slice(
      0,
      longueurs[j],
    );
    reste = reste.slice(longueurs[j]);
  }

  return {
    valeurs: resultat,
    curseur: localiserCurseur(longueurs, index, curseur),
  };
};

/**
 * Convertit une position relative au début du segment `index` (qui peut dépasser sa longueur) en segment et position
 * : un segment rempli jusqu'au bout fait passer au suivant.
 */
export const localiserCurseur = (
  longueurs: number[],
  index: number,
  position: number,
): PositionCurseur => {
  let segment = index;
  let decalage = Math.max(0, position);

  while (segment + 1 < longueurs.length && decalage >= longueurs[segment]) {
    decalage -= longueurs[segment];
    segment++;
  }

  return {
    index: segment,
    position: Math.min(decalage, longueurs[segment]),
  };
};
