import "reflect-metadata";
import {Transform} from "class-transformer";

export class DossierAInstruire {
    readonly id: number;
    readonly reference: string;
    readonly requerant: string;
    readonly adresse: string;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly dateOperation: Date;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly datePublication: Date;
    readonly estEligible: boolean;
}
