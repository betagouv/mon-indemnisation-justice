export type TypeAttestation = "NOUVELLE_ATTESTATION" | "ANCIENNE_ATTESTATION" | "COURRIER_FDO" | "PAS_ATTESTATION";

export const libellesTypeAttestation: [TypeAttestation, string][] = [
    ["NOUVELLE_ATTESTATION", "Nouvelle attestation"],
    ["ANCIENNE_ATTESTATION", "Anciennne attestation"],
    ["COURRIER_FDO", "Courrier émanant des FDO"],
    ["PAS_ATTESTATION", "Document autre qu'attestation"],
]