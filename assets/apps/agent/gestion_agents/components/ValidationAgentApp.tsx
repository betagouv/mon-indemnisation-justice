import {ValidationAgentRow, DeclarationNouvelAgentRow} from "@/apps/agent/gestion_agents/components";
import {Agent, RequeteAgentValidationListe} from "@/apps/agent/gestion_agents/models";
import {observer} from "mobx-react-lite";
import React, {useState} from "react";

export const ValidationAgentApp = observer(({liste}: { liste: RequeteAgentValidationListe }) => {
    const [sauvegardeEnCours, setSauvegardeEnCours] = useState(false);

    let nouvelAgent= new Agent()

    const sauvegarder = async () => {
        setSauvegardeEnCours(true);
        await liste.sauvegarder();
        setSauvegardeEnCours(false);
    }

    return (
        <>
            <div className="fr-grid-row">


                <div className="fr-col-12">
                    <h2>Agents en attente de validation</h2>
                </div>
            </div>

            <div className="fr-grid-row fr-grid-row--right fr-my-1w fr-grid-row--middle">
                <span className="fr-text--sm fr-mx-2w fr-my-0">
                    {sauvegardeEnCours ? "Sauvegarde en cours..." :
                        "Les changements ne sont pas encore sauvegardés"
                    }
                </span>
                <button
                    className="fr-btn fr-btn--sm"
                    disabled={sauvegardeEnCours || (liste.validationsValides.length == 0)}
                    onClick={async () => sauvegarder()}
                >
                    {liste.validationsValides.length == 0 && "Valider"}
                    {liste.validationsValides.length == 1 && "Valider le compte"}
                    {liste.validationsValides.length > 1 && `Valider les ${liste.validationsValides.length} comptes`}
                </button>
            </div>

            <div className="fr-grid-row fr-my-1w">
                <div className="fr-col-12">

                    <div className="fr-table">
                        <div className="fr-table__wrapper">
                            <div className="fr-table__container">
                                <div className="fr-table__content">
                                    <table>
                                        <thead>
                                        <tr>
                                            <th className="fr-col-3">
                                                Nom & adresse courriel
                                            </th>
                                            <th className="fr-col-4">
                                                Administration
                                            </th>
                                            <th className="fr-col-4">
                                                Niveau d'accès
                                            </th>
                                            <th className="fr-col-1"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {
                                            liste.validations.map((validation) =>
                                                <ValidationAgentRow
                                                    key={validation.agent.courriel}
                                                    validation={validation}
                                                    editable={!sauvegardeEnCours}
                                                />)
                                        }
                                        <DeclarationNouvelAgentRow
                                            agent={nouvelAgent}
                                            onSaved={(agent) => {
                                                liste.ajouterAgentValidation(agent)
                                            }}
                                        />
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
});