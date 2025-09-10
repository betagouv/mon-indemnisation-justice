import {RapportAuLogement} from "./RapportAuLogement";
import {Transform} from "class-transformer";

export class TestEligibilite {
    public estVise: boolean;
    public estHebergeant: boolean;
    @Transform(
        ({value}: { value: string }) => {
            console.log(value)
            return RapportAuLogement[value]
        }
    )
    public rapportAuLogement: RapportAuLogement;
    public aContacteAssurance: boolean;
    public aContacteBailleur?: boolean;
}
