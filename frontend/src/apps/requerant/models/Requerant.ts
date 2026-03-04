import { Type } from "class-transformer";
import { Adresse } from "./Adresse.ts";
import { Civilite } from "./Civilite.ts";
import { Commune } from "./Commune.ts";
import { Pays } from "./Pays.ts";
import { PersonneMoraleType } from "./PersonneMoraleType.ts";

export class Requerant {
  estPersonneMorale: boolean;
  typePersonneMorale?: PersonneMoraleType;
  raisonSociale: string;
  siren: string;
  estRepresentantLegal?: boolean;
  civiliteRepresentantLegal?: Civilite;
  nomRepresentantLegal?: string;
  nomNaissanceRepresentantLegal?: string;
  prenomRepresentantLegal?: string;
  courrielRepresentantLegal?: string;
  telephoneRepresentantLegal?: string;
  // Dans le cas d'une personne morale, les informations sont celles du représentant légal ou délégué
  civilite: Civilite;
  nom: string;
  nomNaissance: string;
  prenom: string;
  courriel: string;
  telephone: string;
  adresse: Adresse = new Adresse();
  dateNaissance: Date;
  paysNaissance: Pays;
  /** La commune de naissance ne peut être déclarée que pour les usagers nés en France, puisque seules les communes
   *  françaises disposent d'un code INSEE
   */
  @Type(() => Commune)
  communeNaissance?: Commune;
  // Pour les autres usagers, le nom de la ville de naissance doit être renseigné
  villeNaissance?: string;
}
