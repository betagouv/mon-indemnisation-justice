import { Personne } from "@/apps/requerant/models/Personne.ts";
import { Type } from "class-transformer";
import { Adresse } from "./Adresse.ts";
import { Commune } from "./Commune.ts";
import { Pays } from "./Pays.ts";

export class PersonnePhysique {
  personne: Personne;
  // L'adresse de résidence
  adresse: Adresse = new Adresse();
  // L'état civil
  dateNaissance: Date;
  paysNaissance: Pays;
  /** La commune de naissance ne peut être déclarée que pour les usagers nés en France, puisque seules les communes
   *  françaises disposent d'un code INSEE
   */
  @Type(() => Commune)
  communeNaissance?: Commune;
  // Pour les autres usagers, le nom de la ville de naissance doit être renseigné
  villeNaissance?: string;
  //raisonSociale: unknown;

  get estPersonneMorale(): boolean {
    return false;
  }
}
