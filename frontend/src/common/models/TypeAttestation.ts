export type TypeAttestation =
  | "NOUVELLE_ATTESTATION"
  | "AVIS_INTERVENTION"
  | "ANCIENNE_ATTESTATION"
  | "COURRIER_FDO"
  | "PAS_ATTESTATION";

export const libellesTypeAttestation: [TypeAttestation, string][] = [
  ["NOUVELLE_ATTESTATION", "Nouvelle attestation"],
  ["AVIS_INTERVENTION", "Avis d'intervention"],
  ["ANCIENNE_ATTESTATION", "Ancienne attestation"],
  ["COURRIER_FDO", "Courrier émanant des FDO"],
  ["PAS_ATTESTATION", "Document autre qu'attestation"],
];
