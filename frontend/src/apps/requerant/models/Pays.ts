export class Pays {
  code: string;
  nom: string;

  estFrance(): boolean {
    return this.code === "FRA";
  }

  static from(code: string, nom: string): Pays {
    const pays = new Pays();
    pays.code = code;
    pays.nom = nom;

    return pays;
  }
}
