import { RapportAuLogement } from "./RapportAuLogement";
import { Transform } from "class-transformer";

export class TestEligibilite {
  public estVise: boolean;
  public estHebergeant: boolean;
  @Transform(({ value }: { value: string }) => {
    return RapportAuLogement[value];
  })
  public rapportAuLogement: RapportAuLogement;
  public aContacteAssurance: boolean;
  public aContacteBailleur?: boolean;

  get estProprietaire(): boolean {
    return [RapportAuLogement.BAI, RapportAuLogement.PRO].includes(
      this.rapportAuLogement,
    );
  }
}
