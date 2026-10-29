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

export class EtatDeclaration {
  etat: EtatDeclarationType;
  date: Date;

  estTerminal(): boolean {
    return ["DEMANDE_ACCEPTEE", "DEMANDE_ACCEPTEE"].includes(this.etat);
  }

  estDeclarationEditable(): boolean {
    return !this.estTerminal();
  }
}
