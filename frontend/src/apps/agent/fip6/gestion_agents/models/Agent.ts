import { Administration } from "@/apps/agent/fip6/gestion_agents/models/Administration.ts";
import { Expose } from "class-transformer";
import { computed, makeObservable, observable } from "mobx";

export class Agent {
  public readonly id: number;
  protected _nom: string = "";
  protected _prenom: string = "";
  protected _courriel: string = "";
  private _administration: null | Administration;
  public readonly roles: string[] = [];
  private _datePremiereConnexion: Date;

  constructor() {
    makeObservable(this, {
      _prenom: observable,
      prenom: computed,
      _nom: observable,
      nom: computed,
      _courriel: observable,
      courriel: computed,
    } as any);
  }

  public nomComplet(): string {
    return `${this._prenom} ${this._nom.toUpperCase()}`;
  }

  get datePremiereConnexion(): Date {
    return this._datePremiereConnexion;
  }

  set datePremiereConnexion(value: Date | number) {
    this._datePremiereConnexion =
      typeof value === "number" ? new Date(value) : value;
  }

  get nom(): string {
    return this._nom;
  }

  set nom(value: string) {
    this._nom = value;
  }

  get prenom(): string {
    return this._prenom;
  }

  set prenom(value: string) {
    this._prenom = value;
  }

  get courriel(): string {
    return this._courriel;
  }

  set courriel(value: string) {
    this._courriel = value;
  }

  estCourrielValide(): boolean {
    return (
      this._courriel?.match(
        /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
      )?.length > 0
    );
  }

  @Expose()
  get administration(): Administration | null {
    return this._administration;
  }

  set administration(value: Administration | string | null) {
    this._administration =
      typeof value === "string" ? Administration.resoudre(value) : value;
  }
}
