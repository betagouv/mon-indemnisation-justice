/**
 * Attend que la lib JS du DSFR soit chargée et ait pris en charge `element`, puis retourne ce qu'en extrait
 * `extraire` (par défaut, l'instance DSFR de l'élément elle-même).
 *
 * `extraire` permet d'attendre qu'un composant précis soit instancié, par exemple `(instance) => instance.modal` pour
 * une modale : la lib JS du DSFR instancie en effet les composants de manière asynchrone, après leur ajout au DOM.
 *
 * Retourne `undefined` si l'élément est absent, ou si rien n'a pu être extrait au bout de `delaiMs`.
 */
export const dsfr = async <T = any>(
  element: HTMLElement | null,
  extraire: (instance: any) => T | undefined = (instance) => instance,
  pasMs: number = 50,
  delaiMs: number = 3000,
): Promise<T | undefined> => {
  if (!element) {
    return undefined;
  }

  for (let ecoule = 0; ecoule <= delaiMs; ecoule += pasMs) {
    if (typeof window.dsfr == "function") {
      try {
        const resultat = extraire(window.dsfr(element));
        if (resultat) {
          return resultat;
        }
      } catch (e) {
        // Le composant n'est pas encore instancié par le DSFR : on réessaie
      }
    }

    await new Promise((resolve) => setTimeout(resolve, pasMs));
  }

  return undefined;
};
