import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import { Transform } from "class-transformer";

/**
 * L'état d'un dossier de demande d'indemnisation, du point de vue du requérant.
 */
export const EtatDossierTypeCodes = [
  "A_COMPLETER",
  "DEPOSE",
  "EN_INSTRUCTION",
  "OK_A_ACCEPTER",
  "OK_A_INDEMNISER",
  "OK_INDEMNISE",
  "KO_REJETE",
  "CLOTURE",
] as const;

export type EtatDossierTypeCode = (typeof EtatDossierTypeCodes)[number];

export class EtatDossierType {
  constructor(
    public readonly type: EtatDossierTypeCode,
    public readonly libelle: string,
  ) {}

  get estAccepte(): boolean {
    return this.type.startsWith("OK");
  }

  get estSigne(): boolean {
    return this.estType(
      "OK_A_ACCEPTER",
      "OK_A_INDEMNISER",
      "OK_INDEMNISE",
      "KO_REJETE",
    );
  }

  get estCloture(): boolean {
    return this.type == "CLOTURE";
  }

  get estDepose(): boolean {
    return this.type !== "A_COMPLETER";
  }

  get estEditable(): boolean {
    return this.estType("A_COMPLETER", "DEPOSE", "EN_INSTRUCTION");
  }

  protected estType(...types: EtatDossierTypeCode[]): boolean {
    return types.includes(this.type);
  }
}

export const EtatDossierTypes: {
  [key in EtatDossierTypeCode]: EtatDossierType;
} = {
  A_COMPLETER: new EtatDossierType("A_COMPLETER", "Dossier à compléter"),
  DEPOSE: new EtatDossierType("DEPOSE", "Dossier déposé"),
  EN_INSTRUCTION: new EtatDossierType(
    "EN_INSTRUCTION",
    "Dossier en cours d'instruction",
  ),
  OK_A_ACCEPTER: new EtatDossierType(
    "OK_A_ACCEPTER",
    "Indemnisation à accepter",
  ),
  OK_A_INDEMNISER: new EtatDossierType(
    "OK_A_INDEMNISER",
    "En attente d'indemnisation",
  ),
  OK_INDEMNISE: new EtatDossierType("OK_INDEMNISE", "Indemnisé"),
  KO_REJETE: new EtatDossierType("KO_REJETE", "Dossier rejeté"),
  CLOTURE: new EtatDossierType("CLOTURE", "Dossier clôturé"),
};

export type EtatDossierContexteCloture = {
  motif: string;
  explication: string;
};

export class EtatDossier {
  @Transform(({ value }: { value: string }) => EtatDossierTypes[value], {
    toClassOnly: true,
  })
  @Transform(({ value }: { value: EtatDossierType }) => value?.type, {
    toPlainOnly: true,
  })
  etat: EtatDossierType;
  @DateTransform()
  date: Date;
  agent?: { id: number; nom: string };
  requerant?: { id: number; nom: string };
  contexte?: EtatDossierContexteCloture | any;
}
