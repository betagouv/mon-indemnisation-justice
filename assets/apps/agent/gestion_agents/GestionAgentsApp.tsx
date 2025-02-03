import React, {useState} from 'react';
import ReactDOM from "react-dom/client";
import {disableReactDevTools} from '@/react/services/devtools.js';
import {plainToInstance} from "class-transformer";
import {observer} from "mobx-react-lite"
import {
    Agent,
    Administration,
    RequeteAgentValidation,
    RequeteAgentValidationListe
} from "@/apps/agent/gestion_agents/models/index.ts";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
    import.meta.hot.on(
        "vite:beforeUpdate",
        () => console.clear()
    );
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
    disableReactDevTools();
}

const libelleRoles = {
    ROLE_AGENT_REDACTEUR: {
        libelle: "Rédacteur",
        description: "Instruit les dossiers d'indemnisation"
    },
    ROLE_AGENT_GESTION_PERSONNEL: {
        libelle: "Gestionnaire d'agent",
        description: "Définit les permissions accordées aux agents"
    },
    ROLE_AGENT_VALIDATEUR: {
        libelle: "Validateur",
        description: "Valide les décisions"
    },
    ROLE_AGENT_ATTRIBUTEUR: {
        libelle: "Attributeur",
        description: "Attribue les dossiers aux rédacteurs"
    },
    ROLE_AGENT_BUREAU_BUDGET: {
        libelle: "Agent du bureau du budget",
        description: "Programme les versements d'indemnisation"
    },
    ROLE_AGENT_FORCES_DE_L_ORDRE: {
        libelle: "Agent des forces de l'ordre",
        description: "Déclare des opérations"
    },
}


const {administrations, agents: _agts} = JSON.parse(document.getElementById('react-arguments').textContent);

Administration.charger(administrations)

const agents: Agent[] = plainToInstance(Agent, (_agts as any[]));

const validations = new RequeteAgentValidationListe(agents)

const root = ReactDOM.createRoot(document.getElementById('react-app-agent-gestion-agents'));

/*


const ValidationAgent = function ({agent: Agent}) {
    return (
        <tr>
            <td className="fr-col-3 fr-grid-row fr-grid-row--gutters">
                <p>
                    Jean MICHON
                    <br/>
                    <span
                        className="fr-text--sm">jean.michon@interieur.gouv.fr</span>
                </p>
            </td>
            <td className="fr-col-4 fr-grid-row fr-grid-row--gutters">
                <p>
                    Rattaché à la <b>Police Nationale</b> <a
                    className="fr-link fr-icon-edit-line fr-link--icon-right"
                    style={{textDecoration: 'none'}}
                    href="#"></a>
                    <br/>
                    <span className="fr-text--sm">première connexion le 17 janvier 2025, 17h43</span>
                </p>
            </td>
            <td className="fr-col-4 fr-grid-row fr-grid-row--right">

                <div className="fr-select-group">
                    <label className="fr-label" htmlFor="select">Permissions :</label>
                    <select className="fr-select" id="select" name="select">
                        <option value="" defaultValue disabled hidden></option>
                    </select>
                </div>
            </td>
            <td className="fr-col-1">
                <div className="fr-grid-row fr-grid-row--right">
                                                        <span className="fr-icon-check-line fr-text-default--success"
                                                              aria-hidden="true"></span>
                </div>
            </td>
        </tr>
    );
}
*/

const ValidationAgentRow = observer(({validation, editable = false}: {
    validation: RequeteAgentValidation,
    editable: boolean
}) => {
    return (
        <tr key={`row-${validation.agent.id}`}>
            <td className="fr-col-3 fr-grid-row fr-grid-row--gutters">
                <p>
                    {validation.agent.nomComplet()}
                    <br/>
                    <span
                        className="fr-text--sm">{validation.agent.courriel}</span>
                </p>
            </td>
            <td className="fr-col-4 fr-grid-row fr-grid-row--gutters">
                {validation.administration ?
                    (
                        <p>
                            Rattaché {validation.administration.estLibelleFeminin ? 'à la' : 'au'}
                            <b> {validation.administration.libelle}</b>
                            {!validation.agent.administration &&
                                <a className="fr-link fr-icon-edit-line fr-link--icon-right"
                                   role="button"
                                   title="Changer"
                                   onClick={() => validation.reinitialiser()}></a>}
                            <br/>
                            <span className="fr-text--sm">première connexion le {validation.agent.datePremiereConnexion.toLocaleString('fr-FR', {
                                weekday: 'long',
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                            })}</span>
                        </p>
                    ) :
                    (
                        <div className="fr-select-group fr-select-group--error">
                            <label className="fr-label"
                                   htmlFor={`selection-administration-${validation.agent.id}`}>Rattachement
                                :</label>
                            <select
                                className="fr-select"
                                defaultValue={""}
                                disabled={!editable}
                                id={`selection-administration-${validation.agent.id}`}
                                onChange={(e) => validation.administration = Administration.resoudre(e.target.value)}
                            >
                                <option value="" disabled hidden></option>
                                {administrations.map((administration) => <option
                                    value={administration.id}
                                    key={administration.id}>{administration.libelle}</option>)}
                            </select>
                        </div>
                    )
                }

            </td>
            <td className="fr-col-4 fr-grid-row fr-grid-row--right fr-grid-row--middle">
                {validation.administration &&
                    <fieldset
                        className="fr-fieldset"
                        aria-labelledby="storybook-form-legend storybook-form-messages">
                        <legend
                            className="fr-fieldset__legend--regular fr-fieldset__legend"
                            id="storybook-form-legend"
                        > Permissions
                        </legend>

                        {validation.administration?.roles.map((role: string) => (
                            <div key={`permission-${validation.agent.id}-${role.toLowerCase()}`}
                                 className="fr-fieldset__element">
                                <div className="fr-checkbox-group">
                                    <input
                                        id={`input-permission-${validation.agent.id}-${role.toLowerCase()}`}
                                        type="checkbox"
                                        disabled={!editable}
                                        checked={validation.aRole(role)}
                                        onChange={(e) => validation.definirRole(role, e.target.checked)}
                                        aria-describedby="checkbox-53-messages"/>
                                    <label className="fr-label"
                                           htmlFor={`input-permission-${validation.agent.id}-${role.toLowerCase()}`}>
                                        {libelleRoles[role]?.libelle}
                                        <span className="fr-hint-text">{libelleRoles[role]?.description}</span>
                                    </label>
                                </div>
                            </div>))
                        }

                        <div className="fr-messages-group" id="storybook-form-messages" aria-live="polite">
                        </div>
                    </fieldset>
                }

                {/*
                <div className="fr-select-group">
                    <label className="fr-label" htmlFor={`selection-roles-${validation.agent.id}`}>Permissions
                        :</label>
                    <select className="fr-select" id={`selection-roles-${validation.agent.id}`}
                            disabled={!validation.administration}>
                        <option value="" defaultValue disabled hidden></option>
                        {validation.administration?.roles.map((role: string) => <option key={role}
                                                                                        value={role}>{libelleRoles[role]}</option>)}
                    </select>
                </div>
                */
                }
            </td>
            <td className="fr-col-1">
                <div className="fr-grid-row fr-grid-row--right">
                    {validation.estValide() ? (
                        <span
                            className="fr-icon-check-line fr-text-default--success"
                            aria-hidden="true"
                        ></span>
                    ) : (
                        <span
                            className="fr-icon-close-line fr-text-default--error"
                            aria-hidden="true"></span>
                    )
                    }
                </div>
            </td>
        </tr>
    )
        ;
});

const ValidationAgentApp = observer(({liste}: { liste: RequeteAgentValidationListe }) => {
    const [sauvegardeEnCours, setSauvegardeEnCours] = useState(false);

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
                                                    key={validation.agent.id}
                                                    validation={validation}
                                                    editable={!sauvegardeEnCours}
                                                />)
                                        }
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

root.render(
    <div className="fr-container fr-mt-2w">
        <ValidationAgentApp liste={validations}/>
    </div>
);