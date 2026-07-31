export const dsfr = async (
  element: HTMLElement | null,
  pasMs: number = 150,
  delai: number = 2000,
): Promise<any | undefined> => {
  let tempsRestant = delai;
  while (element && window.dsfr(element) == null && tempsRestant > 0) {
    await new Promise((resolve) => setTimeout(resolve, pasMs));
    tempsRestant -= pasMs;
  }

  return element ? window.dsfr(element) : undefined;
};
