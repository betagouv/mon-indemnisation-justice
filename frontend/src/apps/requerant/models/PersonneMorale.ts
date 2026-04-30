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
  @Type(() => Adresse)
  adresse: Adresse;
  paysNaissance: never;
  communeNaissance: never;
  villeNaissance: never;

  constructor() {
    this.adresse = new Adresse();
    this.representantLegal = new Personne();
  }

  get estPersonneMorale(): boolean {
    return true;
  }
}
