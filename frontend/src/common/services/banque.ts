/**
 * Outils de saisie des coordonnées bancaires (RIB / IBAN).
 *
 * Les contrôles de validité ({@link estCleRibValide}, {@link estIbanValide}) ne servent qu'à **avertir** l'usager :
 * la saisie n'est pas bloquée, les coordonnées étant ensuite vérifiées par un agent.
 */

/** Longueurs des 4 champs d'un RIB français */
export const LONGUEURS_RIB = {
  codeBanque: 5,
  codeGuichet: 5,
  numeroCompte: 11,
  cleRib: 2,
} as const;

/** Retire espaces et séparateurs, et passe en majuscules */
export const normaliserIban = (iban: string): string =>
  iban.replace(/[\s.-]+/g, "").toUpperCase();

/** Retire les accents et autres signes diacritiques (é → e, ç → c, Œ conservé) */
export const retirerAccents = (texte: string): string =>
  texte.normalize("NFD").replace(/[̀-ͯ]/g, "");

/**
 * Modulo 97 d'une suite de chiffres arbitrairement longue (calcul par blocs, sans BigInt).
 */
const modulo97 = (chiffres: string): number =>
  chiffres
    .match(/\d{1,7}/g)!
    .reduce((reste, bloc) => Number(`${reste}${bloc}`) % 97, 0);

/**
 * Les lettres d'un numéro de compte sont converties en chiffres selon la table de la norme RIB
 * (A, J → 1 ; B, K, S → 2 ; … ; I, R, Z → 9).
 */
const convertirLettresRib = (valeur: string): string =>
  valeur.toUpperCase().replace(/[A-Z]/g, (lettre) => {
    const position = lettre.charCodeAt(0) - "A".charCodeAt(0);
    // Les décalages de la table (S commence à 2, et non 1) imposent ce calcul par tranche
    return String(
      position < 9
        ? position + 1
        : position < 18
          ? position - 8
          : position - 16,
    );
  });

/** Clé RIB attendue pour un code banque, un code guichet et un numéro de compte complets */
export const calculerCleRib = (
  codeBanque: string,
  codeGuichet: string,
  numeroCompte: string,
): string =>
  String(
    97 -
      modulo97(
        `${codeBanque}${codeGuichet}${convertirLettresRib(numeroCompte)}00`,
      ),
  ).padStart(2, "0");

export const estCleRibValide = ({
  codeBanque,
  codeGuichet,
  numeroCompte,
  cleRib,
}: {
  codeBanque: string;
  codeGuichet: string;
  numeroCompte: string;
  cleRib: string;
}): boolean =>
  /^\d{5}$/.test(codeBanque) &&
  /^\d{5}$/.test(codeGuichet) &&
  /^[0-9A-Z]{11}$/i.test(numeroCompte) &&
  /^\d{2}$/.test(cleRib) &&
  calculerCleRib(codeBanque, codeGuichet, numeroCompte) === cleRib;

/** Contrôle de format et de clé (ISO 13616 : modulo 97 égal à 1) d'un IBAN */
export const estIbanValide = (iban: string): boolean => {
  const valeur = normaliserIban(iban);

  if (!/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/.test(valeur)) {
    return false;
  }

  const reordonne = `${valeur.slice(4)}${valeur.slice(0, 4)}`.replace(
    /[A-Z]/g,
    (lettre) => String(lettre.charCodeAt(0) - "A".charCodeAt(0) + 10),
  );

  return modulo97(reordonne) === 1;
};

/**
 * Code banque d'un IBAN français (`FRkk BBBBB GGGGG CCCCCCCCCCC KK`), ou `undefined` pour un IBAN étranger ou
 * incomplet.
 */
export const extraireCodeBanqueIban = (iban: string): string | undefined => {
  const valeur = normaliserIban(iban);

  return /^FR\d{2}\d{5}/.test(valeur) ? valeur.slice(4, 9) : undefined;
};
