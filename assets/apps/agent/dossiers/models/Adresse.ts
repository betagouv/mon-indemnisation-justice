export class Adresse {
    public readonly ligne1: string;
    public readonly ligne2: string;
    public readonly codePostal: string;
    public readonly localite: string;

    public libelle(): string {
        return `${this.ligne1} ${this.codePostal} ${this.localite}`
    }
}