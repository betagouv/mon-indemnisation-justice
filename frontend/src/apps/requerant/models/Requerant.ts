import { Civilite } from "./Civilite.ts";
import { Commune } from "./Commune.ts";
import { Adresse } from "./Adresse.ts";
import { Pays } from "./Pays.ts";

export class Requerant {
  estPersonneMorale: boolean;
  raisonSociale: string;
  siren: string;
  estRepresentantLegal?: boolean;
  civiliteRepresentantLegal?: string;
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
  adresse: Adresse;
  dateNaissance: Date;
  paysNaissance: Pays;
  communeNaissance?: Commune;
}
