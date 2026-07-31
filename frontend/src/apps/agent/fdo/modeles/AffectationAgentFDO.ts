import { EtablissementFDO } from "@fdo/modeles/EtablissementFDO.ts";
import DateTransform from "@common/normalisation/transformers/DateTransform.ts";
import { Type } from "class-transformer";

export class AffectationAgentFDO {
  @Type(() => EtablissementFDO)
  public readonly etablissement: EtablissementFDO;
  @DateTransform(true)
  public readonly dateAffectation: Date;
  @DateTransform(true)
  public readonly dateMutation?: Date;

  public get estActive(): boolean {
    return !this.dateMutation;
  }
}
