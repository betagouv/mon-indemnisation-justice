import { Redacteur } from "./Redacteur";
import { Requerant } from "./Requerant";
import { Expose, Transform, Type } from "class-transformer";

interface EtatInterface {
  get libelle(): string;

  estASigner(): boolean;

  estDecide(): boolean;

  estAccepte(): boolean;

  estAccepteRequerant(): boolean;

  estIndemnise(): boolean;

  estRejete(): boolean;

  estCloture(): boolean;
}

export class EtatDossier implements EtatInterface {
  public readonly id: number;
  @Transform(({ value }: { value: string }) => EtatDossierType.resoudre(value))
  public readonly etat: EtatDossierType;
  @Transform(({ value }) => {
    if (!value) {
      return null;
    }
    return typeof value === "number" ? new Date(value) : value;
  })
  public readonly dateEntree: Date;
  @Transform(({ value }: { value: number }) => Redacteur.resoudre(value))
  public readonly redacteur: Redacteur | null = null;
  @Expose()
  @Type(() => Requerant)
  public readonly requerant: boolean;
  public readonly contexte: any | null = null;

  get libelle(): string {
    return this.etat.libelle;
  }

  estASigner(): boolean {
    return this.etat.estASigner();
  }

  estAccepte(): boolean {
    return this.etat.estAccepte();
  }

  estAccepteRequerant(): boolean {
    return this.etat.estAccepteRequerant();
  }

  estDecide(): boolean {
    return this.etat.estDecide();
  }

  estIndemnise(): boolean {
    return this.etat.estIndemnise();
  }

  estRejete(): boolean {
    return this.etat.estRejete();
  }

  estCloture(): boolean {
    return this.etat.estCloture();
  }
}

export class EtatDossierType implements EtatInterface {
  public readonly id: string;
  public readonly slug: string;
  public readonly libelle: string;

  public static A_FINALISER = new EtatDossierType("A_FINALISER", "À finaliser");
  public static A_ATTRIBUER = new EtatDossierType("A_ATTRIBUER", "À attribuer");
  public static A_INSTRUIRE = new EtatDossierType(
    "A_INSTRUIRE",
    "Attribué - à instruire",
  );
  public static EN_INSTRUCTION = new EtatDossierType(
    "EN_INSTRUCTION",
    "En cours d'instruction",
  );

  public static CLOTURE = new EtatDossierType("CLOTURE", "Clôturé");

  public static OK_A_SIGNER = new EtatDossierType(
    "OK_A_SIGNER",
    "Accepté - à signer",
  );

  public static OK_A_APPROUVER = new EtatDossierType(
    "OK_A_APPROUVER",
    "Accepté - en attente déclaration",
  );

  public static OK_A_VERIFIER = new EtatDossierType(
    "OK_A_VERIFIER",
    "Accepté - déclaration retournée",
  );

  public static OK_VERIFIE = new EtatDossierType(
    "OK_VERIFIE",
    "Accepté - arrêté à signer",
  );

  public static OK_A_INDEMNISER = new EtatDossierType(
    "OK_A_INDEMNISER",
    "Accepté - arrêté à transmettre à FIP3",
  );

  public static OK_EN_ATTENTE_PAIEMENT = new EtatDossierType(
    "OK_EN_ATTENTE_PAIEMENT",
    "Accepté - arrêté transmis à FIP3",
  );

  public static OK_INDEMNISE = new EtatDossierType(
    "OK_INDEMNISE",
    "Indemnisé par FIP3",
  );

  public static KO_A_SIGNER = new EtatDossierType(
    "KO_A_SIGNER",
    "Rejeté - à signer",
  );

  public static KO_REJETE = new EtatDossierType("KO_REJETE", "Rejeté - envoyé");

  protected constructor(id: string, libelle: string) {
    this.id = id;
    this.slug = id.toLowerCase().replaceAll("_", "-");
    this.libelle = libelle;
  }

  protected static _catalog: EtatDossierType[] = [
    EtatDossierType.A_FINALISER,
    EtatDossierType.A_INSTRUIRE,
    EtatDossierType.EN_INSTRUCTION,
    EtatDossierType.CLOTURE,
    EtatDossierType.OK_A_SIGNER,
    EtatDossierType.OK_A_APPROUVER,
    EtatDossierType.OK_A_VERIFIER,
    EtatDossierType.OK_VERIFIE,
    EtatDossierType.OK_A_INDEMNISER,
    EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
    EtatDossierType.OK_INDEMNISE,
    EtatDossierType.KO_A_SIGNER,
    EtatDossierType.KO_REJETE,
  ];

  public estASigner(): boolean {
    return this.id.endsWith("A_SIGNER");
  }

  public estDecide(): boolean {
    return this.estASigner();
  }

  public estAccepte(): boolean {
    return this.id.startsWith("OK");
  }

  public estAccepteRequerant(): boolean {
    return [
      EtatDossierType.OK_A_VERIFIER.id,
      EtatDossierType.OK_A_INDEMNISER.id,
      EtatDossierType.OK_INDEMNISE.id,
    ].includes(this.id);
  }

  public estIndemnise(): boolean {
    return EtatDossierType.OK_INDEMNISE == this;
  }

  public estRejete(): boolean {
    return this.id.startsWith("KO");
  }

  estCloture(): boolean {
    return EtatDossierType.CLOTURE == this;
  }

  public static get liste(): EtatDossierType[] {
    return EtatDossierType._catalog;
  }

  public static resoudreParSlug(slug: string): null | EtatDossierType {
    return this._catalog.find((e) => e.slug == slug) ?? null;
  }

  public static resoudre(id: string): null | EtatDossierType {
    return this._catalog.find((e) => e.id == id) ?? null;
  }

  public egal(etat: EtatDossierType): boolean {
    return this.id === etat.id;
  }
}
