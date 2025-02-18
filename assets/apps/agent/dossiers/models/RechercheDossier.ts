import { EtatDossier } from "@/apps/agent/dossiers/models/EtatDossier";
import { action, computed, makeObservable, observable } from "mobx";
import { Redacteur } from "./Redacteur.ts";

/**
 * Représente les critères de filtre sur la recherche de dossier en cours.
 *
 */
export class RechercheDossier {
  protected _etatDossier: EtatDossier | null;
  protected _attributaires: Set<Redacteur | null>;
  public motsClefs: string;

  constructor(
    attributaires: Array<Redacteur | null> = [],
    etatDossier: EtatDossier | null,
    motsClefs: string = "",
  ) {
    this._attributaires = new Set(attributaires);
    this._etatDossier = etatDossier;
    this.motsClefs = motsClefs;
    makeObservable(this, {
      _attributaires: observable,
      _etatDossier: observable,
      motsClefs: observable,
      setMotsClefs: action,
      setAttributaire: action,
      attributaires: computed,
      etatDossier: computed,
      estSelectionneAttributaire: observable,
    } as any);
  }

  estSelectionneAttributaire(attributaire: Redacteur | null): boolean {
    return this._attributaires.has(attributaire);
  }

  get attributaires(): Iterator<Redacteur | null> {
    return this._attributaires.values();
  }

  get etatDossier(): EtatDossier | null {
    return this._etatDossier;
  }

  set etatDossier(etat: EtatDossier | null) {
    this._etatDossier = etat;
  }

  setMotsClefs(motsClefs: string) {
    this.motsClefs = motsClefs;
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
      // État du dossier
      ...(this._etatDossier ? { e: this._etatDossier.slug } : {}),
      // Mots clefs (= filtre textuel de recherche libre)
      ...(this.motsClefs.trim().length > 0
        ? {
            r: this.motsClefs
              .split(" ")
              .filter((f) => f.trim().length > 0)
              .join("|"),
          }
        : {}),
    });
  }

  public toURL(): URL {
    const url = new URL(window.location.toString());

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

    if (this._etatDossier) {
      url.searchParams.set("e", this._etatDossier.slug);
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
      query.has("e") ? EtatDossier.resoudreParSlug(query.get("e")) : null,
      query.has("r") ? query.get("r") : "",
    );
  }
}
