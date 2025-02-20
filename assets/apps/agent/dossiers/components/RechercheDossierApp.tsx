import {
  EtatDossier,
  RechercheDossier,
  Redacteur,
} from "@/apps/agent/dossiers/models";
import { DossierApercu } from "@/apps/agent/dossiers/models/Dossier";
import { observer } from "mobx-react-lite";
import _ from "lodash";
import React from "react";

export const RechercheDossierApp = observer(
  ({
    recherche,
    dossiers = [],
  }: {
    recherche: RechercheDossier;
    dossiers: DossierApercu[];
  }) => {
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
                  Critères de recherche
                </button>
              </h3>
              <div className="fr-collapse" id="accordeon-recherche-filtres">
                <div className="fr-grid-row fr-grid-row--gutters">
                  <div className="fr-col-4">
                    <div className="fr-input-group">
                      <label
                        className="fr-label"
                        htmlFor="recherche-filtres-mots-clefs"
                      >
                        Filtre
                        <span className="fr-hint-text">
                          Nom, prénom du requérant, adresse, etc...
                        </span>
                      </label>
                      <input
                        className="fr-input"
                        id="recherche-filtres-mots-clefs"
                        placeholder="Paul, 75001 PARIS, GARNIER, ..."
                        type="search"
                        defaultValue={recherche.motsClefs}
                        onChange={_.debounce((e) => {
                          console.log(e.target.value);
                          recherche.setMotsClefs(e.target.value);
                        }, 500)}
                      />
                    </div>
                  </div>

                  <div className="fr-col-4">
                    <div className="fr-select-group">
                      <label
                        className="fr-label"
                        htmlFor="recherche-filtres-etat-dossier"
                      >
                        Statut du dossier
                        <span className="fr-hint-text">&zwnj;</span>
                      </label>
                      <select
                        className="fr-select"
                        id="recherche-filtres-etat-dossier"
                        defaultValue={null}
                        onChange={(e) =>
                          (recherche.etatDossier = EtatDossier.resoudre(
                            e.target.value,
                          ))
                        }
                      >
                        <option value="" disabled hidden>
                          Sélectionnez une option
                        </option>
                        {EtatDossier.liste.filter((etat) => etat.estDisponibleRecherche).map((etat) => (
                            <option value={etat.id} key={etat.id}>
                              {etat.libelle}
                            </option>
                          ))}
                      </select>
                    </div>
                  </div>

                  <div className="fr-col-4">
                    <fieldset
                      className="fr-fieldset"
                      id="recherche-filtres-champs-attributaire"
                      aria-labelledby="recherche-filtres-champs-attributaire-legende"
                    >
                      <legend
                        className="fr-fieldset__legend--regular fr-fieldset__legend"
                        id="recherche-filtres-champs-attributaire-legende"
                      >
                        Rédacteur attribué
                        <span className="fr-hint-text">&zwnj;</span>
                      </legend>
                      <div className="fr-fieldset__element">
                        <div className="fr-checkbox-group">
                          <input
                            id="recherche-filtres-attributaire-non-attribue"
                            onChange={(e) =>
                              recherche.setAttributaire(null, e.target.checked)
                            }
                            checked={recherche.estSelectionneAttributaire(null)}
                            type="checkbox"
                          />
                          <label
                            className="fr-label"
                            htmlFor="recherche-filtres-attributaire-non-attribue"
                          >
                            Aucun <i className="fr-mx-1v">(non attribué)</i>
                          </label>
                        </div>
                      </div>

                      {Redacteur.catalog
                        .values()
                        .toArray()
                        .map((redacteur) => (
                          <div
                            className="fr-fieldset__element"
                            key={redacteur.id}
                          >
                            <div className="fr-checkbox-group">
                              <input
                                id={`recherche-filtres-attributaire-${redacteur.id}`}
                                type="checkbox"
                                onChange={(e) =>
                                  recherche.setAttributaire(
                                    redacteur,
                                    e.target.checked,
                                  )
                                }
                                checked={recherche.estSelectionneAttributaire(
                                  redacteur,
                                )}
                              />
                              <label
                                className="fr-label"
                                htmlFor={`recherche-filtres-attributaire-${redacteur.id}`}
                              >
                                {redacteur.nom}
                              </label>
                            </div>
                          </div>
                        ))}
                    </fieldset>
                  </div>
                </div>
              </div>
            </section>

            <div className="fr-grid-row">
              <div className="fr-col-12">
                <div
                  className="fr-table"
                  id="prec-tableau-dossiers-nouveauxs_container"
                >
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
                            {dossiers.length > 0 ? (
                              dossiers.map((dossier: DossierApercu) => (
                                <tr key={dossier.id}>
                                  <td className="fr-col-2">
                                    <p className="fr-badge fr-badge--info fr-badge--no-icon fr-mb-1v">
                                      {dossier.etat.libelle}
                                    </p>
                                    <br />
                                    {dossier.reference}
                                  </td>
                                  <td className="fr-col-4">
                                    <span className="fr-text--lg fr-text--bold">
                                      {dossier.requerant}
                                    </span>
                                    <br />
                                    {dossier.adresse}
                                  </td>
                                  <td className="fr-col-3">
                                    {_.capitalize(
                                      dossier.dateDepot?.toLocaleString(
                                        "fr-FR",
                                        {
                                          weekday: "long",
                                          day: "numeric",
                                          month: "long",
                                          year: "numeric",
                                          hour: "numeric",
                                          minute: "numeric",
                                        },
                                      ),
                                    )}
                                  </td>
                                  <td className="fr-col-2">
                                    {dossier.attributaire ? (
                                      <span className="fr-text--bold">
                                        {dossier.attributaire.nom}
                                      </span>
                                    ) : (
                                      <i>non attribué</i>
                                    )}
                                  </td>
                                  <td className="fr-col-1">
                                    <div className="fr-btns-group fr-btns-group--right">
                                      <a
                                        className="fr-btn fr-btn--tertiary fr-icon-eye-line fr-btn--sm"
                                        href={`/agent/redacteur/dossier/${dossier.id}`}
                                      ></a>
                                    </div>
                                  </td>
                                </tr>
                              ))
                            ) : (
                              <tr>
                                <td colSpan="5">
                                  <p className="fr-p-2w">
                                    Aucun dossier correspondant
                                  </p>
                                </td>
                              </tr>
                            )}
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
    );
  },
);
