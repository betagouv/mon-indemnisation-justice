import { PersonnePhysique } from "@/apps/requerant/models/PersonnePhysique.ts";
import { Exclude } from "class-transformer";
import { Civilite } from "./Civilite.ts";

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
}
