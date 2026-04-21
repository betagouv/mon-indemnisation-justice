import { Dossier } from "@/apps/requerant/models";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe.ts";
import { differentiel } from "@/common/services";
import { data } from "autoprefixer";
import {
  instanceToInstance,
  instanceToPlain,
  plainToClassFromExist,
  plainToInstance,
} from "class-transformer";
import { ServiceIdentifier } from "inversify"; // Source - https://stackoverflow.com/a/61132308

// Source - https://stackoverflow.com/a/61132308
// Posted by Terry, modified by community. See post 'Timeline' for change history
// Retrieved 2026-03-09, License - CC BY-SA 4.0
export type DeepPartial<T> = T extends object
  ? {
      [P in keyof T]?: DeepPartial<T[P]>;
    }
  : T;

export type NouvellePieceJointe = {
  fichier: File;
  type: TypePieceJointe;
  contexte?: any;
};

export interface DossierManagerInterface {
  //aDossier(reference: string): Promise<boolean>;
  getDossier(id: number): Promise<Dossier | undefined>;

  modifier(id: number, modifications: DeepPartial<Dossier>): void;

  enregistrer(id: number): Promise<Dossier>;

  ajouterPiecesJointes(
    id: number,
    piecesJointes: NouvellePieceJointe[],
  ): Promise<void>;

  mesDemandes(): Promise<DossierApercu[]>;

  soumettre(id: number): Promise<Dossier>;

  accepter(id: number, declarationAcceptationSignee: File): Promise<Dossier>;
}

export namespace DossierManagerInterface {
  export const $: ServiceIdentifier<DossierManagerInterface> = Symbol(
    "DossierManagerInterface",
  );
}

type EtatSauvegardeDossier = {
  // Le dossier tel qu'il est en temps réel
  original: Dossier;
  modifie: Dossier;
};

/**
 * Synchronise les dossiers du requérant avec l'API
 */
export class ApiDossierManager implements DossierManagerInterface {
  protected mesDossiers?: DossierApercu[];
  protected dossiers: Map<number, EtatSauvegardeDossier | undefined>;
  constructor() {
    this.dossiers = new Map();
  }

  async getDossier(id: number): Promise<Dossier | undefined> {
    if (!this.dossiers.has(id)) {
      const reponse = await fetch(`/api/requerant/dossier/bris-de-porte/${id}`);

      if (reponse.ok) {
        const data = await reponse.json();
        const dossier = plainToInstance(Dossier, data);

        this.dossiers.set(id, {
          original: dossier,
          // On clone l'objet source pour ne pas également modifier le dossier original
          modifie: instanceToInstance(dossier),
        });
      }
    }

    return this.dossiers.get(id)?.modifie;
  }

  modifier(id: number, modifications: DeepPartial<Dossier>): void {
    if (!this.dossiers.has(id)) {
      throw new Error(`Aucun dossier de référence ${id}`);
    }

    let { original, modifie } = this.dossiers.get(id) as EtatSauvegardeDossier;

    this.dossiers.set(id, {
      original,
      modifie: plainToClassFromExist(modifie, modifications),
    });
  }

  async ajouterPiecesJointes(
    id: number,
    piecesJointes: NouvellePieceJointe[],
  ): Promise<void> {
    const payload = new FormData();

    piecesJointes.forEach((fichier) => {
      payload.append(
        "piecesJointes[]",
        fichier.fichier,
        // TODO nommer le fichier selon le type et la date si blob
        fichier.fichier.name ?? "",
      );
    });

    payload.append(
      "donnees",
      JSON.stringify(
        piecesJointes.map((p) => ({
          type: p.type.type,
          contexte: p.contexte,
        })),
      ),
    );

    const reponse = await fetch(
      `/api/requerant/dossier/bris-de-porte/${id}/televerser-pieces-jointes`,
      {
        method: "POST",
        body: payload,
      },
    );

    const data = await reponse.json();
    const dossier = plainToInstance(Dossier, data);

    this.dossiers.set(id, {
      original: dossier,
      modifie: instanceToInstance(dossier),
    });
  }

  async enregistrer(id: number): Promise<Dossier> {
    let { original, modifie } = this.dossiers.get(id) as EtatSauvegardeDossier;

    const modificationsEnAttente = differentiel(
      instanceToPlain(original),
      instanceToPlain(modifie),
    );

    // On ne déclenche l'enregistrement que si des modifications sont présentes
    if (modificationsEnAttente) {
      const reponse = await fetch(
        // Vers la route `api_requerant_dossier_bris_porte_amender`
        `/api/requerant/dossier/bris-de-porte/${id}/amender`,
        {
          method: "PATCH",
          headers: {
            Accept: "application/json",
          },
          body: JSON.stringify(modificationsEnAttente),
        },
      );
      const data = await reponse.json();
      const dossier = plainToInstance(Dossier, data);

      this.dossiers.set(id, {
        original: dossier,
        modifie: instanceToInstance(dossier),
      });
    }

    return this.dossiers.get(id)?.modifie as Dossier;
  }

  async mesDemandes(): Promise<DossierApercu[]> {
    if (!this.mesDossiers) {
      const reponse = await fetch("/api/requerant/mes-demandes");

      const data = await reponse.json();

      this.mesDossiers = plainToInstance(DossierApercu, data as any[]);
    }

    return this.mesDossiers;
  }

  async soumettre(id: number): Promise<Dossier> {
    const reponse = await fetch(
      `/api/requerant/dossier/bris-de-porte/${id}/publier`,
      {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!reponse.ok) {
      const data = await reponse.json();
      throw new Error(data.erreurs);
    } else {
      // On remplace le dossier brouillon par celui déposé
      this.dossiers.delete(id);
      const dossierDepose = plainToInstance(Dossier, data);
      this.dossiers.set(dossierDepose.id, {
        original: dossierDepose,
        modifie: instanceToInstance(dossierDepose),
      });
      // On vide la liste des dossiers pour forcer le rafraîchissement.
      this.mesDossiers = undefined;

      return dossierDepose;
    }
  }

  async accepter(
    id: number,
    declarationAcceptationSignee: File,
  ): Promise<Dossier> {
    // TODO envoyer à la nouvelle route API
    const reponse = await fetch(
      // Route 'api_requerant_dossier_bris_porte_accepter_indemnistaion'
      `/api/requerant/dossier/bris-de-porte/${id}/accepter-indemnisation`,
      {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
        body: (() => {
          const data = new FormData();
          data.append(
            "declarationAcceptationSignee",
            declarationAcceptationSignee,
          );

          return data;
        })(),
      },
    );

    if (!reponse.ok) {
      const data = await reponse.json();
      throw new Error(data.erreurs);
    } else {
      // On remplace le dossier brouillon par celui déposé
      this.dossiers.delete(id);
      const dossierDepose = plainToInstance(Dossier, data);
      this.dossiers.set(dossierDepose.id, {
        original: dossierDepose,
        modifie: instanceToInstance(dossierDepose),
      });
      // On vide la liste des dossiers pour forcer le rafraîchissement.
      this.mesDossiers = undefined;

      return dossierDepose;
    }
  }
}
