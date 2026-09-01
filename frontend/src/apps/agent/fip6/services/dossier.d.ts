import {Document, DossierDetail} from "@common/models";

// Compteurs et tableaus de bords des dossiers
export type ListeDossier =
    | "a-categoriser"
    | "a-attribuer"
    | "a-verifier"
    | "a-instruire"
    | "en-instruction"
    | "rejet-a-signer"
    | "proposition-a-signer"
    | "acceptation-a-verifier"
    | "arrete-a-signer"
    | "a-transmettre"
    | "en-attente-indemnisation";

export type CompteurDossiers = Record<ListeDossier, number>;

// Vérification du dossier
type BaseVerification = {
    estRecevable?: boolean;
    commentaire?: string;
};


export type VerificationPieceJointe = {
    pieceJointe: Document;
    verification: BaseVerification;
};

export type VerificationDossier = {
    dossier: DossierDetail;
    verification: BaseVerification;
    piecesJointes: VerificationPieceJointe[];
};