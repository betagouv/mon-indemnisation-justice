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

  @Transform(({ value }: { value: unknown }) => Array.isArray(value) ? value as TypeDecision[] : [])
  public typeDecision: TypeDecision[] = [];

  @Transform(({ value }: { value: unknown }) => Array.isArray(value) ? value as PieceProcedure[] : [])
  public piecesProc: PieceProcedure[] = [];

  @Transform(({ value }: { value: boolean | string | undefined }) => value === undefined ? undefined : value === true || value === "oui")
  public preuvesDiligences?: boolean;

  get estEligible(): boolean {
    return (
      calculerPrescription(this.dateDecision).rempli &&
      this.actionContentieuse === ActionContentieuse.Non &&
      this.typeDecision.length > 0 &&
      !this.typeDecision.includes(TypeDecision.Aucune) &&
      this.piecesProc.length > 0 &&
      this.preuvesDiligences === true
    );
  }
}
