import { IsEmailAlreadyUsed, IsEqualTo } from "@/common/validation";
import {
  Equals,
  IsDefined,
  IsEmail,
  IsNotEmpty,
  Matches,
  MinLength,
  ValidateIf,
} from "class-validator";
import { makeAutoObservable } from "mobx";

export enum Civilite {
  M = "Monsieur",
  MME = "Madame",
}

export class Inscription {
  @IsDefined({ message: "" })
  _civilite?: Civilite = null;
  @IsDefined()
  @IsNotEmpty()
  _prenom?: string = null;
  @IsNotEmpty()
  _nom?: string = null;
  _nomNaissance?: string = null;
  @IsEmail(undefined, { message: "L'adresse courriel n'est pas valide" })
  @IsEmailAlreadyUsed({ message: "Cette adresse est déjà utilisée" })
  @ValidateIf((i) => !!i.courriel)
  _courriel?: string = null;
  @IsNotEmpty()
  _telephone?: string = null;
  @MinLength(8, {
    message:
      "Le mot de passe doit contenir au moins 8 caractères, dont 1 chiffre",
  })
  _motDePasse?: string = null;
  @ValidateIf((i) => !!i.motDePasse)
  @IsEqualTo("_motDePasse", {
    message: "Les deux mots de passe doivent être identiques",
  })
  _confirmation?: string = null;
  @Equals(true)
  _cguOk: boolean = false;

  constructor() {
    makeAutoObservable(this, {}, { autoBind: true });
  }

  get civilite(): Civilite {
    return this._civilite;
  }

  set civilite(value: Civilite) {
    this._civilite = value;
  }

  get prenom(): string {
    return this._prenom;
  }

  set prenom(value: string) {
    this._prenom = value;
  }

  get nom(): string {
    return this._nom;
  }

  set nom(value: string) {
    this._nom = value;
  }

  get nomNaissance(): string {
    return this._nomNaissance;
  }

  set nomNaissance(value: string) {
    this._nomNaissance = value;
  }

  get courriel(): string {
    return this._courriel;
  }

  set courriel(value: string) {
    this._courriel = value;
  }

  get telephone(): string {
    return this._telephone;
  }

  set telephone(value: string) {
    this._telephone = value;
  }

  get motDePasse(): string {
    return this._motDePasse;
  }

  set motDePasse(value: string) {
    this._motDePasse = value;
  }

  get confirmation(): string {
    return this._confirmation;
  }

  set confirmation(value: string) {
    this._confirmation = value;
  }

  get cguOk(): boolean {
    return this._cguOk;
  }

  set cguOk(value: boolean) {
    this._cguOk = value;
  }
}
