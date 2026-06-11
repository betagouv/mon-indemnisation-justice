import DateTransform from "@/common/normalisation/transformers/DateTransform";
import { calculerPrescription } from "@/apps/public/services/prescription";
import { Transform } from "class-transformer";
import {
  ActionContentieuse,
  PieceProcedure,
  TypeDecision,
} from "@/apps/public/components/types";

export class TestEligibilite {
  public procedureTerminee?: boolean;

  @DateTransform(true)
  public dateDecision?: Date;

  public actionContentieuse?: ActionContentieuse;

  public typeDecision?: TypeDecision;

  @Transform(({ value }: { value: unknown }) => (Array.isArray(value) ? value : []) as PieceProcedure[])
  public piecesProc: PieceProcedure[] = [];

  @Transform(({ value }: { value: boolean | string }) => value === true || value === "oui")
  public preuvesDiligences?: boolean;

  get estEligible(): boolean {
    return (
      calculerPrescription(this.dateDecision).rempli &&
      this.actionContentieuse === ActionContentieuse.Non &&
      this.typeDecision !== undefined &&
      this.typeDecision !== TypeDecision.Aucune &&
      this.piecesProc.length > 0 &&
      this.preuvesDiligences === true
    );
  }
}
