import {Agent, BaseDossier, DossierDetail} from "@common/models";
import {RechercheReponse, RechercheRequete} from "@fip6/composants/pages/RechercherDossierPage.tsx";
import {MotifRejetBrisPorte} from "@common/models/rejet.ts";

export type ListeDossier =
    | "a-categoriser"
    | "a-attribuer"
    | "a-instruire"
    | "en-instruction"
    | "rejet-a-signer"
    | "proposition-a-signer"
    | "a-verifier"
    | "arrete-a-signer"
    | "a-transmettre"
    | "en-attente-indemnisation";

export type CompteurDossiers = Record<ListeDossier, number>;


// Décision
export type DecisionDossier = {
    montantIndemnisation: number;
} | { motifRejet: MotifRejetBrisPorte};

export type ValidationDecisionDossier = {
    estValide: true;
    fichierSigne: File;
    montantIndemnisation?: number;
} | { estValide: false;
    fichierSigne: File;};