/**
 * @see backend/src/Entity/TypeReferenceBancaire.php
 */
export const TypesReferenceBancaire = ["RIB", "IBAN"] as const;
export type TypeReferenceBancaire = (typeof TypesReferenceBancaire)[number];

/**
 * Coordonnées bancaires saisies par le requérant : seuls les champs correspondant au `type` sont renseignés.
 *
 * @see backend/src/Entity/ReferenceBancaire.php
 */
export type ReferenceBancaire = {
  type: TypeReferenceBancaire;
  codeBanque: string;
  codeGuichet: string;
  numeroCompte: string;
  cleRib: string;
  iban: string;
  nomBanque: string;
  titulaireDuCompte: string;
};

/**
 * @see backend/src/Api/Requerant/EtablissementBancaire/ListerEtablissementsBancairesEndpoint.php
 */
export type EtablissementBancaire = {
  codeBanque: string;
  nom: string;
  /** Chemin public du logo SVG, s'il est connu */
  logo: string | null;
};
