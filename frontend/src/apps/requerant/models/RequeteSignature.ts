import DateTransform from "@common/normalisation/transformers/DateTransform.ts";

export class RequeteSignature {
  public readonly id!: string;
  public readonly urlExterne: string = "";
  @DateTransform()
  public readonly dateActivation?: Date;
  @DateTransform()
  public readonly dateSignature?: Date;
  @DateTransform()
  public readonly dateAnnulation?: Date;
  @DateTransform()
  public readonly dateExpiration?: Date;

  public get estActive(): boolean {
    return !!this.dateActivation;
  }

  public get estASigner(): boolean {
    return (
      this.estActive && !this.estSignee && !this.estExpiree && !this.estAnnulee
    );
  }

  public get estSignee(): boolean {
    return !!this.dateSignature;
  }

  public get estAnnulee(): boolean {
    return !!this.dateAnnulation;
  }

  public get estExpiree(): boolean {
    return !!this.dateExpiration && this.dateExpiration <= new Date();
  }

  /**
   * Requête fermée (expirée ou annulée) : elle ne peut plus être signée, mais une nouvelle peut la remplacer.
   */
  public get estFermee(): boolean {
    return this.estExpiree || this.estAnnulee;
  }
}
