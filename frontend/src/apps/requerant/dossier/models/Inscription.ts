import { IsEmailAlreadyUsed, IsEqualTo } from "@/common/validation";
import { Expose, Transform } from "class-transformer";
import {
  Equals,
  IsDefined,
  IsEmail,
  IsNotEmpty,
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
  _civilite?: Civilite;
  @IsDefined()
  @IsNotEmpty()
  @Expose({ name: "prenom" })
  _prenom?: string;
  @IsNotEmpty()
  @Expose({ name: "nom" })
  _nom?: string;
  _nomNaissance?: string;
  @IsEmail(undefined, { message: "L'adresse courriel n'est pas valide" })
  @ValidateIf((i) => !!i.courriel)
  @IsEmailAlreadyUsed({ message: "Cette adresse est déjà utilisée" })
  @Expose({ name: "courriel" })
  _courriel?: string;
  @IsNotEmpty()
  _telephone?: string;
  @MinLength(8, {
    message:
      "Le mot de passe doit contenir au moins 8 caractères, dont 1 chiffre",
  })
  _motDePasse?: string;
  @ValidateIf((i) => !!i.motDePasse)
  @IsEqualTo("_motDePasse", {
    message: "Les deux mots de passe doivent être identiques",
  })
  _confirmation?: string = undefined;
  @Equals(true)
  _cguOk: boolean = false;

  constructor() {
    makeAutoObservable(this, {}, { autoBind: true });
  }

  @Expose()
  @Transform(({ value }: { value: Civilite }) =>
    Object.keys(Civilite).find((key) => Civilite[key] === value),
  )
  get civilite(): Civilite | undefined {
    return this._civilite;
  }

  set civilite(value: Civilite) {
    this._civilite = value;
  }

  @Expose()
  get prenom(): string | undefined {
    return this._prenom;
  }

  set prenom(value: string) {
    this._prenom = value;
  }

  @Expose()
  get nom(): string | undefined {
    return this._nom;
  }

  set nom(value: string) {
    this._nom = value;
  }

  @Expose()
  get nomNaissance(): string | undefined {
    return this._nomNaissance;
  }

  set nomNaissance(value: string) {
    this._nomNaissance = value;
  }

  @Expose()
  get courriel(): string | undefined {
    return this._courriel;
  }

  set courriel(value: string) {
    this._courriel = value;
  }

  @Expose()
  get telephone(): string | undefined {
    return this._telephone;
  }

  set telephone(value: string) {
    this._telephone = value;
  }

  @Expose()
  get motDePasse(): string | undefined {
    return this._motDePasse;
  }

  set motDePasse(value: string) {
    this._motDePasse = value;
  }

  @Expose()
  get confirmation(): string | undefined {
    return this._confirmation;
  }

  set confirmation(value: string) {
    this._confirmation = value;
  }

  @Expose()
  get cguOk(): boolean {
    return this._cguOk;
  }

  set cguOk(value: boolean) {
    this._cguOk = value;
  }
}
