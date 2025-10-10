import { BaseDossier, Document } from "@/common/models";
import { plainToInstance } from "class-transformer";
import { data } from "autoprefixer";
import { reject } from "lodash";

interface DocumentManagerInterface {
  imprimer(document: Document, corps?: string): Promise<Document>;

  genererCourrierPropositionIndemnisation(
    dossier: BaseDossier,
    montantIndemnisation: number,
  ): Promise<Document>;

  genererCourrierRejet(
    dossier: BaseDossier,
    motifRejet: string,
  ): Promise<Document>;

  genererDeclarationAcceptation(
    dossier: BaseDossier,
    montantIndemnisation: number,
  ): Promise<Document>;

  genererArretePaiement(dossier: BaseDossier): Promise<Document>;
}

class APIDocumentManager implements DocumentManagerInterface {
  async imprimer(document: Document, corps?: string): Promise<Document> {
    return new Promise(async (resolve, reject) => {
      const response = await fetch(
        `/api/agent/fip6/document/${document.id}/imprimer`,
        {
          headers: {
            "Content-type": "application/json",
          },
          method: "PUT",
          body: JSON.stringify({ corps: corps || document.corps }),
        },
      );

      if (response.ok) {
        const data = await response.json();

        resolve(plainToInstance(Document, data));
      } else {
        reject("Une erreur est survenue lors de l'appel à l'API");
      }
    });
  }

  async genererCourrierPropositionIndemnisation(
    dossier: BaseDossier,
    montantIndemnisation: number,
  ): Promise<Document> {
    const response = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/generer-courrier-proposition-indemnisation`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          montantIndemnisation,
        }),
      },
    );

    const data = await response.json();

    if (response.ok) {
      return plainToInstance(Document, data.document);
    }

    throw new Error(
      data.erreur ??
        "Une erreur indéterminée est survenue lors de la génération de courrier de proposition d'indemnisation",
    );
  }

  async genererCourrierRejet(
    dossier: BaseDossier,
    motifRejet: string,
  ): Promise<Document> {
    const response = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/generer-courrier-rejet`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          motifRejet,
        }),
      },
    );

    const data = await response.json();

    if (response.ok) {
      return plainToInstance(Document, data.document);
    }

    throw new Error(
      data.erreur ??
        "Une erreur indéterminée est survenue lors de la génération de courrier de proposition d'indemnisation",
    );
  }

  async genererDeclarationAcceptation(
    dossier: BaseDossier,
    montantIndemnisation: number,
  ): Promise<Document> {
    const response = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/generer-declaration-acceptation`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          montantIndemnisation,
        }),
      },
    );

    const data = await response.json();

    if (response.ok) {
      return plainToInstance(Document, data.document);
    }

    throw new Error(
      data.erreur ??
        "Une erreur indéterminée est survenue lors de la génération de la déclaration d'acceptation",
    );
  }

  async genererArretePaiement(dossier: BaseDossier): Promise<Document> {
    // Appel à l'API pour valider le document
    const response = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/generer-arrete-paiement`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();

    if (response.ok) {
      return plainToInstance(Document, data.document);
    }

    throw new Error(
      data.erreur ??
        "Une erreur indéterminée est survenue lors de la génération de l'arrêté de paiement",
    );
  }
}

export { type DocumentManagerInterface, APIDocumentManager };
