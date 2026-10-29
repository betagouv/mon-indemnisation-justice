import DateTransform from "@common/normalisation/transformers/DateTransform.ts";

const EtatDeclarationTypes = [
  "BROUILLON",
  "ENVOYEE",
  "DEMANDE_INITIEE",
  "DEMANDE_DEPOSEE",
  "DEMANDE_EN_INSTRUCTION",
  "DEMANDE_ACCEPTEE",
  "DEMANDE_REJETEE",
] as const;
type EtatDeclarationType = (typeof EtatDeclarationTypes)[number];

const libellesEtatDeclaration: Record<EtatDeclarationType, string> = {
  BROUILLON: "Brouillon",
  ENVOYEE: "Envoyée",
  DEMANDE_INITIEE: "Demande initiée",
  DEMANDE_DEPOSEE: "Demande déposée",
  DEMANDE_EN_INSTRUCTION: "Demande en instruction",
  DEMANDE_ACCEPTEE: "Demande acceptée",
  DEMANDE_REJETEE: "Demande rejetée",
};

export class EtatDeclaration {
  etat: EtatDeclarationType;
  @DateTransform()
  date: Date;

  get libelle(): string {
    return libellesEtatDeclaration[this.etat];
  }

  /**
   * Identifiant de l'état utilisable en CSS (ex : `DEMANDE_EN_INSTRUCTION` => `demande-en-instruction`)
   */
  get slug(): string {
    return this.etat.toLowerCase().replaceAll("_", "-");
  }

  estTerminal(): boolean {
    return ["DEMANDE_ACCEPTEE", "DEMANDE_ACCEPTEE"].includes(this.etat);
  }

  estDeclarationEditable(): boolean {
    return !this.estTerminal();
  }
}
