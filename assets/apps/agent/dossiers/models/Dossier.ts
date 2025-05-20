import { EtatDossier } from "@/apps/agent/dossiers/models/EtatDossier";
import {
  Adresse,
  Courrier,
  Document,
  DocumentType,
  EtatDossierType,
  Redacteur,
  Requerant,
  TestEligibilite,
} from ".";
import { Expose, Transform, Type } from "class-transformer";
import { action, computed, makeObservable, observable } from "mobx";

export abstract class BaseDossier {
  public readonly id: number;
  public readonly reference: string;

  @Type(() => EtatDossier)
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
    return (
      this.etat.etat.egal(EtatDossierType.A_INSTRUIRE) &&
      null === this.redacteur
    );
  }

  attribuer(redacteur: Redacteur): void {
    this.redacteur = redacteur;
  }

  enAttenteInstruction(): boolean {
    return this.etat.etat.egal(EtatDossierType.A_INSTRUIRE);
  }

  enInstruction(): boolean {
    return this.etat.etat.egal(EtatDossierType.EN_INSTRUCTION);
  }

  estCloturable(): boolean {
    return [
      EtatDossierType.A_INSTRUIRE,
      EtatDossierType.EN_INSTRUCTION,
      EtatDossierType.OK_A_SIGNER,
      EtatDossierType.KO_A_SIGNER,
    ].includes(this.etat.etat);
  }

  get enAttenteDecision(): boolean {
    return [
      EtatDossierType.A_INSTRUIRE,
      EtatDossierType.EN_INSTRUCTION,
    ].includes(this.etat.etat);
  }

  get enAttenteValidation(): boolean {
    return [EtatDossierType.OK_A_SIGNER, EtatDossierType.KO_A_SIGNER].includes(
      this.etat.etat,
    );
  }

  get estAVerifier(): boolean {
    return [EtatDossierType.OK_A_VERIFIER].includes(this.etat.etat);
  }

  get enAttentePaiement(): boolean {
    return [
      EtatDossierType.OK_VERIFIE,
      EtatDossierType.OK_A_INDEMNISER,
      EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
    ].includes(this.etat.etat);
  }

  changerEtat(etat: EtatDossier): void {
    this.etat = etat;
  }

  public estDecide(): boolean {
    return this.etat.etat.estDecide();
  }

  public estAccepte(): boolean {
    return this.etat.etat.estAccepte();
  }

  public estAccepteRequerant(): boolean {
    return this.etat.etat.estAccepteRequerant();
  }

  public estIndemnise(): boolean {
    return this.etat.etat.estIndemnise();
  }

  public estRejete(): boolean {
    return this.etat.etat.estRejete();
  }
}

export class DossierApercu extends BaseDossier {
  public readonly requerant: string; // Prénom NOM
  public readonly adresse: string;
  public readonly estEligible: boolean;
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
  public documents: Map<string, Document[]> = new Map(
    Document.types.map((type: DocumentType) => [type.type, []]),
  );

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
      documents: observable,
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
