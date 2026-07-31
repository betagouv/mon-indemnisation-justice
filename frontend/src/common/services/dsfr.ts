export const onDSFRPret = async (onPret: () => void) => {
  while (typeof window.dsfr != "function") {
    await new Promise((resolve) => setTimeout(resolve, 200));
  }

  onPret();
};
