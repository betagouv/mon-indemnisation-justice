export class EtatDossier {
  public readonly id: string;
  public readonly slug: string;
  public readonly libelle: string;
  public readonly estDisponibleRecherche: boolean;

  public static A_FINALISER = new EtatDossier("A_FINALISER", "À finaliser");
  public static A_INSTRUIRE = new EtatDossier("A_INSTRUIRE", "À instruire");
  public static EN_INSTRUCTION = new EtatDossier(
    "EN_INSTRUCTION",
    "En cours d'instruction",
  );

  public static DOUBLON_PAPIER = new EtatDossier(
    "DOUBLON_PAPIER",
    "Doublon papier",
  );

  public static OK_A_SIGNER = new EtatDossier(
    "OK_A_SIGNER",
    "Accepté - à signer",
  );

  public static OK_A_APPROUVER = new EtatDossier(
    "OK_A_APPROUVER",
    "Accepté - à approuver",
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
    EtatDossier.EN_INSTRUCTION,
    EtatDossier.DOUBLON_PAPIER,
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

  public estAccepteRequerant(): boolean {
    return [
      EtatDossier.OK_A_INDEMNISER.id,
      EtatDossier.OK_INDEMNISE.id,
    ].includes(this.id);
  }

  public estIndemnise(): boolean {
    return EtatDossier.OK_INDEMNISE == this;
  }

  public estRejete(): boolean {
    return this.id.startsWith("KO");
  }

  public static get liste(): EtatDossier[] {
    return EtatDossier._catalog;
  }

  public static resoudreParSlug(slug: string): null | EtatDossier {
    return this._catalog.find((e) => e.slug == slug) ?? null;
  }

  public static resoudre(id: string): null | EtatDossier {
    return this._catalog.find((e) => e.id == id) ?? null;
  }

  public egal(etat: EtatDossier): boolean {
    return this.id === etat.id;
  }
}
