export const capitaliser = (nom: string): string => {
  return nom.at(0)?.toUpperCase() + nom.slice(1);
};
