import "reflect-metadata";
import { Transform } from "class-transformer";

export class DossierAAttribuer {
  readonly id: number;
  readonly reference: string;
  readonly requerant: string;
  readonly adresse: string;
  @Transform(({ value }: { value: string | null }) =>
    value ? new Date(value) : undefined,
  )
  readonly dateOperation?: Date;
}
