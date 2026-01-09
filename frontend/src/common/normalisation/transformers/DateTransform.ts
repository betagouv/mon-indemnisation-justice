import { Transform } from "class-transformer";

export default function DateTransform(dateOnly: boolean = false) {
  const toPlain = Transform(
    ({ value }: { value: Date | undefined }) =>
      dateOnly ? value?.toISOString().split("T").at(0) : value?.toISOString(),
    {
      toPlainOnly: true,
    },
  );

  const toClass = Transform(
    ({ value }: { value: number | string | undefined | null }) => {
      return value ? new Date(value) : undefined;
    },
    {
      toClassOnly: true,
    },
  );

  return function (target: any, key: string) {
    toPlain(target, key);
    toClass(target, key);
  };
}
