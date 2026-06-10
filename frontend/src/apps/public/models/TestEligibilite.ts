import DateTransform from "@/common/normalisation/transformers/DateTransform";
import { calculerPrescription } from "@/apps/public/services/prescription";
import { Transform } from "class-transformer";
import {
  ActionContentieuse,
  PieceProcedure,
  TypeDecision,
} from "@/apps/public/components/types";

export class TestEligibilite {
  @DateTransform(true)
  public dateDecision?: Date;

  @Transform(({ value }: { value: string }) => value as ActionContentieuse)
  public actionContentieuse?: ActionContentieuse;

  @Transform(({ value }: { value: unknown }) => (Array.isArray(value) ? value : []) as TypeDecision[])
  public typeDecision: TypeDecision[] = [];

  @Transform(({ value }: { value: string[] }) => value as PieceProcedure[])
  public piecesProc: PieceProcedure[] = [];

  @Transform(({ value }: { value: unknown }) => value === true || value === "oui")
  public preuvesDiligences?: boolean;

  get estEligible(): boolean {
    return (
      calculerPrescription(this.dateDecision).rempli &&
      this.actionContentieuse === ActionContentieuse.Non &&
      this.typeDecision.length > 0 &&
      this.piecesProc.length > 0 &&
      this.preuvesDiligences === true
    );
  }
}
