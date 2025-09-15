import "reflect-metadata";
import {Transform} from "class-transformer";

export class DossierRejetASigner {
    readonly id: number;
    readonly reference: string;
    readonly requerant: string;
    readonly motif: string;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly dateDecision: Date;
    readonly agentDecision: string;
}
