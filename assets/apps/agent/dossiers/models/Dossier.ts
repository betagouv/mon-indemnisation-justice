import {
  Adresse,
  Courrier,
  Document,
  DocumentType,
  EtatDossier,
  Redacteur,
  Requerant,
  TestEligibilite,
} from "@/apps/agent/dossiers/models";
import { Expose, Transform, Type } from "class-transformer";
import { action, computed, makeObservable, observable } from "mobx";

export abstract class BaseDossier {
  public readonly id: number;
  public readonly reference: string;

  @Transform(({ value }: { value: string }) => EtatDossier.resoudre(value))
  public etat: EtatDossier = null;
  protected _dateDepot: Date | null;
  @Expose()
  @Transform(({ value }: { value: number }) => Redacteur.resoudre(value))
  public redacteur: Redacteur | null = null;

  @Expose()
  get dateDepot(): null | Date {
    return this._dateDepot;
  }

  set dateDepot(value: Date | number) {
    this._dateDepot = typeof value === "number" ? new Date(value) : value;
  }

  public estAAttribuer(): boolean {
    return this.etat.egal(EtatDossier.A_INSTRUIRE) && null === this.redacteur;
  }

  attribuer(redacteur: Redacteur): void {
    this.redacteur = redacteur;
  }

  enAttenteInstruction(): boolean {
    return this.etat.egal(EtatDossier.A_INSTRUIRE);
  }

  enInstruction(): boolean {
    return this.etat.egal(EtatDossier.EN_INSTRUCTION);
  }

  get enAttenteDecision(): boolean {
    return [EtatDossier.A_INSTRUIRE, EtatDossier.EN_INSTRUCTION].includes(
      this.etat,
    );
  }

  get enAttenteValidation(): boolean {
    return [EtatDossier.OK_A_SIGNER, EtatDossier.KO_A_SIGNER].includes(
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
  @Type(() => TestEligibilite)
  public readonly testEligibilite?: TestEligibilite = null;

  public notes?: string = null;

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
  public documents: Map<string, Document[]> = new Map();

  public corpsCourrier?: string;
  @Expose()
  @Type(() => Courrier)
  public courrier?: Courrier = null;

  constructor() {
    super();
    makeObservable(this, {
      redacteur: observable,
      attribuer: action,
      enAttenteDecision: computed,
      etat: observable,
      changerEtat: action,
      courrier: observable,
      setCourrier: action,
      //documents: observable,
      addDocument: action,
      viderDocumentParType: action,
      notes: observable,
      annoter: action,
    });
  }

  setCourrier(courrier: Courrier) {
    this.courrier = courrier;
  }

  annoter(notes: string): void {
    this.notes = notes;
  }

  public hasDocumentsType(type: DocumentType): boolean {
    return this.getDocumentsType(type).length > 0;
  }

  public getDocumentsType(type: DocumentType): Document[] {
    return this.documents.get(type.type) ?? [];
  }

  public addDocument(document: Document): void {
    if (!this.documents.has(document.type.type)) {
      this.documents.set(document.type.type, []);
    }

    this.documents.get(document.type.type).push(document);
  }

  public viderDocumentParType(type: DocumentType): void {
    this.documents.set(type.type, []);
  }
}
