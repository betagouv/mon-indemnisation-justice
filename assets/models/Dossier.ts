export class Dossier {
    // adresse: Object { id: 75, ligne1: "", ligne2: "", … }

    public id: number;

    public dateAttestationInformation : Date|null;

    public dateCreation : Date|null;

    public dateDeclaration: Date|null;

    public dateOperationPJ : Date|null;

    public identitePersonneRecherchee: string;

    public isPorteBlindee: boolean;

    public lastStatut: string;

    //liasseDocumentaire: "/api/liasse_documentaires/145"

    public nomRemiseAttestation: string|null;

    public numeroPV: string|null;

    public numeroParquet: string|null;

    public precisionRequerant: string|null;

    public prenomRemiseAttestation: string|null;

    public qualiteRequerant: string|null;

    public raccourci: string|null;

    //receveurAttestation: Object { id: 71, civilite: "M", nom: "Test", … }

    public reference: string|null;

    //requerant: Object { id: 1, email: "pierre@pierrelemee.fr", isPersonneMorale: false, … }

    //serviceEnqueteur: "/api/service_enqueteurs/44"
    
}
