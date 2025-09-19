import "reflect-metadata";
import {Transform} from "class-transformer";

export class DossierAVerifier {
    readonly id: number;
    readonly reference: string;
    readonly requerant: string;
    readonly montantIndemnisation: number;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly dateAcceptation: Date;
}
