import UndefinedTransform from "@/common/normalisation/transformers/UndefinedTransform.ts";

export class Adresse {
  ligne1: string;
  @UndefinedTransform()
  ligne2?: string;
  codePostal: string;
  commune: string;

  get libelle(): string {
    let [preposition, commune] = ["à", this.commune];

    // Ex : "à Le Havre" => "au Havre"
    if (this.commune.match(/le /i)) {
      preposition = "au";
      commune = this.commune.replace(/le /i, "");
    }

    // Ex : "à Les Herbiers" => "aux Herbiers"
    if (this.commune.match(/les /i)) {
      preposition = "aux";
      commune = this.commune.replace(/les /i, "");
    }

    return `${this.ligne1} ${preposition} ${commune.toUpperCase()} (${this.codePostal})`;
  }
}
