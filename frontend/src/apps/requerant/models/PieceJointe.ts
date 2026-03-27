import { Transform } from "class-transformer";
import { PieceJointeType, TypePieceJointe } from "./TypePieceJointe";

export class PieceJointe {
  id?: number;
  chemin: string;
  url: string;
  nom: string;
  mime: string;
  taille: number;
  @Transform(
    ({ value }: { value: PieceJointeType | string }) => {
      return TypePieceJointe.depuis(value as PieceJointeType);
    },
    { toClassOnly: true },
  )
  @Transform(
    ({ value }: { value: TypePieceJointe }) => {
      return value.type;
    },
    { toPlainOnly: true },
  )
  type: TypePieceJointe;

  get typeFichier(): string {
    switch (this.mime) {
      case "application/pdf":
        return "pdf";
      case "image/jpeg":
        return "jpg";
      case "image/png":
        return "png";
      case "image/webp":
        return "webp";
      case "image/gif":
        return "gif";

      default:
        return "";
    }
  }

  public estPDF(): boolean {
    return "application/pdf" === this.mime;
  }

  get tailleFichier(): string {
    if (!this.taille) {
      return "";
    }

    const k = 1024;
    const sizes = ["o", "ko", "mo", "go", "to"];
    const i = Math.floor(Math.log(this.taille) / Math.log(k));
    return (
      parseFloat((this.taille / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
    );
  }

  get infoFichier(): string {
    return `${this.type?.libelle({}) || ""} - ${this.typeFichier?.toUpperCase()}${this.taille ? " - " + this.tailleFichier : ""}`;
  }
}
