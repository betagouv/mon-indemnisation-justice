import React, {useEffect, useMemo, useState} from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import {plainToInstance} from "class-transformer";
import {periode} from "@/common/services/date.ts";
import {DossierArreteASigner} from "@/apps/agent/dossiers/components/liste/DossierArreteASigner";
import {convertirEnEuros} from "@/common/services/devise.ts";

function DossierArreteASignerLigne({
                                       dossier,
                                   }: {
    dossier: DossierArreteASigner;
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
                    <li>{convertirEnEuros(dossier.montantIndemnisation)}</li>
                    <li>
                        arrêté édité il y a {periode(dossier.dateEdition)} par <span
                        className="fr-text--bold">{dossier.agentEdition}</span>
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

export function ListeDossierArreteASigner() {
    const [dossiers, setDossiers]: [
        DossierArreteASigner[],
        (dossiers: DossierArreteASigner[]) => void,
    ] = useState<DossierArreteASigner[]>([]);

    // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
    useEffect(() => {
        fetch("/api/agent/dossiers/liste/arrete-a-signer")
            .then((response) => response.json())
            .then((data) =>
                setDossiers(plainToInstance(DossierArreteASigner, data as any[])),
            );
    }, []);

    return (
        <>
            <h1>Dossiers en attente de signature PI</h1>

            <p>
                Vous pouvez signer la proposition d'indemnisation des dossiers ci-dessous.
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
                {dossiers.map((dossier: DossierArreteASigner) => (
                    <DossierArreteASignerLigne key={`dossier-a-attribuer-${dossier.id}`} dossier={dossier}/>
                ))}
            </div>
        </>
    );
}
