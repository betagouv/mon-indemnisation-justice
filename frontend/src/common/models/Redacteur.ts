export class Redacteur {
  public id: number;
  public nom: string;

  public equals(other?: Redacteur): boolean {
    return this.id === other?.id;
  }
}
