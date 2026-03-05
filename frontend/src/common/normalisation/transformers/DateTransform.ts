import { dateChiffre } from "@/common/services/date.ts";
import { Transform } from "class-transformer";

export default function DateTransform(dateOnly: boolean = false) {
  const toPlain = Transform(
    ({ value }: { value: Date | undefined }) => {
      return dateOnly ? dateChiffre(value) : value?.toISOString();
    },
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
