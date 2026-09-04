import {Agent, BaseDossier, DossierDetail} from "@common/models";
import {RechercheReponse, RechercheRequete} from "@fip6/composants/pages/RechercherDossierPage.tsx";

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