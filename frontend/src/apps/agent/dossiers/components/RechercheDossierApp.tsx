import {
    Agent,
    DossierApercu,
    EtatDossierType,
    RechercheDossier,
    Redacteur,
} from "@/common/models";
import {observer} from "mobx-react-lite";
import _ from "lodash";
import React, {useState} from "react";
import {useQuery} from "@tanstack/react-query";
import {plainToInstance} from "class-transformer";
import {Loader} from "@/common/components/Loader.tsx";
import Pagination from "@codegouvfr/react-dsfr/Pagination";
import {Input} from "@codegouvfr/react-dsfr/Input";
import {Select} from "@codegouvfr/react-dsfr/Select";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import {Accordion} from "@codegouvfr/react-dsfr/Accordion";
import {periode} from "@/common/services/date.ts";

type RechercheReponse = {
    resultats: DossierApercu[];
    taille: number;
    total: number;
    page: number;
};

export const RechercheDossierApp = observer(({agent}: { agent: Agent }) => {
    const [recherche] = useState<RechercheDossier>(() =>
        RechercheDossier.fromURL(),
    );

    const [afficherCriteres, setAfficherCriteres] = useState(false);

    //const queryClient = useQueryClient();
    const {
        isPending,
        error,
        data: reponse,
    } = useQuery<RechercheReponse>({
        queryKey: ["recherche-dossiers", recherche.state],
        queryFn: () =>
            fetch(`${location.pathname}.json?${recherche.buildURLParameters()}`)
                .then((response) => {
                    history.replaceState(null, "", recherche.toURL());
                    return response.json();
                })
                .then(
                    (data: any) =>
                        ({
                            resultats: plainToInstance(
                                DossierApercu,
                                data.resultats as any[],
                            ),
                            taille: data.taille,
                            total: data.total,
                            page: data.page,
                        }) as RechercheReponse,
                ),
    });

    return (
        <div className="fr-container fr-container--fluid fr-my-3w">
            <div className="fr-grid-row">
                <div className="fr-col-12">
                    <h1>Les dossiers</h1>

                    <Accordion
                        label="Critères de recherche"
                        expanded={afficherCriteres}
                        onExpandedChange={(afficher) => setAfficherCriteres(afficher)}
                    >
                        <div className="fr-grid-row fr-grid-row--gutters">
                            <div className="fr-col-4">
                                <Input
                                    label="Filtre"
                                    hintText="Nom, prénom du requérant, adresse, etc..."
                                    state="default"
                                    nativeInputProps={{
                                        type: "search",
                                        placeholder: "Paul, 75001 PARIS, GARNIER, ...",
                                        defaultValue: recherche.motsClefs,
                                        onChange: _.debounce((e) => {
                                            recherche.setMotsClefs(e.target.value);
                                        }, 500),
                                    }}
                                />
                            </div>

                            <div className="fr-col-4" style={{justifySelf: "stretch"}}>
                                <Select
                                    label="Statut du dossier"
                                    hint="&zwnj;"
                                    style={{height: "100%"}}
                                    nativeSelectProps={{
                                        multiple: true,
                                        size: EtatDossierType.liste.length,
                                        defaultValue: recherche
                                            .getEtatsDossierSelectionnes()
                                            .map((etat) => etat.id),

                                        onChange: (e) => {
                                            recherche.changerEtatsDossier(
                                                Array.from(e.target.selectedOptions).map(
                                                    (option: HTMLOptionElement) =>
                                                        EtatDossierType.resoudre(option.value),
                                                ),
                                            );
                                        },
                                    }}
                                >
                                    {EtatDossierType.liste.map((etat) => (
                                        <option value={etat.id} key={etat.id}>
                                            {etat.libelle}
                                        </option>
                                    ))}
                                </Select>
                            </div>

                            <div className="fr-col-4">
                                <Checkbox
                                    legend="Rédacteur attribué"
                                    hintText="&zwnj;"
                                    options={[
                                        {
                                            label: (
                                                <>
                                                    Aucun <i className="fr-mx-1v">(non attribué)</i>
                                                </>
                                            ),
                                            nativeInputProps: {
                                                value: "",

                                                onChange: (e) =>
                                                    recherche.setAttributaire(null, e.target.checked),
                                                checked: recherche.estSelectionneAttributaire(null),
                                            },
                                        },
                                        ...Redacteur.catalog().map((redacteur) => ({
                                            label: agent.equals(redacteur) ? (
                                                <b>Moi</b>
                                            ) : (
                                                redacteur.nom
                                            ),
                                            nativeInputProps: {
                                                onChange: (e) =>
                                                    recherche.setAttributaire(
                                                        redacteur,
                                                        e.target.checked,
                                                    ),
                                                checked:
                                                    recherche.estSelectionneAttributaire(redacteur),
                                            },
                                        })),
                                    ]}
                                    state="default"
                                />
                            </div>
                        </div>
                    </Accordion>

                    <div className="fr-grid-row">
                        <div className="fr-col-12 fr-my-2w">
                            <h6 className="fr-m-0">
                                {isPending ? (
                                    "Chargement des dosssiers ..."
                                ) : (
                                    <>
                                        {reponse.total} dossier
                                        {reponse.total > 1 && "s"} trouvé
                                        {reponse.total > 1 && "s"}
                                    </>
                                )}
                            </h6>
                        </div>
                        {!isPending && (
                            <div className="fr-col-12">
                                <Pagination
                                    count={Math.ceil((1 * reponse.total) / reponse.taille)}
                                    defaultPage={reponse.page}
                                    showFirstLast={false}
                                    getPageLinkProps={(pageNumber: number) => ({
                                        onClick: () => recherche.setPage(pageNumber),
                                    })}
                                />
                            </div>
                        )}
                        <div className="fr-col-12 fr-my-1w">
                            <div className="fr-table fr-m-0">
                                {isPending ? (
                                    <Loader/>
                                ) : (
                                    <div className="fr-table__wrapper">
                                        <div className="fr-table__container">
                                            <div className="fr-table__content">
                                                <table data-testid="tableau-dossiers-resultats">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col" className="fr-col-2">
                                                            Référence / état
                                                        </th>
                                                        <th scope="col" className="fr-col-4">
                                                            Idéntité et adresse du requérant
                                                        </th>
                                                        <th scope="col" className="fr-col-2">
                                                            Déposé le
                                                        </th>
                                                        <th scope="col" className="fr-col-1">
                                                            Éligible ?<br/>
                                                            Attestation ?
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
                                                    {reponse.resultats.length > 0 ? (
                                                        reponse.resultats.map(
                                                            (dossier: DossierApercu) => (
                                                                <tr key={dossier.id}>
                                                                    <td className="fr-col-2">
                                      <span className="fr-text--lg fr-text--bold">
                                        {dossier.reference}
                                      </span>
                                                                        <br/>
                                                                        <p
                                                                            className={`fr-badge fr-badge--no-icon fr-badge--dossier-etat fr-badge--dossier-etat--${dossier.etat.etat.slug} fr-mb-1v`}
                                                                            {...(dossier.etat.estCloture()
                                                                                ? {
                                                                                    "aria-describedby": `tooltip-etat-dossier-${dossier.id}`,
                                                                                }
                                                                                : {})}
                                                                        >
                                                                            {dossier.etat.libelle}
                                                                        </p>
                                                                        {dossier.etat.estCloture() && (
                                                                            <span
                                                                                className="fr-tooltip fr-placement"
                                                                                id={`tooltip-etat-dossier-${dossier.id}`}
                                                                                role="tooltip"
                                                                            >
                                          {dossier.etat.contexte?.motif || (
                                              <i>Aucun motif</i>
                                          )}
                                        </span>
                                                                        )}
                                                                        <br/>
                                                                        depuis {periode(dossier.etat.dateEntree)}
                                                                    </td>
                                                                    <td
                                                                        className="fr-col-4"
                                                                        style={{
                                                                            wordWrap: "break-word",
                                                                            whiteSpace: "pre-wrap",
                                                                        }}
                                                                    >
                                      <span className="fr-text--lg fr-text--bold">
                                        {dossier.requerant}
                                      </span>
                                                                        <br/>
                                                                        <p>{dossier.adresse}</p>
                                                                    </td>
                                                                    <td
                                                                        className="fr-col-2"
                                                                        style={{overflow: "hidden"}}
                                                                    >
                                                                        {dossier.dateDepot && (
                                                                            <>
                                                                                {_.capitalize(
                                                                                    dossier.dateDepot?.toLocaleString(
                                                                                        "fr-FR",
                                                                                        {
                                                                                            weekday: "long",
                                                                                            day: "numeric",
                                                                                            month: "long",
                                                                                            year: "numeric",
                                                                                        },
                                                                                    ),
                                                                                )}
                                                                                <br/>à{" "}
                                                                                {dossier.dateDepot?.getHours()}h
                                                                                {String(
                                                                                    dossier.dateDepot?.getMinutes(),
                                                                                ).padStart(2, "0")}
                                                                            </>
                                                                        )}
                                                                    </td>
                                                                    <td className="fr-col-1">
                                                                        {dossier.estEligible ? (
                                                                            <p className="fr-badge fr-badge--success fr-badge--no-icon">
                                                                                Oui
                                                                            </p>
                                                                        ) : (
                                                                            <p className="fr-badge fr-badge--warning">
                                                                                Non
                                                                            </p>
                                                                        )}
                                                                        <br/>
                                                                        {dossier.typeAttestation ? (
                                                                            <b>Oui</b>
                                                                        ) : dossier.typeAttestation === 'NOUVELLE_ATTESTATION' ? (
                                                                            <>Non</>
                                                                        ) : (
                                                                            <i>-</i>
                                                                        )}
                                                                    </td>
                                                                    <td className="fr-col-2">
                                                                        {dossier.redacteur ? (
                                                                            <span className="fr-text--bold">
                                          {dossier.redacteur.nom}
                                        </span>
                                                                        ) : (
                                                                            <i>non attribué</i>
                                                                        )}
                                                                    </td>

                                                                    <td className="fr-col-1">
                                                                        <div
                                                                            className="fr-btns-group fr-btns-group--right">
                                                                            <a
                                                                                className="fr-btn fr-btn--tertiary fr-icon-eye-line fr-btn--sm"
                                                                                href={`/agent/redacteur/dossier/${dossier.id}`}
                                                                            ></a>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            ),
                                                        )
                                                    ) : (
                                                        <tr>
                                                            <td colSpan={5}>
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
                                )}
                            </div>
                        </div>
                        {!isPending && (
                            <div className="fr-col-12 fr-mt-2w">
                                <Pagination
                                    count={Math.ceil((1 * reponse.total) / reponse.taille)}
                                    defaultPage={reponse.page}
                                    showFirstLast={false}
                                    getPageLinkProps={(pageNumber: number) => ({
                                        onClick: () => recherche.setPage(pageNumber),
                                    })}
                                />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
});
