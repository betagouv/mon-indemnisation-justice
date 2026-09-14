import {
  ActionContentieuse,
  PieceProcedure,
  TypeDecision,
} from "@/apps/public/components/types";
import { calculerPrescription } from "@/apps/public/services/prescription";
import DateTransform from "@/common/normalisation/transformers/DateTransform";
import { Transform } from "class-transformer";

export type TypePreuveDiligence =
  | "avec_justificatifs"
  | "sans_justificatif"
  | "pas_de_demarche";

export const LibellesPreuveDiligence: Record<TypePreuveDiligence, string> = {
  avec_justificatifs: "Je dispose de justificatifs de mes démarches",
  sans_justificatif:
    "J’ai effectué des démarches, mais je ne dispose pas de justificatifs",
  pas_de_demarche: "Je n’ai effectué aucune démarche auprès de la juridiction",
};

export class TestEligibilite {
  public procedureTerminee?: boolean;

  @DateTransform(true)
  public dateDecision?: Date;

  public actionContentieuse?: ActionContentieuse;

  @Transform(({ value }: { value: unknown }) =>
    Array.isArray(value) ? (value as TypeDecision[]) : [],
  )
  public typeDecision: TypeDecision[] = [];

  @Transform(({ value }: { value: unknown }) =>
    Array.isArray(value) ? (value as PieceProcedure[]) : [],
  )
  public piecesProc: PieceProcedure[] = [];

  public preuvesDiligences?: TypePreuveDiligence;

  get estEligible(): boolean {
    return (
      new Date() < (calculerPrescription(this.dateDecision) as Date) &&
      this.actionContentieuse === ActionContentieuse.Non &&
      this.typeDecision.length > 0 &&
      !this.typeDecision.includes(TypeDecision.Aucune) &&
      this.piecesProc.length > 0 &&
      this.preuvesDiligences != "pas_de_demarche"
    );
  }
}
