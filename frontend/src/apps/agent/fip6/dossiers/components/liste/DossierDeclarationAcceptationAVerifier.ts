import { Transform } from "class-transformer";
import "reflect-metadata";

export class DossierDeclarationAcceptationAVerifier {
  readonly id: number;
  readonly reference: string;
  readonly requerant: string;
  readonly montantIndemnisation: number;
  @Transform(({ value }: { value: string }) => new Date(value))
  readonly dateAcceptation: Date;
}
