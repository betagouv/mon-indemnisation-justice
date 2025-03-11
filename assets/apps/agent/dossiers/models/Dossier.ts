import {
  Adresse,
  Courrier,
  Document,
  DocumentType,
  EtatDossier,
  Redacteur,
  Requerant,
} from "@/apps/agent/dossiers/models";
import { Expose, Transform, Type } from "class-transformer";
import { action, computed, makeObservable, observable } from "mobx";

export abstract class BaseDossier {
  public readonly id: number;
  public readonly reference: string;

  @Transform(({ value }: { value: string }) => EtatDossier.resoudre(value))
  public etat: EtatDossier = null;
  protected _dateDepot: Date | null;
  protected _attributaire: Redacteur | null;

  @Expose()
  get dateDepot(): null | Date {
    return this._dateDepot;
  }

  set dateDepot(value: Date | number) {
    this._dateDepot = typeof value === "number" ? new Date(value) : value;
  }

  @Expose()
  get attributaire(): Redacteur | null {
    return this._attributaire;
  }

  set attributaire(value: Redacteur | number | null) {
    this._attributaire =
      typeof value == "number" ? Redacteur.resoudre(value) : value;
  }

  get enAttenteDecision(): boolean {
    return this.etat == EtatDossier.A_INSTRUIRE;
  }

  get enAttenteValidation(): boolean {
    return [EtatDossier.OK_A_VALIDER, EtatDossier.KO_A_VALIDER].includes(
      this.etat,
    );
  }

  changerEtat(etat: EtatDossier): void {
    this.etat = etat instanceof EtatDossier ? etat : EtatDossier.resoudre(etat);
  }

  public estDecide(): boolean {
    return this.etat.estDecide();
  }

  public estAccepte(): boolean {
    return this.etat.estAccepte();
  }

  public estRejete(): boolean {
    return this.etat.estRejete();
  }
}

export class DossierApercu extends BaseDossier {
  public readonly requerant: string; // Prénom NOM
  public readonly adresse: string;
}

export class DossierDetail extends BaseDossier {
  @Expose()
  @Type(() => Requerant)
  public readonly requerant: Requerant;

  @Expose()
  @Transform(({ value }: { value: number }) => Redacteur.resoudre(value))
  public redacteur: Redacteur | null = null;

  @Expose()
  @Type(() => Adresse)
  public readonly adresse: Adresse;

  @Transform(({ value }) => {
    if (!value) {
      return null;
    }
    return typeof value === "number" ? new Date(value) : value;
  })
  public dateOperation: Date | null;

  public estPorteBlindee: boolean | null;

  public montantIndemnisation: number | null = null;

  @Transform(({ value }: { value: object }) => new Map(Object.entries(value)))
  public documents: Map<string, Document[]>;

  public courrier?: Courrier;

  protected listeDocuments?: Document[];

  constructor() {
    super();
    makeObservable(this, {
      redacteur: observable,
      attribuer: action,
      enAttenteDecision: computed,
      etat: observable,
      changerEtat: action,
      setCourrier: action,
    });
  }

  attribuer(redacteur: Redacteur): void {
    this.redacteur = redacteur;
  }

  public getDocumentParIndex(index: number): Document | null {
    if (!this.listeDocuments) {
      this.listeDocuments =
        this.documents
          ?.values()
          .reduce((pre: Document[], cur: Document[]) => pre.concat(cur), []) ??
        [];
    }

    return this.listeDocuments?.at(index % this.listeDocuments.length) ?? null;
  }

  setCourrier(courrier: Courrier) {
    this.courrier = courrier;
  }

  public getDocumentIndex(document: Document): number {
    return this.listeDocuments.indexOf(document);
  }

  public getDocumentsType(type: DocumentType): Document[] {
    return this.documents.get(type.type) ?? [];
  }
}
