export class Adresse {
  public readonly ligne1: string;
  public readonly ligne2: string;
  public readonly codePostal: string;
  public readonly localite: string;

  /**
   * Une adresse est considérée comme renseignée si au moins un des champs `ligne1`, `codePostal` ou `localite` est renseigné (i.e. la valeur
   * est non _nullish_).
   */
  public estRenseignee(): boolean {
    return !!this.ligne1 && !!this.codePostal && !!this.localite;
  }

  public libelle(): string {
    return `${this.ligne1} ${this.codePostal} ${this.localite}`;
  }
}
