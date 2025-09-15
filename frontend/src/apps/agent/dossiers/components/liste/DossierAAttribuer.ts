import "reflect-metadata";
import {Transform} from "class-transformer";

export class DossierAAttribuer {
    readonly id: number;
    readonly reference: string;
    readonly requerant: string;
    readonly adresse: string;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly dateOperation: Date;
}
