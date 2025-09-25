export type TypeInstitutionSecuritePublique = "GN" | "PN" | "PP";

export class InstitutionSecuritePublique {
    public static GN = new InstitutionSecuritePublique("GN");
    public static PN = new InstitutionSecuritePublique("PN");
    public static PP = new InstitutionSecuritePublique("PP");

    private constructor(readonly type: TypeInstitutionSecuritePublique) {
    }

    public libelle(): string {
        switch (this.type) {
            case "GN":
                return "Gendarmerie nationale";
            case "PN":
                return "Police nationale";
            default:
                return "Préfecture de Police";
        }
    }

    public static entries(): Array<
        [TypeInstitutionSecuritePublique, InstitutionSecuritePublique]
    > {
        return [
            ["GN", this.GN],
            ["PN", this.PN],
            ["PP", this.PP],
        ];
    }

    public static from(
        type: TypeInstitutionSecuritePublique,
    ): InstitutionSecuritePublique {
        switch (type) {
            case "GN":
                return this.GN;
            case "PN":
                return this.PN;
            case "PP":
                return this.PP;
        }
    }
}
