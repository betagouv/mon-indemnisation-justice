export class Adresse {
  ligne1: string;
  ligne2?: string;
  codePostal: string;
  commune: string;

  get libelle(): string {
    return `${this.ligne1}, ${this.codePostal} ${this.commune}`;
  }
}
