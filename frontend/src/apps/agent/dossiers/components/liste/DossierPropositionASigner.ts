import "reflect-metadata";
import {Transform} from "class-transformer";

export class DossierPropositionASigner {
    readonly id: number;
    readonly reference: string;
    readonly requerant: string;
    readonly montantIndemnisation: number;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly dateDecision: Date;
    readonly agentDecision: string;
}
