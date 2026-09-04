import {
  Agent,
  BaseDossier,
  Document,
  DocumentType,
  DossierApercu,
  DossierDetail,
  Redacteur,
} from "@/common/models";
import { RoleAgent } from "@/common/models/Agent.ts";
import { dateChiffre } from "@/common/services/date.ts";
import {
  RechercheReponse,
  RechercheRequete,
  requeteVersUrl,
} from "@fip6/composants/pages/RechercherDossierPage.tsx";
import { queryClient } from "@fip6/query.ts";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

export type ListeDossier =
  | "a-categoriser"
  | "a-attribuer"
  | "a-instruire"
  | "en-instruction"
  | "rejet-a-signer"
  | "proposition-a-signer"
  | "a-verifier"
  | "arrete-a-signer"
  | "a-transmettre"
  | "en-attente-indemnisation";

export type CompteurDossiers = Record<ListeDossier, number>;

export interface DossierManagerInterface {
  compteursDossiers(agent: Agent): Promise<CompteurDossiers>;

  rechercher(requete: RechercheRequete): Promise<RechercheReponse>;

  consulter(id: number): Promise<DossierDetail>;

  televerserPieceJointe(
    dossier: BaseDossier,
    type: DocumentType,
    fichier: File,
  ): Promise<Document>;

  ajouterDocument(dossier: DossierDetail, document: Document): void;

  attribuer(dossier: BaseDossier, redacteur: Redacteur): Promise<void>;

  transmettreAFIP3(dossier: BaseDossier): Promise<void>;

  marquerIndemnise(
    dossier: BaseDossier,
    dateIndemnisation: Date,
  ): Promise<void>;
}

export namespace DossierManagerInterface {
  export const $: ServiceIdentifier<DossierManagerInterface> = Symbol(
    "DossierManagerInterface",
  );
}

export class APIDossierManager implements DossierManagerInterface {
  compteursDossiers(agent: Agent): Promise<CompteurDossiers> {
    return queryClient.fetchQuery({
      queryKey: ["DossierManagerInterface", "compteursDossiers"],
      queryFn: async (): Promise<CompteurDossiers> => {
        // Si l'agent n'a pas le rôle DOSSIER, évitons un appel inutile à l'API
        if (!agent.aRole(RoleAgent.DOSSIER)) {
          return {} as CompteurDossiers;
        }
        const reponse = await fetch("/api/agent/fip6/decompter-dossiers");
        if (!reponse.ok) {
          throw new Error(
            `Failed to fetch compteurs dossiers: ${reponse.status}`,
          );
        }
        return (await reponse.json()) as CompteurDossiers;
      },
      staleTime: 5 * 60 * 1000, // 5 minutes
    });
  }

  async rechercher(requete: RechercheRequete): Promise<RechercheReponse> {
    const reponse = await fetch(
      `/api/agent/fip6/dossiers/rechercher?${requeteVersUrl(requete)}`,
    );
    const data = await reponse.json();

    return {
      resultats: plainToInstance(DossierApercu, data.resultats as any[]),
      taille: data.taille,
      total: data.total,
      page: data.page,
    };
  }

  protected recupererDossier(id: number): Promise<DossierDetail> {
    return queryClient.fetchQuery<DossierDetail>({
      queryKey: ["DossierManagerInterface", "dossier", id],
      queryFn: async (): Promise<DossierDetail> => {
        const reponse = await fetch(`/api/agent/fip6/dossier/${id}`);

        if (!reponse.ok) {
          throw new Error(`Failed to fetch dossier: ${reponse.status}`);
        }

        const donnees = await reponse.json();

        return plainToInstance(DossierDetail, donnees);
      },
      staleTime: 5 * 60 * 1000, // 5 minutes
    });
  }

  protected enregistrerDossier(dossier: DossierDetail): void {
    queryClient.setQueryData(
      ["DossierManagerInterface", "dossier", dossier.id],
      () => dossier,
    );
  }

  async consulter(id: number): Promise<DossierDetail> {
    return this.recupererDossier(id);
  }

  // TODO renommer en `televerserPieceJointe`
  async televerserPieceJointe(
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

  ajouterDocument(dossier: DossierDetail, document: Document): void {
    this.enregistrerDossier(dossier.addDocument(document));
  }

  async attribuer(dossier: BaseDossier, redacteur: Redacteur): Promise<void> {
    const reponse = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/attribuer`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          redacteur_id: redacteur.id,
        }),
      },
    );

    if (reponse.ok) {
      const donnees = await reponse.json();

      this.enregistrerDossier(plainToInstance(DossierDetail, donnees));
    }
  }

  async transmettreAFIP3(dossier: BaseDossier): Promise<void> {
    const reponse = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/transmettre-a-fip3`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
      },
    );

    if (reponse.ok) {
      const donnees = await reponse.json();

      this.enregistrerDossier(plainToInstance(DossierDetail, donnees));
    }
  }

  async marquerIndemnise(
    dossier: BaseDossier,
    dateIndemnisation: Date,
  ): Promise<void> {
    const reponse = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/marquer-indemnise`,
      {
        method: "POST",
        body: JSON.stringify({
          dateIndemnisation: dateChiffre(dateIndemnisation),
        }),
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
      },
    );

    if (reponse.ok) {
      const donnees = await reponse.json();

      this.enregistrerDossier(plainToInstance(DossierDetail, donnees));
    }
  }
}
