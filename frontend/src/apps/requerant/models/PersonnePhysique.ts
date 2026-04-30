import { Personne } from "@/apps/requerant/models/Personne.ts";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import UndefinedTransform from "@/common/normalisation/transformers/UndefinedTransform.ts";
import { Expose, plainToInstance, Transform, Type } from "class-transformer";
import { Adresse } from "./Adresse.ts";
import { Commune } from "./Commune.ts";
import { Pays } from "./Pays.ts";

export class PersonnePhysique {
  @Type(() => Personne)
  @Expose({ toClassOnly: true })
  personne: Personne;
  @UndefinedTransform()
  prenom2?: string;
  @UndefinedTransform()
  prenom3?: string;
  // L'adresse de résidence
  @Type(() => Adresse)
  adresse: Adresse;
  // L'état civil
  @DateTransform(true)
  dateNaissance: Date;
  @Type(() => Pays)
  paysNaissance: Pays = new Pays();
  /** La commune de naissance ne peut être déclarée que pour les usagers nés en France, puisque seules les communes
   *  françaises disposent d'un code INSEE
   */
  @Transform(
    ({ value }) => (value ? plainToInstance(Commune, value) : new Commune()),
    { toClassOnly: true },
  )
  @Type(() => Commune)
  communeNaissance: Commune;
  // Pour les autres usagers, le nom de la ville de naissance doit être renseigné
  villeNaissance?: string;
  //raisonSociale: unknown;

  constructor() {
    this.adresse = new Adresse();
    this.personne = new Personne();
  }

  get estPersonneMorale(): boolean {
    return false;
  }
}
