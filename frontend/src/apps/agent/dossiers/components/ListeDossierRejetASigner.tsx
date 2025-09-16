import React, {useEffect, useMemo, useState} from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import {plainToInstance} from "class-transformer";
import {DossierPropositionASigner} from './liste/DossierPropositionASigner';
import {periode} from "@/common/services/date.ts";
import {DossierRejetASigner} from "@/apps/agent/dossiers/components/liste/DossierRejetASigner.ts";

function DossierRejetASignerLigne({
                                      dossier,
                                  }: {
    dossier: DossierRejetASigner;
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
                    <li><u>Motif: </u>{dossier.motif}</li>
                    <li>
                        instruction finalisée il y a {periode(dossier.dateDecision)} par <span
                        className="fr-text--bold">{dossier.agentDecision}</span>
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

export function ListeDossierRejetASigner() {
    const [dossiers, setDossiers]: [
        DossierRejetASigner[],
        (dossiers: DossierRejetASigner[]) => void,
    ] = useState<DossierRejetASigner[]>([]);

    // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
    useEffect(() => {
        fetch("/api/agent/fip3/dossiers/liste/rejet-a-signer")
            .then((response) => response.json())
            .then((data) =>
                setDossiers(plainToInstance(DossierRejetASigner, data as any[])),
            );
    }, []);

    return (
        <>
            <h1>Dossiers en attente de signature du courrier de rejet</h1>

            <p>
                Vous pouvez signer le courrier de rejet des dossiers ci-dessous.
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
                {dossiers.map((dossier: DossierRejetASigner) => (
                    <DossierRejetASignerLigne key={`dossier-a-attribuer-${dossier.id}`} dossier={dossier}/>
                ))}
            </div>
        </>
    );
}
