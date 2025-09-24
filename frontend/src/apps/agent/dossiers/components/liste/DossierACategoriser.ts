import "reflect-metadata";
import {Transform, Type} from "class-transformer";
import {Adresse, Document} from "@/common/models";

export class DossierACategoriser {
    readonly id: number;
    readonly reference: string;
    readonly requerant: string;
    readonly adresse: string;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly dateOperation: Date;
    @Transform(({value}: { value: string }) => new Date(value))
    readonly datePublication: Date;
    @Type(() => Document)
    attestations: Document[]
}
