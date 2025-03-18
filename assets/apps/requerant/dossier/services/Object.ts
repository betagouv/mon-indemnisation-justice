export function recursivePatch(original: object, patch: object): object {
  return Object.fromEntries(
    Object.entries(original).map(([key, value]) => {
      if (patch.hasOwnProperty(key)) {
        if (Array.isArray(patch[key]) || null == value) {
          return [key, patch[key]];
        }
        if (value instanceof Object) {
          return patch[key] instanceof Object
            ? [key, recursivePatch(value, patch[key])]
            : [key, patch[key]];
        }

        return [key, patch[key]];
      }

      return [key, value];
    }),
  );
}

export function recursiveMerge(original: object, addition: object): object {
  return Object.fromEntries(
    Object
      // Parcours des clefs définis dans `original` et / ou `addition` ...
      .keys(original)
      .concat(Object.keys(addition))
      // ... dédoublonnés ...
      .reduce((keys: string[], next: string) => {
        if (!keys.includes(next)) {
          keys.push(next);
        }

        return keys;
      }, [])
      // ... et création des nouveaux couples clef/valeur
      .map((key) => {
        if (!original.hasOwnProperty(key)) {
          return [key, addition[key]];
        } else if (!addition.hasOwnProperty(key)) {
          return [key, original[key]];
        } else {
          if (
            original[key] instanceof Object &&
            addition[key] instanceof Object
          ) {
            return [key, recursiveMerge(original[key], addition[key])];
          }

          return [key, addition[key]];
        }
      }),
  );
}
