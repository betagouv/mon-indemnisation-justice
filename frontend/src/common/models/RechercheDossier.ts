import { EtatDossierType, Redacteur } from "@/common/models";
import { action, computed, makeObservable, observable } from "mobx";
import { parseInt } from "lodash";

/**
 * Représente les critères de filtre sur la recherche de dossier en cours.
 *
 */
export class RechercheDossier {
  protected _etatsDossier: Map<EtatDossierType, boolean>;
  protected _attributaires: Set<Redacteur | null>;
  public motsClefs: string;
  protected _page: number = 1;

  constructor(
    attributaires: Array<Redacteur | null> = [],
    etatsSelectionnes: EtatDossierType[],
    motsClefs: string = "",
    page?: number,
  ) {
    this._attributaires = new Set(attributaires);
    this._etatsDossier = new Map(
      EtatDossierType.liste.map((etatDossier) => [
        etatDossier,
        etatsSelectionnes.includes(etatDossier),
      ]),
    );
    this.motsClefs = motsClefs;
    this._page = page ?? 1;
    makeObservable(this, {
      _attributaires: observable,
      _etatsDossier: observable,
      _page: observable,
      motsClefs: observable,
      setMotsClefs: action,
      setAttributaire: action,
      attributaires: computed,
      etatsDossier: computed,
      estSelectionneAttributaire: observable,
      changerEtatsDossier: action,
      setPage: action,
    } as any);
  }

  estSelectionneAttributaire(attributaire: Redacteur | null): boolean {
    return this._attributaires.has(attributaire);
  }

  get attributaires(): Iterator<Redacteur | null> {
    return this._attributaires.values();
  }

  get etatsDossier(): Map<EtatDossierType, boolean> {
    return this._etatsDossier;
  }

  estEtatDossierSelectionne(etatDossier: EtatDossierType): boolean {
    return this._etatsDossier.get(etatDossier);
  }

  get page(): number {
    return this._page;
  }

  setPage(page: number) {
    this._page = page;
  }

  get state() {
    return {
      page: this._page,
      attributaires: this._attributaires.values().toArray(),
      etatsDossier: this.getEtatsDossierSelectionnes(),
      recherche: this.motsClefs,
    };
  }

  getEtatsDossierSelectionnes(): EtatDossierType[] {
    return this._etatsDossier
      .entries()
      .filter(([_, selectionne]) => selectionne)
      .map(([etat, _]) => etat)
      .toArray();
  }

  changerEtatsDossier(etats: EtatDossierType[]) {
    for (const etat of EtatDossierType.liste) {
      this._etatsDossier.set(etat, etats.includes(etat));
    }
    this._page = 1;
  }

  protected serialiserURLEtatsDossier(): string {
    return this.getEtatsDossierSelectionnes()
      .map((etat) => etat.slug)
      .join("|");
  }

  setMotsClefs(motsClefs: string) {
    this.motsClefs = motsClefs;
    this._page = 1;
  }

  public setAttributaire(
    attributaire: Redacteur | null,
    actif: boolean = true,
  ) {
    if (actif) {
      this._attributaires.add(attributaire);
    } else {
      this._attributaires.delete(attributaire);
    }
    this._page = 1;
  }

  public buildURLParameters(): URLSearchParams {
    return new URLSearchParams({
      // Attributaire (_ = non attribué)
      ...(this._attributaires.size > 0
        ? {
            a: this._attributaires
              .values()
              .toArray()
              .map((r: Redacteur | null) => r?.id ?? "_")
              .sort()
              .join("|"),
          }
        : {}),
      // États du dossier
      ...(this.getEtatsDossierSelectionnes()
        ? { e: this.serialiserURLEtatsDossier() }
        : {}),
      // Mots clefs (= filtre textuel de recherche libre)
      ...(this.motsClefs.trim().length > 0
        ? {
            r: this.motsClefs
              .split(" ")
              .filter((f) => f.trim().length > 0)
              .join("|"),
          }
        : {}),
      p: this._page.toString(),
    });
  }

  public toURL(): URL {
    const url = new URL(window.location.toString());

    url.searchParams.set("p", this._page.toString());

    if (this._attributaires.size > 0) {
      url.searchParams.set(
        "a",
        this._attributaires
          .values()
          .toArray()
          .map((r: Redacteur | null) => r?.id ?? "_")
          .sort()
          .join("|"),
      );
    } else {
      url.searchParams.delete("a");
    }

    if (this.getEtatsDossierSelectionnes().length > 0) {
      url.searchParams.set("e", this.serialiserURLEtatsDossier());
    } else {
      url.searchParams.delete("e");
    }

    if (this.motsClefs.trim().length > 0) {
      url.searchParams.set("r", this.motsClefs);
    } else {
      url.searchParams.delete("r");
    }

    return url;
  }

  public static fromURL(): RechercheDossier {
    const query = new URLSearchParams(window.location.search);

    return new RechercheDossier(
      query.has("a")
        ? query
            .get("a")
            .split("|")
            .map((a) => (a == "_" ? null : Redacteur.resoudre(parseInt(a))))
        : [],
      query.has("e")
        ? query
            .get("e")
            .split("|")
            .map((e) => EtatDossierType.resoudreParSlug(e))
            .filter((e) => !!e)
        : [
            EtatDossierType.A_ATTRIBUER,
            EtatDossierType.A_INSTRUIRE,
            EtatDossierType.EN_INSTRUCTION,
            EtatDossierType.OK_A_SIGNER,
            EtatDossierType.OK_A_APPROUVER,
            EtatDossierType.OK_A_VERIFIER,
            EtatDossierType.OK_VERIFIE,
            EtatDossierType.OK_A_INDEMNISER,
            EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
            EtatDossierType.OK_INDEMNISE,
            EtatDossierType.KO_A_SIGNER,
            EtatDossierType.KO_REJETE,
          ],
      query.has("r") ? query.get("r") : "",
      parseInt(query.get("p")) || 1,
    );
  }
}
