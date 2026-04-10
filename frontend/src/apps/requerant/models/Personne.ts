import { PersonnePhysique } from "@/apps/requerant/models/PersonnePhysique.ts";
import { capitaliser } from "@/common/services/divers";
import { Exclude } from "class-transformer";
import { Civilite, getCiviliteLibelle } from "./Civilite.ts";

export class Personne {
  id: string;
  civilite: Civilite;
  nom: string;
  nomNaissance: string;
  prenom: string;
  @Exclude()
  prenoms?: string[];
  courriel: string;
  telephone: string;

  personnePhysique?: PersonnePhysique;

  public libelle(): string {
    return `${getCiviliteLibelle(this.civilite)} ${this.prenom} ${capitaliser(this.nom)} `;
  }
}
