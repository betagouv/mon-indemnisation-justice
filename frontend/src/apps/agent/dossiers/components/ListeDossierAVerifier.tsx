import React, {useEffect, useMemo, useState} from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import {plainToInstance} from "class-transformer";
import {DossierAVerifier} from './liste/DossierAVerifier';
import {periode} from "@/common/services/date.ts";

function DossierAVerifierLigne({
                                   dossier,
                               }: {
    dossier: DossierAVerifier;
}) {
    const consulterDossierURL = useMemo<string>(
        () => `/agent/redacteur/dossier/${dossier.id}`,
        [dossier.id],
    );

    return (
        <div className="fr-grid-row mij-dossier-liste-element">
            <div className="fr-col-3">
                <strong className="fr-text--bold fr-text--lg">
                    {dossier.reference}
                </strong>
            </div>

            <div className="fr-col-7 mij-dossier-details">
                <ul>
                    <li>{dossier.requerant}</li>
                    <li>{dossier.montantIndemnisation}</li>
                    <li>
                        accepté il y a {periode(dossier.dateAcceptation)}
                    </li>
                </ul>
            </div>

            <div className="fr-col-2">
                <ButtonsGroup
                    inlineLayoutWhen="always"
                    alignment="right"
                    buttonsIconPosition="left"
                    buttonsEquisized={false}
                    buttonsSize="small"
                    buttons={[
                        {
                            size: "small",
                            priority: "tertiary no outline",
                            iconId: "fr-icon-eye-line",
                            children: "Consulter",
                            className: "fr-mb-0",
                            linkProps: {
                                href: consulterDossierURL,
                            },
                        },
                    ]}
                />
            </div>
        </div>
    );
}

export function ListeDossierAVerifier() {
    const [dossiers, setDossiers]: [
        DossierAVerifier[],
        (dossiers: DossierAVerifier[]) => void,
    ] = useState<DossierAVerifier[]>([]);

    // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
    useEffect(() => {
        fetch("/api/agent/fip3/dossiers/liste/a-verifier")
            .then((response) => response.json())
            .then((data) =>
                setDossiers(plainToInstance(DossierAVerifier, data as any[])),
            );
    }, []);

    return (
        <>
            <h1>Dossiers en attente d'arrêté de paiement</h1>

            <p>
                Vos dossiers attribués, ci-dessous, ont reçu une déclaration d'acceptation à vérifier et attendent un
                arrêté de paiement que vous pouvez désormais initier.
            </p>

            <h4>
                {dossiers.length ? (
                    <>
                        {dossiers.length} dossier{dossiers.length > 1 ? "s" : ""}
                    </>
                ) : <>Aucun dossier</>
                }
            </h4>

            <div>
                {dossiers.map((dossier: DossierAVerifier) => (
                    <DossierAVerifierLigne key={`dossier-a-attribuer-${dossier.id}`} dossier={dossier}/>
                ))}
            </div>
        </>
    );
}
