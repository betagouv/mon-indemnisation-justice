import {Administration, RequeteAgentValidation, RolesAgent} from "@/apps/agent/gestion_agents/models";
import {observer} from "mobx-react-lite";
import React from 'react';

export const ValidationAgentRow = observer(({validation, editable = false}: {
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
                            {validation.agent.datePremiereConnexion &&
                                <span
                                    className="fr-text--sm">première connexion le {validation.agent.datePremiereConnexion.toLocaleString('fr-FR', {
                                    weekday: 'long',
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric',
                                    hour: 'numeric',
                                    minute: 'numeric',
                                })}</span>
                            }
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
                                {Administration.catalog.values().toArray().map((administration) => <option
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
                                 className="fr-fieldset__element fr-m-0">
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
                                        {RolesAgent[role]?.libelle}
                                        <span className="fr-hint-text">{RolesAgent[role]?.description}</span>
                                    </label>
                                </div>
                            </div>))
                        }

                        <div className="fr-messages-group" id="storybook-form-messages" aria-live="polite">
                        </div>
                    </fieldset>
                }
            </td>
            <td className="fr-col-1">
                <div className="fr-grid-row fr-grid-row--right">
                    {!validation.estValide() ? (
                        <span
                            className="fr-icon-close-line fr-text-default--error"
                            aria-hidden="true"></span>
                    ) : (!validation.estInchange() &&
                        <span
                            className="fr-icon-check-line fr-text-default--success"
                            aria-hidden="true"
                        ></span>
                    )}
                </div>
            </td>
        </tr>
    )
        ;
});