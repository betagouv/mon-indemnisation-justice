import { Transform } from "class-transformer";

/**
 * Convertit un objet _null_ en _undefined_.
 *
 * @constructor
 */
export default function UndefinedTransform() {
  const toClass = Transform(
    ({ value }: { value: any | undefined | null }) => {
      return value != null ? value : undefined;
    },
    {
      toClassOnly: true,
    },
  );

  return function (target: any, key: string) {
    toClass(target, key);
  };
}
