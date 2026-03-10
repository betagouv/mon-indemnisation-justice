import { Personne } from "@/apps/requerant/models/Personne.ts";
import { Type } from "class-transformer";
import { Adresse } from "./Adresse.ts";
import { type TypePersonneMoraleType } from "./TypePersonneMoraleType.ts";

export class PersonneMorale {
  raisonSociale: string;
  siren: string;
  typePersonneMorale: TypePersonneMoraleType;
  @Type(() => Personne)
  representantLegal: Personne;
  //delegataires?: Personne[];
  //gestionnaires?: Personne[];
  adresse: Adresse;
  paysNaissance: never;
  communeNaissance: never;

  get estPersonneMorale(): boolean {
    return true;
  }
}
