import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import "reflect-metadata";

export class DossierAInstruire {
  readonly id: number;
  readonly reference: string;
  readonly requerant: string;
  readonly adresse: string;
  @DateTransform(true)
  readonly dateOperation?: Date;
  @DateTransform(true)
  readonly datePublication: Date;
  readonly estEligible: boolean;
}

export class DossierEnInstruction extends DossierAInstruire {
  @DateTransform(true)
  readonly dateDebutInstruction: Date;
}
