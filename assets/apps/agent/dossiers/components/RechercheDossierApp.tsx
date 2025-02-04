import {EtatDossier, RechercheDossier, Redacteur} from "@/apps/agent/dossiers/models";
import {observer} from "mobx-react-lite";
import React from 'react';

export const RechercheDossierApp = observer(({recherche}: { recherche: RechercheDossier }) => {
    return (
        <div className="fr-container fr-container--fluid fr-my-3w">
            <div className="fr-grid-row">

                <div className="fr-col-12">
                    <h1>Les dossiers</h1>

                    <section className="fr-accordion">
                        <h3 className="fr-accordion__title">
                            <button
                                className="fr-accordion__btn"
                                aria-expanded="false"
                                aria-controls="accordeon-recherche-filtres"
                            >
                                Filtres
                            </button>
                        </h3>
                        <div className="fr-collapse" id="accordeon-recherche-filtres">
                            <div className="fr-grid-row fr-grid-row--gutters">
                                <div className="fr-col-4">
                                    <div className="fr-input-group" id="input-group-168">
                                        <label className="fr-label" htmlFor="input-47">Filtre <span
                                            className="fr-hint-text">Nom, prénom du requérant, adresse, etc...</span></label>
                                        <input
                                            className="fr-input"
                                            aria-describedby="input-47-messages"
                                            name="input1"
                                            id="input-47"
                                            type="search"
                                        />
                                    </div>

                                </div>

                                <div className="fr-col-4">

                                    <div className="fr-select-group">
                                        <label
                                            className="fr-label"
                                            htmlFor="storybook-select-34">
                                            Statut du dossier
                                            <span className="fr-hint-text">&zwnj;</span>
                                        </label>
                                        <select
                                            className="fr-select"
                                            id="storybook-select-34"
                                            name="storybook-select-34"
                                            defaultValue={null}
                                        >
                                            <option value="" disabled hidden>Sélectionnez une option</option>
                                            {EtatDossier.catalog.map( (etat) =>
                                                <option value={etat.id} key={etat.id}>
                                                    {etat.libelle}
                                                </option>
                                            )}
                                        </select>
                                    </div>

                                </div>

                                <div className="fr-col-4">

                                    <fieldset
                                        className="fr-fieldset"
                                        id="storybook-form"
                                        aria-labelledby="storybook-form-legend storybook-form-messages">
                                        <legend
                                            className="fr-fieldset__legend--regular fr-fieldset__legend"
                                            id="storybook-form-legend">
                                            Rédacteur attribué
                                            <span className="fr-hint-text">&zwnj;</span>
                                        </legend>
                                        <div className="fr-fieldset__element">
                                            <div className="fr-checkbox-group">
                                                <input
                                                    id="recherche-filtres-attributaire-non-attribue"
                                                    onChange={(e) => recherche.setAttributaire(null, e.target.checked)}
                                                    checked={recherche.estActif(null)}
                                                    type="checkbox"
                                                />
                                                <label
                                                    className="fr-label"
                                                    htmlFor="recherche-filtres-attributaire-non-attribue">
                                                    Aucun <i className="fr-mx-1v">(non attribué)</i>
                                                </label>
                                            </div>
                                        </div>

                                        {Redacteur.catalog.values().toArray().map((redacteur) =>
                                            <div className="fr-fieldset__element" key={redacteur.id}>
                                                <div className="fr-checkbox-group">
                                                    <input
                                                        id={`recherche-filtres-attributaire-${redacteur.id}`}
                                                        type="checkbox"
                                                        onChange={(e) => recherche.setAttributaire(redacteur, e.target.checked)}
                                                        checked={recherche.estActif(redacteur)}
                                                    />
                                                    <label
                                                        className="fr-label"
                                                        htmlFor={`recherche-filtres-attributaire-${redacteur.id}`}
                                                    >
                                                        {redacteur.nom}
                                                    </label>
                                                </div>
                                            </div>
                                        )}
                                    </fieldset>

                                </div>
                            </div>

                        </div>
                    </section>


                    <div className="fr-grid-row">

                        <div className="fr-col-12">
                            <div className="fr-table" id="prec-tableau-dossiers-nouveauxs_container">
                                <div className="fr-table__wrapper">
                                    <div className="fr-table__container">
                                        <div className="fr-table__content">
                                            <table id="prec-tableau-dossiers-nouveaux">
                                                <thead>
                                                <tr>
                                                    <th scope="col" className="fr-col-2">
                                                        Référence / état
                                                    </th>
                                                    <th scope="col" className="fr-col-4">
                                                        Idéntité et adresse du requérant
                                                    </th>
                                                    <th scope="col" className="fr-col-3">
                                                        Déposé le
                                                    </th>
                                                    <th scope="col" className="fr-col-2">
                                                        Attribué à
                                                    </th>
                                                    <th scope="col" className="fr-col-1">
                                                        <span className="fr-hidden">Action</span>
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                <tr>
                                                    <td className="fr-col-2">
                                                        <p className="fr-badge fr-badge--info fr-badge--no-icon fr-mb-1v">À
                                                            instruire</p>
                                                        <br/>
                                                        {/*{{dossier.reference}}*/}
                                                    </td>
                                                    <td className="fr-col-4">
                                                    <span className="fr-text--lg fr-text--bold">
                                                        {/*{{
                                                        dossier
                                                        .requerant.personnePhysique.prenom1
                                                    }} {{dossier.requerant.personnePhysique.nom}}*/}
                                                    </span>
                                                        <br/>
                                                        {/*{{dossier.adresse.libelle}}*/}
                                                    </td>
                                                    <td className="fr-col-3">
                                                        {/*{{
                                                        dossier
                                                        .dateDeclaration | format_datetime(locale = 'fr', pattern = "d MMMM YYYY")
                                                    }}*/}
                                                    </td>
                                                    <td className="fr-col-2">
                                                        <i>non attribué</i>
                                                    </td>
                                                    <td className="fr-col-1">
                                                        <div className="fr-btns-group fr-btns-group--right">
                                                            <a className="fr-btn fr-btn--tertiary fr-icon-eye-line fr-btn--sm">

                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>

                                                {/*
                                            <tr>
                                                <td colSpan="5">
                                                    <p className="fr-p-2w">Aucun dossier correspondant</p>
                                                </td>
                                            </tr>
                                            */}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
});