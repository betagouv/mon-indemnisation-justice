import { Transform } from "class-transformer";

export default function DateTransform() {
  const toPlain = Transform(
    ({ value }: { value: Date | undefined }) => value?.toISOString(),
    {
      toPlainOnly: true,
    },
  );

  const toClass = Transform(
    ({ value }: { value: number | string }) => new Date(value),
    {
      toClassOnly: true,
    },
  );

  return function (target: any, key: string) {
    toPlain(target, key);
    toClass(target, key);
  };
}
