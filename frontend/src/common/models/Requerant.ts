import { type TypePersonneMoraleType, TypesPersonneMorale } from "@/common/models/TypePersonneMorale";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import { Expose, Transform } from "class-transformer";

export type Civilite = "M" | "MME";

export class Requerant {
  public readonly civilite: Civilite;
  public readonly nom: string;
  public readonly nomNaissance: string | null;
  public readonly prenoms: string[];
  public readonly courriel: string;
  public readonly telephone?: string;
  protected _dateNaissance: Date | null;
  public readonly communeNaissance: string | null;
  public readonly paysNaissance: string | null;
  @Transform(({ value }) =>
    TypesPersonneMorale.includes(value) ? value : undefined,
  )
  public readonly typePersonneMorale?: TypePersonneMoraleType;
  public readonly raisonSociale: string | null;
  public readonly siren: string | null;

  estFeminin(): boolean {
    return this.civilite == "MME";
  }

  get prenom(): string {
    return this.prenoms[0] ?? "";
  }

  @Expose()
  @DateTransform()
  get dateNaissance(): null | Date {
    return this._dateNaissance;
  }

  set dateNaissance(dateNaissance: Date | number) {
    this._dateNaissance =
      typeof dateNaissance === "number"
        ? new Date(dateNaissance)
        : dateNaissance;
  }

  estPersonneMorale(): boolean {
    return this.raisonSociale !== null;
  }

  civiliteComplete(): string {
    return this.civilite === "MME" ? "Madame" : "Monsieur";
  }

  nomSimple(
    { capitaliser = false }: { capitaliser: boolean } = { capitaliser: false },
  ): string {
    return `${this.prenom} ${capitaliser ? this.nom.toUpperCase() : this.nom}`;
  }

  nomComplet(): string {
    return (
      `${this.civiliteComplete()} ${this.nom}` +
      (this.nomNaissance
        ? `, né${this.estFeminin() ? "e" : ""} ${this.nomNaissance}`
        : "")
    );
  }
}
