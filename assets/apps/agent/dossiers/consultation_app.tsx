import "reflect-metadata";
import {Document, DocumentType} from "@/apps/agent/dossiers/models";
import {DossierDetail} from "@/apps/agent/dossiers/models/Dossier";
import {disableReactDevTools} from '@/react/services/devtools.js';
import {plainToInstance} from "class-transformer";
import React from 'react';
import ReactDOM from "react-dom/client";

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

const args = JSON.parse(document.getElementById('react-arguments').textContent);

const dossier = plainToInstance(DossierDetail, args.dossier, {enableImplicitConversion: true})

ReactDOM
    .createRoot(document.getElementById('react-app-agent-consultation-dossiers'))
    .render(
        <>
            <div className="fr-container fr-container--fluid fr-mt-2w">
                <div className="fr-grid-row fr-grid-row--gutters">

                    <div className="fr-col-12 fr-p-3w">

                        {/*  Résumé de l'état + boutons */}
                        <div className="fr-dossier-etat fr-p-4w">

                            <h3 className="">Dossier {dossier.reference}</h3>

                            <div className="">
                                <p className="fr-badge fr-badge--info fr-badge--no-icon">À statuer</p>
                                <p className="fr-badge fr-badge--new fr-badge--no-icon">Nouveau dossier</p>
                            </div>

                            <p className="fr-m-1w">Déposé le {dossier.dateDepot?.toLocaleString('fr-FR', {
                                weekday: 'long',
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric',
                            })}, à {dossier.dateDepot?.toLocaleString('fr-FR', {
                                hour: 'numeric',
                                minute: 'numeric'
                            })} par <u>{dossier.requerant.nomSimple()}</u></p>

                            <p className="fr-m-1w">Ce dossier n'est <i>pas encore attribué</i> à un rédacteur.</p>


                            <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                                {/*  Actions, à adapter en fonction de l'état du dossier et des droits de l'agent */}
                                <li>
                                    <button className="fr-btn fr-btn--sm fr-btn--secondary" disabled>
                                        Refuser le dossier
                                    </button>

                                </li>
                                <li>
                                    <button className="fr-btn fr-btn--sm" type="button" data-fr-opened="false"
                                            aria-controls="fr-modal-pre-valider" disabled>
                                        Accepter l'indemnisation
                                    </button>
                                </li>
                            </ul>

                        </div>


                        {/* Accordéon de section */}
                        <div className="fr-my-2w">
                            <div className="fr-accordions-group">
                                <section className="fr-accordion">
                                    <h3 className="fr-accordion__title">
                                        <button className="fr-accordion__btn" aria-expanded="true"
                                                aria-controls="accordion-114">Informations du dossier
                                        </button>
                                    </h3>
                                    <div className="fr-collapse" id="accordion-114">
                                        <div className="fr-grid-column">
                                            <h5>Requérant</h5>

                                            <ul className="fr-list">
                                                <li>
                                                    <b>Nom: </b> {dossier.requerant.nomComplet()}
                                                </li>
                                                <li>
                                                    <b>Prénom(s): </b> {dossier.requerant.prenoms.filter((p) => !!p).join(', ')}
                                                </li>
                                                {dossier.requerant.dateNaissance &&
                                                    <li>
                                                        <b>Né{dossier.requerant.estFeminin() ? 'e' : ''}</b> le {dossier.requerant.dateNaissance.toLocaleString('fr-FR', {
                                                        //weekday: 'long',
                                                        day: 'numeric',
                                                        month: 'long',
                                                        year: 'numeric',
                                                    })},
                                                        à {dossier.requerant.communeNaissance} {dossier.requerant.paysNaissance ? `(${dossier.requerant.paysNaissance})` : ''}
                                                    </li>
                                                }
                                                {dossier.requerant.estPersonneMorale() &&
                                                    <li>
                                                        Représentant{dossier.requerant.estFeminin() ? 'e' : ''} légal{dossier.requerant.estFeminin() ? 'e' : ''} de
                                                        la
                                                        société <b>{dossier.requerant.raisonSociale}</b> (SIREN: {dossier.requerant.siren})
                                                    </li>
                                                }
                                            </ul>
                                        </div>
                                        <div className="fr-grid-column">
                                            <h5>
                                                Bris de porte
                                            </h5>

                                            <ul>
                                                <li>
                                                    <b>Survenu à l'adresse: </b> {dossier.adresse.libelle()}
                                                </li>
                                                <li>
                                                    <b>Le :</b> {dossier.dateOperation ?

                                                    (<>{
                                                        dossier.dateOperation?.toLocaleString('fr-FR', {
                                                            weekday: 'long',
                                                            day: 'numeric',
                                                            month: 'long',
                                                            year: 'numeric',
                                                        })
                                                    }, à {
                                                        dossier.dateOperation?.toLocaleString('fr-FR', {
                                                            hour: 'numeric',
                                                            minute: 'numeric'
                                                        })
                                                    }</>)
                                                    :
                                                    <i>non renseigné</i>
                                                }
                                                </li>
                                                <li>
                                                    <b>Porte blindée: </b> {
                                                    dossier.estPorteBlindee !== null ?
                                                        (<>
                                                            {dossier.estPorteBlindee ? 'oui' : 'non'}
                                                        </>) :
                                                        <i>non renseigné</i>
                                                }
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </section>

                                <section className="fr-accordion">
                                    <h3 className="fr-accordion__title">
                                        <button className="fr-accordion__btn" aria-expanded="false"
                                                aria-controls="accordion-115">Pièces jointes
                                        </button>
                                    </h3>
                                    <div className="fr-collapse" id="accordion-115">

                                        <div className="fr-tabs">
                                            <ul className="fr-tabs__list" role="tablist" aria-label="Pièces jointes">
                                                {Document.types.map((type: DocumentType, index: number) =>
                                                    <li role="presentation" key={type.type}>
                                                        <button
                                                            id={`tab-document-${type.type}`}
                                                            className="fr-tabs__tab"
                                                            tabIndex={index == 0 ? 0 : -1}
                                                            role="tab"
                                                            aria-selected={index == 0}
                                                            aria-controls={`tab-document-${type.type}-panel`}
                                                            disabled={!dossier.documents.has(type.type)}
                                                        >
                                                            {type.libelle} {dossier.documents.has(type.type) ? `(${dossier.documents.get(type.type)?.length})` : ''}
                                                        </button>
                                                    </li>
                                                )}
                                            </ul>
                                            {Document.types.map((type: DocumentType, index: number) =>
                                                <div
                                                    key={`document-tab-${type.type}`}
                                                    id={`tab-document-${type.type}-panel`}
                                                    className={`fr-tabs__panel ${index == 0 ? 'fr-tabs__panel--selected' : ''}`}
                                                    role="tabpanel"
                                                    aria-labelledby={`tab-document-${type.type}`}
                                                    tabIndex={0}
                                                >
                                                    <h4>{type.libelle}</h4>

                                                    {dossier.documents.has(type.type) && dossier.documents.get(type.type).map((document: Document) =>
                                                        (<div className="fr-grid-row" key={document.id}>
                                                            <h6>{document.originalFilename}</h6>
                                                            {document.mime == 'application/pdf' ?
                                                                <object
                                                                    data={document.url}
                                                                    type="application/pdf"
                                                                    style={{width: '100%', aspectRatio: '210/297'}}></object>
                                                                :
                                                                <img
                                                                    src={document.url}
                                                                    style={{width: '100%', maxHeight: '100vh'}} />
                                                            }
                                                        </div>)
                                                    )}
                                                </div>
                                            )}

                                        </div>

                                    </div>
                                </section>


                                <section className="fr-accordion">
                                    <h3 className="fr-accordion__title">
                                        <button className="fr-accordion__btn" aria-expanded="false"
                                                aria-controls="accordion-116" disabled>Lettre de synthèse
                                        </button>
                                    </h3>
                                </section>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </>
    );