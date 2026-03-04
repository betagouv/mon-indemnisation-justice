export const formaterDate = (date: Date): string | undefined => {
  return date.toISOString().split("T").at(0);
};
