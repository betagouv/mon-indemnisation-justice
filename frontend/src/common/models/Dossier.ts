import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import { Expose, plainToInstance, Transform, Type } from "class-transformer";
import { groupBy } from "lodash";
import { action, computed, makeObservable, observable } from "mobx";
import {
  Adresse,
  Document,
  DocumentType,
  EtatDossier,
  EtatDossierType,
  Redacteur,
  Requerant,
  TestEligibilite,
  TypeInstitutionSecuritePublique,
} from ".";

export type TypeAttestation =
  | "NOUVELLE_ATTESTATION"
  | "ANCIENNE_ATTESTATION"
  | "COURRIER_FDO"
  | "PAS_ATTESTATION";

export abstract class BaseDossier {
  public readonly id: number;
  public readonly reference: string;
  public montantIndemnisation?: number;

  @Type(() => EtatDossier)
  @Expose()
  public etat: EtatDossier;
  @DateTransform()
  @Type(() => Date)
  public dateDepot?: Date;
  @Expose()
  @Transform(({ value }: { value: number }) => Redacteur.resoudre(value))
  public redacteur: Redacteur | null = null;
  public typeAttestation?: TypeAttestation;
  public qualiteRequerant?: string;
  public readonly estEligible: boolean;

  public estAAttribuer(): boolean {
    return this.etat.etat.egal(EtatDossierType.A_ATTRIBUER);
  }

  estDepose(): boolean {
    return !!this.dateDepot;
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
      EtatDossierType.A_ATTRIBUER,
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

  get estAApprouver(): boolean {
    return [EtatDossierType.OK_A_APPROUVER].includes(this.etat.etat);
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

  public estEnvoye(): boolean {
    return this.etat.etat.estEnvoye();
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
  public readonly issuDeclarationFDO: boolean;
  public readonly requerant: string; // Prénom NOM
  public readonly adresse: string;
}

export class DossierDetail extends BaseDossier {
  @Expose()
  @Type(() => Requerant)
  public readonly requerant: Requerant;

  @Expose()
  @Type(() => TestEligibilite)
  public readonly testEligibilite?: TestEligibilite;

  @Expose()
  @Type(() => DeclarationFDOBrisPorte)
  public readonly declarationFDO?: DeclarationFDOBrisPorte;

  public descriptionRequerant?: string;

  public notes?: string;

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

  @Transform(({ value }: { value: any[] }) => {
    return new Map(
      Object.entries(
        groupBy(
          value.map((d) => plainToInstance(Document, d)),
          (item) => {
            return item.type.type;
          },
        ),
      ),
    );
  })
  public documents: Map<string, Document[]> = new Map(
    Document.types.map((type: DocumentType) => [type.type, []]),
  );

  public typeInstitutionSecuritePublique?: TypeInstitutionSecuritePublique;

  constructor() {
    super();
    makeObservable(this, {
      redacteur: observable,
      attribuer: action,
      enAttenteDecision: computed,
      etat: observable,
      changerEtat: action,
      setMontantIndemnisation: action,
      documents: observable,
      addDocument: action,
      removeDocument: action,
      viderDocumentParType: action,
      notes: observable,
      annoter: action,
    });
  }

  annoter(notes: string): void {
    this.notes = notes;
  }

  setMontantIndemnisation(montantIndemnisation: number): this {
    this.montantIndemnisation = montantIndemnisation;

    return this;
  }

  public hasDocumentsType(type: DocumentType): boolean {
    return this.getDocumentsType(type).length > 0;
  }

  public getDocumentType(type: DocumentType): Document | null {
    return this.documents.get(type.type)?.at(0) || null;
  }

  public getDocumentsType(type: DocumentType): Document[] {
    return this.documents.get(type.type) ?? [];
  }

  public addDocument(document: Document): void {
    if (document.type) {
      if (!this.documents.has(document.type.type) || document.type.estUnique) {
        this.documents.set(document.type.type, []);
      }

      this.documents.get(document.type.type)?.push(document);
    }
  }

  public removeDocument(document: Document): void {
    this.documents.set(
      document.type.type,
      this.documents
        .get(document.type.type)
        ?.filter((d) => d.id != document.id) || [],
    );
  }

  public viderDocumentParType(type: DocumentType): void {
    this.documents.set(type.type, []);
  }

  get piecesJointes(): Document[] {
    return this.documents
      .entries()
      .filter((e) =>
        Document.types
          .filter((t) => t.estPieceJointe())
          .map((t) => t.type)
          .includes(e[0]),
      )
      .reduce(
        (previousValue, currentValue, currentIndex) =>
          previousValue.concat(...currentValue[1]),
        [] as Document[],
      );
  }

  public getCourrierDecision(): Document | null {
    return this.getDocumentType(DocumentType.TYPE_COURRIER_MINISTERE);
  }

  public getDeclarationAcceptation(): Document | null {
    return this.getDocumentType(DocumentType.TYPE_COURRIER_REQUERANT);
  }

  public getArretePaiement(): Document | null {
    return this.getDocumentType(DocumentType.TYPE_ARRETE_PAIEMENT);
  }
}
