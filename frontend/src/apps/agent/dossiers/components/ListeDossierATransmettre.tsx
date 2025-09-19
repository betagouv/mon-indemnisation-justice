import React, {useEffect, useMemo, useState} from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import {plainToInstance} from "class-transformer";
import {DossierATransmettre} from "./liste/DossierATransmettre.ts";
import {dateSimple} from "@/common/services/date.ts";

const formateurMontantEuro = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
    trailingZeroDisplay: "auto",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

function DossierATransmettreLigne({
                                      dossier,
                                  }: {
    dossier: DossierATransmettre;
}) {
    const telechargerDocumentsURL = useMemo<string>(
        () => `/agent/redacteur/${dossier.id}/documents-a-transmettre`,
        [dossier.id],
    );
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
                    <li>{formateurMontantEuro.format(dossier.montantIndemnisation)}</li>
                    <li>
                        validé le {dateSimple(dossier.dateValidation)}{" "}
                        par {dossier.agentValidateur}
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
                            iconId: "fr-icon-download-line",
                            children: "Télécharger",
                            className: "fr-mb-0",
                            linkProps: {
                                href: telechargerDocumentsURL,
                            },
                        },
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

export function ListeDossierATransmettre() {
    const [dossiers, setDossiers]: [
        DossierATransmettre[],
        (dossiers: DossierATransmettre[]) => void,
    ] = useState<DossierATransmettre[]>([]);

    useEffect(() => {
        fetch("/api/agent/fip6/dossiers/liste/a-transmettre")
            .then((response) => response.json())
            .then((data) =>
                setDossiers(plainToInstance(DossierATransmettre, data as any[])),
            );
    }, []);

    return (
        <>
            <h1>Dossiers à transmettre au bureau du budget</h1>

            <p>
                Dossiers dont l'indemnisation a été acceptée par le requérant et dont
                l'arrêté de paiement est signé.
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
                {dossiers.map((dossier: DossierATransmettre) => (
                    <DossierATransmettreLigne key={dossier.id} dossier={dossier}/>
                ))}
            </div>
        </>
    );
}
