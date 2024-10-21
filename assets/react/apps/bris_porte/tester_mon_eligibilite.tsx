import * as ReactDOMClient from 'react-dom/client';
import * as React from 'react';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {useState} from "react";

class Reponses {
    estVise?: boolean = null;
    estRecherche?: boolean = null;
    estProprietaire?: boolean = null;
}

function EligibiliteTestForm({ url, csrfToken }) {
    const [ reponses, setReponses ] = useState<Reponses>({} as Reponses);

    return (
        <form
            name="eligibilite-test-form"
            action={url}
            method="POST"
        >
            <input type="hidden" name="_token" value={csrfToken}/>

            <div className="fr-callout">
                <h3 className="fr-callout__title">
                    Avant de démarrer le questionnaire
                </h3>
                <p className="fr-callout__text">
                    Il est important de noter que vous ne pouvez pas être indemnisé plusieurs fois par différents
                    intermédiaires pour le même préjudice. Afin de garantir
                    <a className="fr-link" aria-describedby="infobulle-respect-principe"
                       id="lien-infobulle-respect-principe" href="#">&nbsp;le respect de ce principe</a>,
                    nous vous prions de bien vouloir répondre aux questions suivantes.
                </p>
                <span className="fr-tooltip fr-placement" id="infobulle-respect-principe" role="tooltip"
                      aria-hidden="true">
                        En vertu du principe de réparation intégrale du préjudice sans perte ni profit, la victime d'un
                        dommage ne peut obtenir deux indemnisations distinctes en réparation du même préjudice (Cour de
                        cassation, civile, Chambre civile 2, 16 décembre 2021, 19-11.294, Inédit ; Cour de cassation,
                        civile, Chambre civile 2, 9 février 2023, 21-21.217, Publié au bulletin)
                    </span>
            </div>

            {/* Questions */}

            <div className="pr-section-formulaire">
                <h4>Personne recherchée</h4>
                <div className="fr-col-12">
                    <RadioButtons
                        legend="Êtes-vous la personne visée par l'opération des forces de l'ordre ?"
                        orientation="horizontal"
                        options={[
                            {
                                label: "Oui",
                                nativeInputProps: {
                                    checked: reponses.estVise === true,
                                    onChange: () => setReponses({...reponses, estVise: true, estRecherche: null, estProprietaire: null})
                                },
                            },
                            {
                                label: "Non",
                                nativeInputProps: {
                                    checked: reponses.estVise === false,
                                    onChange: () => setReponses({...reponses, estVise: false, estRecherche: null, estProprietaire: null})
                                }
                            }
                        ]}
                    />
                </div>

                {reponses.estVise != null &&
                    <div className="fr-col-12">
                        <RadioButtons
                            legend="Est-ce que la personne recherchée réside ou est hébergée à cette adresse ?"
                            orientation="horizontal"
                            options={[
                                {
                                    label: "Oui",
                                    nativeInputProps: {
                                        checked: reponses.estRecherche === true,
                                        onChange: () => setReponses({...reponses, estRecherche: true, estProprietaire: null})
                                    },
                                },
                                {
                                    label: "Non",
                                    nativeInputProps: {
                                        checked: reponses.estRecherche === false,
                                        onChange: () => setReponses({...reponses, estRecherche: false, estProprietaire: null})
                                    }
                                }
                            ]}
                        />
                    </div>
                }

            </div>

            {reponses.estRecherche != null &&
                <div className="pr-section-formulaire" x-show="questions.estProprietaire(reponses)">
                    <h4>Situation liée au logement et possession des attestations</h4>
                    <div className="fr-col-12">
                        <RadioButtons
                            legend="Quel est votre statut par rapport au logement ?"
                            orientation="horizontal"
                            options={[
                                {
                                    label: "Propriétaire",
                                    nativeInputProps: {
                                        checked: reponses.estProprietaire === true,
                                        onChange: () => setReponses({...reponses, estProprietaire: true})
                                    },
                                },
                                {
                                    label: "Locataire",
                                    nativeInputProps: {
                                        checked: reponses.estProprietaire === false,
                                        onChange: () => setReponses({...reponses, estProprietaire: false})
                                    }
                                }
                            ]}
                        />
                    </div>
                </div>
            }
        </form>
    );
}

const container = document.getElementById('react-app');
const formUrl = container.getAttribute('data-form-url') ?? document.location.href;
const csrfToken = container.getAttribute('data-csrf-token');
const root = ReactDOMClient.createRoot(container);


root.render(
    <React.StrictMode>
        <>
            <EligibiliteTestForm url={formUrl} csrfToken={csrfToken}/>
        </>
    </React.StrictMode>
);
