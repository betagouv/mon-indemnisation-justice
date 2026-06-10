export const contenuFichier = (file: File): string => {
  const blob = new Blob([file], { type: file.type }); // e.g., 'image/png'
  return URL.createObjectURL(blob);
};
