import { Transform } from "class-transformer";

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
