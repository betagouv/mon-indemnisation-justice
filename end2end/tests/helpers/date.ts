export const hier = () => dateIlYaNJours(1);

export const dateIlYaNJours = (n: number) =>
  new Date(Date.now() - n * 24 * 60 * 60 * 1000);

export const dateDansNJours = (n: number) =>
  new Date(Date.now() + n * 24 * 60 * 60 * 1000);

export const formatYmd = (date: Date) => date.toISOString().split("T")[0];
