import React, {useEffect, useMemo, useRef, useState} from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import {plainToInstance} from "class-transformer";
import {DossierACategoriser} from "./liste/DossierACategoriser";
import {dateSimple} from "@/common/services/date.ts";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import {PieceJointe} from "@/apps/agent/dossiers/components/consultation/piecejointe";
import {Document, InstitutionSecuritePublique, TypeInstitutionSecuritePublique} from "@/common/models";
import {Select} from "@codegouvfr/react-dsfr/Select";
import {libellesTypeAttestation} from "@/common/models/TypeAttestation.ts";
import {TypeAttestation} from "@/common/models/Dossier.ts";
import {MetaDonneesAttestation} from "@/common/models/Document.ts";

function DossierACategoriserLigne({dossier, estSelectionnee, selection}: {
    dossier: DossierACategoriser;
    estSelectionnee: boolean,
    selection: (dossier: DossierACategoriser) => void
}) {
    return (
        <div className="fr-grid-row mij-dossier-liste-element">
            <div className="fr-col-1">
                <Checkbox legend="" options={[
                    {
                        label: "",
                        nativeInputProps: {
                            checked: estSelectionnee,
                            onChange: (e) => {
                                selection(dossier)
                            }
                        }
                    }
                ]}/>
            </div>
            <div className="fr-col-3">
                <strong className="fr-text--bold fr-text--lg">
                    {dossier.reference}
                </strong>
            </div>

            <div className="fr-col-6 mij-dossier-details">
                <ul>
                    <li>{dossier.requerant}</li>
                    <li>{dossier.adresse.trim().length > 0 ? dossier.adresse : <i>Adresse non renseignée</i>}</li>
                    <li>
                        survenu le {dateSimple(dossier.dateOperation)}
                    </li>
                </ul>
            </div>
        </div>
    );
}

export function FormulaireCategorisationAttestation({pieceJointe, suivant}: {
    pieceJointe: Document,
    suivant: () => void
}) {

    const [metaDonnees, setMetaDonnees] = useState<MetaDonneesAttestation>({
        typeAttestation: undefined,
        typeInstitutionSecuritePublique: undefined,
        ...pieceJointe.metaDonnees as MetaDonneesAttestation
    })


    return <div className="fr-grid-row fr-grid-row--gutters">

        <div className="fr-col-9">
            <PieceJointe pieceJointe={pieceJointe}/>
        </div>

        <div className="fr-col-3">
            <Select
                label="Type d'attestation"
                className="fr-col-12"
                nativeSelectProps={{
                    value: metaDonnees.typeInstitutionSecuritePublique || "",
                    onChange: (event) => {
                        setMetaDonnees({
                            ...metaDonnees,
                            typeAttestation: event.target.value as TypeAttestation,
                        });
                    }
                }}
            >
                <option value="">Inconnu</option>
                {libellesTypeAttestation.map(([typeAttestation, libelle]) => (
                    <option value={typeAttestation} key={typeAttestation}>
                        {libelle}
                    </option>
                ))}
            </Select>

            <Select
                label="Forces de l'ordre"
                className="fr-col-12"
                nativeSelectProps={{
                    value: metaDonnees.typeInstitutionSecuritePublique || "",
                    onChange: (event) => {
                        setMetaDonnees({
                            ...metaDonnees,
                            typeInstitutionSecuritePublique: event.target.value as TypeInstitutionSecuritePublique || null
                        });
                    }
                }}
            >
                <option value={""}>Inconnu</option>
                {InstitutionSecuritePublique.entries().map(([type, institution]) => (
                    <option value={type} key={type}>
                        {institution.libelle()}
                    </option>
                ))}
            </Select>


            <ButtonsGroup
                inlineLayoutWhen="always"
                alignment="right"
                buttonsIconPosition="left"
                buttonsEquisized={false}
                buttonsSize="medium"
                buttons={[
                    {
                        priority: "primary",
                        children: "Suivant",
                        disabled: metaDonnees.typeAttestation === undefined || metaDonnees.typeInstitutionSecuritePublique === undefined,
                        onClick: () => {
                            setMetaDonnees({
                                typeAttestation: undefined,
                                typeInstitutionSecuritePublique: undefined,
                            })
                            suivant()
                        }
                    },
                ]}
            />
        </div>
    </div>
}

export function ListeDossierACategoriser() {
    const [dossiers, setDossiers]: [
        DossierACategoriser[],
        (dossiers: DossierACategoriser[]) => void,
    ] = useState<DossierACategoriser[]>([]);

    const refModale = useRef(null);

    const [selection, setSelection] = useState<Set<DossierACategoriser>>(new Set());

    const [dossier, setDossier] = useState<DossierACategoriser | null>(null)
    const [indiceDocument, setIndiceDocument] = useState<number>(0)

    const selectionnerDossier = (dossier: DossierACategoriser) => {
        if (selection.has(dossier)) {
            setSelection(new Set([...selection].filter(d => d !== dossier)));
        } else {
            setSelection(new Set([...selection, dossier]));
        }
    }

    // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
    useEffect(() => {
        fetch("/api/agent/fip6/dossiers/liste/a-categoriser")
            .then((response) => response.json())
            .then((data) =>
                setDossiers(plainToInstance(DossierACategoriser, data as any[])),
            );
    }, []);

    return (
        <>

            <dialog className="fr-modal" aria-labelledby="modal-title" aria-modal="true" ref={refModale}>
                <div className="fr-container fr-container--fluid fr-container-md">
                    <div className="fr-grid-row fr-grid-row--center">
                        <div className="fr-col-12 fr-col-md-12 fr-col-lg-12">
                            <div className="fr-modal__body">
                                <div className="fr-modal__header">
                                    <button aria-controls="modal" title="Fermer" type="button"
                                            onClick={() => window.dsfr(refModale.current).modal.conceal()}
                                            className="fr-btn--close fr-btn">Fermer
                                    </button>
                                </div>
                                <div className="fr-modal__content">
                                    <h2 id="modal-title" className="fr-modal__title">
                                        <span className="fr-icon-arrow-right-line fr-icon--lg"
                                              aria-hidden="true"></span>
                                        Categoriser le dossier {dossier?.reference}
                                    </h2>

                                    {dossier && dossier.attestations.length > indiceDocument &&
                                        <FormulaireCategorisationAttestation
                                            pieceJointe={dossier?.attestations[indiceDocument]}
                                            suivant={() => {
                                                if (indiceDocument == (dossier.attestations.length - 1)) {
                                                    const dossiers = [...selection]
                                                    if (dossiers.indexOf(dossier) == (dossiers.length - 1)) {
                                                        window.dsfr(refModale.current).modal.conceal();
                                                        setDossier(null);
                                                        setIndiceDocument(0);
                                                    } else {
                                                        setDossier([...selection][[...selection].indexOf(dossier) + 1]);
                                                        setIndiceDocument(0);
                                                    }

                                                } else {
                                                    setIndiceDocument((indiceDocument) => indiceDocument + 1)
                                                }
                                            }}
                                        />
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </dialog>


            <h1>Dossiers à catégoriser</h1>

            <p>
                Les dossiers ci-dessous ont été récemment déposés et nécessitent d'être catégorisés en fonction des
                éléments figurant sur l'attestation.
            </p>

            <h4>
                {dossiers.length ? (
                    <>
                        {dossiers.length} dossier{dossiers.length > 1 ? "s" : ""}
                    </>
                ) : <>Aucun dossier</>
                }
            </h4>

            <div className="fr-grid-row">
                <div className="fr-col-6">
                    <ButtonsGroup
                        inlineLayoutWhen="always"
                        alignment="left"
                        buttonsEquisized={false}
                        buttonsSize="small"
                        buttons={[
                            {
                                size: "small",
                                priority: "tertiary no outline",
                                children: "Tout sélectionner",
                                onClick: () => setSelection(new Set(dossiers)),
                                disabled: selection.size === dossiers.length
                            },
                            {
                                size: "small",
                                priority: "tertiary no outline",
                                children: "Vider la sélection",
                                onClick: () => setSelection(new Set(dossiers.filter(d => !selection.has(d)))),
                                disabled: selection.size === 0
                            },
                        ]}
                    />
                </div>

                <div className="fr-col-6">
                    <ButtonsGroup
                        inlineLayoutWhen="always"
                        alignment="right"
                        buttonsIconPosition="left"
                        buttonsEquisized={false}
                        buttonsSize="medium"
                        buttons={[
                            {
                                priority: "primary",
                                iconId: "fr-icon-eye-line",
                                children: "Catégoriser",
                                disabled: selection.size === 0,
                                onClick: () => {
                                    if (refModale.current) {
                                        setDossier([...selection].at(0) ?? null)
                                        window.dsfr(refModale.current).modal.disclose();
                                    }
                                }
                            },
                        ]}
                    />
                </div>
            </div>

            <div>
                {dossiers.map((dossier: DossierACategoriser) => (
                    <DossierACategoriserLigne
                        key={`dossier-a-attribuer-${dossier.id}`}
                        dossier={dossier}
                        estSelectionnee={selection.has(dossier)}
                        selection={selectionnerDossier}
                    />
                ))}
            </div>
        </>
    );
}
