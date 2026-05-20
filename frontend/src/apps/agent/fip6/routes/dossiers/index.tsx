import { AgentContext } from "@/apps/agent/_commun/contexts";
import { container } from "@/apps/agent/fdo/_init";
import { BadgesDossier } from "@/apps/agent/fip6/dossiers/components/BadgesDossier.tsx";
import { Loader } from "@/common/composants/Loader.tsx";
import {
  Agent,
  DossierApercu,
  EtatDossierType,
  Redacteur,
} from "@/common/models";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { dateEtHeureSimple, periode } from "@/common/services/date.ts";
import "@/style/agents.css";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { Input } from "@codegouvfr/react-dsfr/Input";
import Pagination from "@codegouvfr/react-dsfr/Pagination";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { useQuery } from "@tanstack/react-query";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { plainToInstance } from "class-transformer";
import _ from "lodash";
import React, { useMemo, useState } from "react";

type RechercheRequete = {
  etatsDossier: EtatDossierType[];
  attributaires: Redacteur[];
  inclureDossierNonAttribue: boolean;
  // Recherche textuelle libre
  recherche: string;
  page: number;
};

/**
 * Convertit une requête de recherche en paramètres d'URL, en excluant les valeurs non définies.
 *
 * @param requete RechercheRequete
 *
 * @returns Record<string, string>
 */
const requeteVersParametres = (
  requete: RechercheRequete,
): Record<string, string> => {
  return Object.fromEntries(
    Object.entries({
      p: requete.page.toString(),
      a:
        requete.attributaires.length > 0
          ? requete.attributaires.map((a) => a.id).join("|")
          : undefined,
      e: requete.etatsDossier.length
        ? requete.etatsDossier.map((e: EtatDossierType) => e.slug).join("|")
        : undefined,
      r: !!requete.recherche ? requete.recherche : undefined,
    })
      .filter(([_, value]) => typeof value === "string")
      .map(([key, value]) => [key, value as string]),
  );
};

/**
 * Convertit une requête de recherche en requête GET.
 *
 * @param requete RechercheRequete
 *
 * @returns string
 */
const requeteVersUrl = (requete: RechercheRequete): string => {
  return Object.entries(requeteVersParametres(requete))
    .map(([key, value]) => `${key}=${value}`)
    .join("&");
};

type RechercheReponse = {
  resultats: DossierApercu[];
  taille: number;
  total: number;
  page: number;
};

export const Route = createFileRoute("/agent/fip6/dossiers/")({
  loader: async ({ context }: { context: AgentContext }) => ({
    agent: context.agent,
    // TODO transformer par un appel API
    redacteurs: await container.get(AgentManagerInterface.$).redacteurs(),
  }),
  validateSearch: (search: Record<string, string | string[] | undefined>) => {
    return {
      p: search.p ? parseInt(search.p as string) : 1,
      a: search.a,
      e:
        search.e ||
        [
          EtatDossierType.A_ATTRIBUER,
          EtatDossierType.A_INSTRUIRE,
          EtatDossierType.EN_INSTRUCTION,
          EtatDossierType.OK_A_SIGNER,
          EtatDossierType.OK_A_APPROUVER,
          EtatDossierType.OK_A_VERIFIER,
          EtatDossierType.OK_VERIFIE,
          EtatDossierType.OK_A_INDEMNISER,
          EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
          EtatDossierType.OK_INDEMNISE,
          EtatDossierType.KO_A_SIGNER,
          EtatDossierType.KO_REJETE,
        ]
          .map((e) => e.slug)
          .join("|"),
      r: search.r,
    };
  },
  component: RouteComponent,
});

function RouteComponent() {
  // Fonction de navigation à partir de cette route
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  // Récupérer l'agent actuellement connecté
  const { agent, redacteurs }: { agent: Agent; redacteurs: Redacteur[] } =
    Route.useLoaderData();

  // Récupérer les paramètres de recherche
  const {
    p,
    a,
    e,
    r,
  }: {
    p: number;
    a?: string;
    e?: string;
    r?: string;
  } = Route.useSearch();

  // L'état de la requête de recherche de dossiers, calculée à partir des paramères GET
  const requete: RechercheRequete = useMemo<RechercheRequete>(() => {
    const etats = e?.split("|");
    const attributaires = a?.split("|").map((id) => parseInt(id));

    return {
      etatsDossier: etats
        ? EtatDossierType.liste.filter((e) => etats.includes(e.slug))
        : [
            EtatDossierType.A_ATTRIBUER,
            EtatDossierType.A_INSTRUIRE,
            EtatDossierType.EN_INSTRUCTION,
            EtatDossierType.OK_A_SIGNER,
            EtatDossierType.OK_A_APPROUVER,
            EtatDossierType.OK_A_VERIFIER,
            EtatDossierType.OK_VERIFIE,
            EtatDossierType.OK_A_INDEMNISER,
            EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
            EtatDossierType.OK_INDEMNISE,
            EtatDossierType.KO_A_SIGNER,
            EtatDossierType.KO_REJETE,
          ],
      attributaires: attributaires
        ? redacteurs.filter((redacteur: Redacteur) =>
            attributaires.includes(redacteur.id),
          )
        : [],
      inclureDossierNonAttribue: !!a?.split("|").find((v) => v === "_"),
      recherche: r ?? "",
      page: p,
    };
  }, [p, e, a, r]);

  //
  const [afficherCriteres, setAfficherCriteres] = useState(false);

  //const queryClient = useQueryClient();
  const {
    isPending,
    error,
    data: reponse,
  } = useQuery<RechercheReponse>({
    queryKey: ["recherche-dossiers", requeteVersUrl(requete)],
    queryFn: async () => {
      const reponse = await fetch(
        `/api/agent/fip6/dossiers/rechercher?${requeteVersUrl(requete)}`,
      );
      const data = await reponse.json();

      return {
        resultats: plainToInstance(DossierApercu, data.resultats as any[]),
        taille: data.taille,
        total: data.total,
        page: data.page,
      } as RechercheReponse;
    },
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
                    defaultValue: requete.recherche,
                    onChange: _.debounce(async (e) => {
                      await naviguer({
                        to: Route.fullPath,
                        search: requeteVersParametres({
                          ...requete,
                          recherche: e.target.value as string,
                        }) as any,
                      });
                    }, 500),
                  }}
                />
              </div>

              <div className="fr-col-4" style={{ justifySelf: "stretch" }}>
                <Select
                  label="Statut du dossier"
                  hint="&zwnj;"
                  style={{ height: "100%" }}
                  nativeSelectProps={{
                    multiple: true,
                    size: EtatDossierType.liste.length,
                    defaultValue: requete.etatsDossier.map((etat) => etat.slug),

                    onChange: async (event) => {
                      await naviguer({
                        to: Route.fullPath,
                        search: requeteVersParametres({
                          ...requete,
                          etatsDossier: EtatDossierType.liste.filter((e) =>
                            Array.from(event.target.selectedOptions)
                              .map((option) => option.value)
                              .includes(e.slug),
                          ),
                        }) as any,
                      });
                    },
                  }}
                >
                  {EtatDossierType.liste.map((etat) => (
                    <option value={etat.slug} key={etat.id}>
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
                        onChange: async (e) => {
                          await naviguer({
                            to: Route.fullPath,
                            search: requeteVersParametres({
                              ...requete,
                              inclureDossierNonAttribue: e.target.checked,
                            }) as any,
                          });
                        },
                        checked: requete.inclureDossierNonAttribue,
                      },
                    },
                    ...(redacteurs || []).map((redacteur) => ({
                      label:
                        agent.id === redacteur.id ? <b>Moi</b> : redacteur.nom,
                      nativeInputProps: {
                        onChange: async (e) => {
                          await naviguer({
                            to: Route.fullPath,
                            search: requeteVersParametres({
                              ...requete,
                              attributaires: requete.attributaires
                                .filter((a) => a.id !== redacteur.id)
                                .concat(
                                  ...(e.target.checked ? [redacteur] : []),
                                ),
                            }) as any,
                          });
                        },
                        checked: requete.attributaires.includes(redacteur),
                      },
                    })),
                  ]}
                  state="default"
                />
              </div>
            </div>
          </Accordion>

          {reponse ? (
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
                    defaultPage={requete.page}
                    showFirstLast={true}
                    getPageLinkProps={(pageNumber: number) => ({
                      from: Route.fullPath,
                      to: Route.fullPath,
                      search: requeteVersParametres({
                        ...requete,
                        page: pageNumber,
                      }),
                      "aria-current":
                        requete.page === pageNumber ? "true" : undefined,
                    })}
                  />
                </div>
              )}
              <div className="fr-col-12 fr-my-1w">
                <div className="fr-table fr-m-0">
                  {isPending ? (
                    <Loader />
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
                              {reponse.resultats.length > 0 ? (
                                reponse.resultats.map(
                                  (dossier: DossierApercu) => (
                                    <tr key={dossier.id}>
                                      <td className="fr-col-2">
                                        <span className="fr-text--lg fr-text--bold">
                                          {dossier.reference}
                                        </span>
                                        <br />
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
                                            {dossier.etat.contexte
                                              ?.motifRejet || (
                                              <i>Aucun motif</i>
                                            )}
                                          </span>
                                        )}
                                        <br />
                                        depuis{" "}
                                        {periode(dossier.etat.dateEntree)}
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
                                        <br />
                                        <p>{dossier.adresse}</p>
                                      </td>
                                      <td
                                        className="fr-col-2"
                                        style={{ overflow: "hidden" }}
                                      >
                                        {dossier.dateDepot && (
                                          <>
                                            {dateEtHeureSimple(
                                              dossier.dateDepot,
                                              { masquerAnneeSiCourante: true },
                                            )}
                                          </>
                                        )}
                                        <BadgesDossier dossier={dossier} />
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
                                        <div className="fr-btns-group fr-btns-group--right">
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
                    defaultPage={requete.page}
                    showFirstLast={true}
                    getPageLinkProps={(pageNumber: number) => ({
                      from: Route.fullPath,
                      to: Route.fullPath,
                      search: requeteVersParametres({
                        ...requete,
                        page: pageNumber,
                      }),
                      "aria-current":
                        requete.page === pageNumber ? "true" : undefined,
                    })}
                  />
                </div>
              )}
            </div>
          ) : (
            <Loader />
          )}
        </div>
      </div>
    </div>
  );
}
