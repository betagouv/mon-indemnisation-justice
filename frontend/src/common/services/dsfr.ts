export const dsfr = async (
  element: HTMLElement | null,
  pasMs: number = 150,
  delai: number = 3000,
): Promise<any | undefined> => {
  let tempsRestant = delai;
  while (element && tempsRestant > 0) {
    await new Promise((resolve) => setTimeout(resolve, pasMs));
    tempsRestant -= pasMs;
    if (typeof window.dsfr == "function") {
      try {
        return window.dsfr(element);
      } catch (e) {
        console.warn(e);
      }
    }
  }

  return element ? window.dsfr(element) : undefined;
};
