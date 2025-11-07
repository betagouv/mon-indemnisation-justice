import { BaseDossier, Document, DocumentType } from "@/common/models";
import { ServiceIdentifier } from "inversify";
import { plainToInstance } from "class-transformer";

export interface DossierManagerInterface {
  ajouterPieceJointe(
    dossier: BaseDossier,
    type: DocumentType,
    fichier: File,
  ): Promise<Document>;
}

export namespace DossierManagerInterface {
  export const $: ServiceIdentifier<DossierManagerInterface> = Symbol(
    "DossierManagerInterface",
  );
}

export class APIDossierManager implements DossierManagerInterface {
  async ajouterPieceJointe(
    dossier: BaseDossier,
    type: DocumentType,
    fichier: File,
  ): Promise<Document> {
    const payload = new FormData();
    payload.append("pieceJointe", fichier);

    const response = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/ajouter-piece-jointe/${type.type}`,
      {
        method: "POST",
        body: payload,
      },
    );

    const data = await response.json();

    if (response.ok) {
      return plainToInstance(Document, data);
    }

    throw new Error(
      data?.erreur ??
        "Une erreur est survenue lors de l'envoi de la pièce jointe",
    );
  }
}
