export class EtatDossier {
  public readonly id: string;
  public readonly slug: string;
  public readonly libelle: string;
  public readonly estDisponibleRecherche: boolean;

  public static A_FINALISER = new EtatDossier("A_FINALISER", "À finaliser");
  public static A_INSTRUIRE = new EtatDossier("A_INSTRUIRE", "À instruire");

  public static OK_A_SIGNER = new EtatDossier(
    "OK_A_SIGNER",
    "Accepté - à signer",
  );

  public static OK_A_APPROUVER = new EtatDossier(
    "OK_A_APPROUVER",
    "Accepté - à approuver requérant",
  );

  public static OK_A_INDEMNISER = new EtatDossier(
    "OK_A_INDEMNISER",
    "Accepté - à indemniser",
  );

  public static OK_INDEMNISE = new EtatDossier("OK_INDEMNISE", "Indemnisé");

  public static KO_A_SIGNER = new EtatDossier(
    "KO_A_SIGNER",
    "Rejeté - à signer",
  );

  public static KO_REJETE = new EtatDossier("KO_REJETE", "Rejeté");

  protected constructor(
    id: string,
    libelle: string,
    estDisponibleRecherche: boolean = true,
  ) {
    this.id = id;
    this.slug = id.toLowerCase().replaceAll("_", "-");
    this.libelle = libelle;
    this.estDisponibleRecherche = estDisponibleRecherche;
  }

  protected static _catalog: EtatDossier[] = [
    EtatDossier.A_FINALISER,
    EtatDossier.A_INSTRUIRE,
    EtatDossier.OK_A_SIGNER,
    EtatDossier.OK_A_APPROUVER,
    EtatDossier.OK_A_INDEMNISER,
    EtatDossier.OK_INDEMNISE,
    EtatDossier.KO_A_SIGNER,
    EtatDossier.KO_REJETE,
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

  public estRejete(): boolean {
    return this.id.startsWith("KO");
  }

  public static get liste(): EtatDossier[] {
    return EtatDossier._catalog;
  }

  public static resoudreParSlug(slug: string): null | EtatDossier {
    return this._catalog.values().find((e) => e.slug == slug) ?? null;
  }

  public static resoudre(id: string): null | EtatDossier {
    return this._catalog.values().find((e) => e.id == id) ?? null;
  }
}
