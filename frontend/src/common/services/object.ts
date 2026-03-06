/**
 * Calcule le différentiel d'un object `b` vis-à-vis d'un object `a` en retournant
 * un object, en profondeur, contenant toutes les entrées de `b` existantes dans
 * `a` mais dont les valeurs sont différentes.
 *
 * @param a
 * @param b
 */
export function differentiel(a: any, b: any): any {
  // Si les types divergent, retourner b
  if (typeof a !== typeof b) {
    return b;
  }

  // Cas des types primitifs
  if (a === null || b === null || typeof a !== "object") {
    return a !== b ? b : undefined;
  }

  // Gestion des tableaux
  if (Array.isArray(a) && Array.isArray(b)) {
    // Si les tableaux n'ont pas le même nombre d'éléments, retourner b
    if (a.length !== b.length) {
      return b;
    }
    // Sinon au moindre élément différent, recursivement, retourner b
    for (let i = 0; i < a.length; i++) {
      const diff = differentiel(a[i], b[i]);
      if (diff !== undefined) {
        return b;
      }
    }
    return undefined;
  }

  // Gestion des objets
  if (!Array.isArray(a) && !Array.isArray(b)) {
    // On stocke les écarts dans un objet
    const diff: Record<string, any> = {};
    // On établit la liste de toutes les clefs présentes dans au moins un des deux objets
    const clefsUniques = new Set([...Object.keys(a), ...Object.keys(b)]);

    for (const key of clefsUniques) {
      const diff = differentiel(a[key], b[key]);
      // Chaque élément différent, recursivement, est stocké dans l'objet diff
      if (diff !== undefined) {
        diff[key] = diff;
      }
    }

    // On retourne l'objet s'il existe la moindre différence, sinon undefined
    return Object.keys(diff).length > 0 ? diff : undefined;
  }

  // En cas de comparaison entre un tableau et un objet, retourne `b`
  return b;
}

/**
 * Calcule la fusion récursive de deux objets.
 *
 * @param cible
 * @param source
 */
export function fusion(cible: any, source: any): any {
  // Si la `source` est _nullish_, retourner `cible`
  if (source === undefined || source === null) {
    return cible;
  }

  // Si la `cible` est _nullish_, retourner `source`
  if (cible === undefined || cible === null) {
    return source;
  }

  // Gestion des types primitifs
  if (typeof source !== "object") {
    return source;
  }

  // Gestion des tableaux
  if (Array.isArray(source)) {
    return source;
  }

  // Gestion des objets
  if (!Array.isArray(cible) && typeof cible === "object") {
    // On stocke l'union des deux objets dans un objet
    const union: Record<string, any> = { ...cible };

    for (const key in source) {
      if (source.hasOwnProperty(key)) {
        // Fusionner récursivement si les deux valeurs sont des objets
        if (
          typeof source[key] === "object" &&
          source[key] !== null &&
          typeof union[key] === "object" &&
          union[key] !== null &&
          !Array.isArray(source[key]) &&
          !Array.isArray(union[key])
        ) {
          union[key] = fusion(union[key], source[key]);
        } else {
          // Autrement, `source` écrase `cible`
          union[key] = source[key];
        }
      }
    }

    return union;
  }

  // Si `cible` n'est pas un objet, retourner `source`
  return source;
}
