import { ListeDossiers } from "@/apps/agent/fip6/composants/dossiers/ListeDossiers.tsx";
import { Loader } from "@/common/composants/Loader.tsx";
import { Agent, DossierApercu, EtatDossierType, Redacteur } from "@/common/models";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { Input } from "@codegouvfr/react-dsfr/Input";
import Pagination from "@codegouvfr/react-dsfr/Pagination";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { RegisteredLinkProps } from "@codegouvfr/react-dsfr/src/link.tsx";
import { useQuery } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import { plainToInstance } from "class-transformer";
import _ from "lodash";
import React, { useMemo, useState } from "react";

export type RechercheRequete = {
  etatsDossier: EtatDossierType[];
  attributaires: Redacteur[];
  inclureDossierNonAttribue: boolean;
  // Recherche textuelle libre
  recherche: string;
  page: number;
};

type RechercheReponse = {
  resultats: DossierApercu[];
  taille: number;
  total: number;
  page: number;
};

export const validerParametres = (
  search: Record<string, string | string[] | undefined>,
) => {
  return {
    p: search.p ? parseInt(search.p as string) : 1,
    a: search.a?.toString(),
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
};

/**
 * Convertit une requête de recherche en paramètres d'URL, en excluant les valeurs non définies.
 *
 * @param requete RechercheRequete
 *
 * @returns Record<string, string>
 */
export const requeteVersParametres = (
  requete: RechercheRequete,
): Record<string, string> => {
  return Object.fromEntries(
    Object.entries({
      p: requete.page?.toString(),
      a:
        requete.attributaires?.length > 0 || requete.inclureDossierNonAttribue
          ? requete.attributaires
              .map((a) => a.id.toString())
              .concat(...(requete.inclureDossierNonAttribue ? ["_"] : []))
              .join("|")
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

export const RechercherDossierPage = ({
  agent,
  redacteurs,
  requete,
  construireLien,
  activerFiltreAttributaires = true,
}: {
  agent: Agent;
  redacteurs: Redacteur[];
  requete: RechercheRequete;
  construireLien: ({
    changement,
  }: {
    changement: Partial<RechercheRequete>;
  }) => RegisteredLinkProps;
  activerFiltreAttributaires?: boolean;
}) => {
  // Fonction de navigation à partir de cette route
  const naviguer = useNavigate();

  // Afficher les critères de recherche ou non
  const [afficherCriteres, setAfficherCriteres] = useState(false);

  // Traduire la requête de recherche en paramètres d'URL
  const urlParametres = useMemo(() => requeteVersUrl(requete), [requete]);

  // Requête de récupération des dossiers
  const {
    isPending,
    error,
    data: reponse,
  } = useQuery<RechercheReponse>({
    queryKey: ["recherche-dossiers", urlParametres],
    queryFn: async () => {
      const reponse = await fetch(
        `/api/agent/fip6/dossiers/rechercher?${urlParametres}`,
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
                      await naviguer(
                        construireLien({
                          changement: {
                            recherche: e.target.value as string,
                          },
                        }),
                      );
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
                      await naviguer(
                        construireLien({
                          changement: {
                            etatsDossier: EtatDossierType.liste.filter((e) =>
                              Array.from(event.target.selectedOptions)
                                .map((option) => option.value)
                                .includes(e.slug),
                            ),
                          },
                        }),
                      );
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

              {activerFiltreAttributaires && (
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
                            await naviguer(
                              construireLien({
                                changement: {
                                  inclureDossierNonAttribue: e.target.checked,
                                },
                              }),
                            );
                          },
                          checked: requete.inclureDossierNonAttribue,
                        },
                      },
                      ...(redacteurs || []).map((redacteur) => ({
                        label:
                          agent.id === redacteur.id ? (
                            <b>Moi</b>
                          ) : (
                            redacteur.nom
                          ),
                        nativeInputProps: {
                          onChange: async (e) => {
                            await naviguer(
                              construireLien({
                                changement: {
                                  attributaires: requete.attributaires
                                    .filter((a) => a.id !== redacteur.id)
                                    .concat(
                                      ...(e.target.checked ? [redacteur] : []),
                                    ),
                                },
                              }),
                            );
                          },
                          checked: requete.attributaires.includes(redacteur),
                        },
                      })),
                    ]}
                    state="default"
                  />
                </div>
              )}
            </div>
          </Accordion>

          {reponse ? (
            <div className="fr-grid-row">
              <div className="fr-col-12 fr-my-2w">
                <h6 className="fr-m-0">
                  {isPending ? (
                    "Chargement des dossiers ..."
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
                      ...construireLien({
                        changement: {
                          page: pageNumber,
                        },
                      }),
                      "aria-current":
                        requete.page === pageNumber ? "true" : undefined,
                    })}
                  />
                </div>
              )}
              {/* TODO convertir en un composant réutilisable avec l'usage de https://components.react-dsfr.codegouv.studio/?path=/docs/components-table--default */}
              <div className="fr-col-12 fr-my-1w">
                {isPending ? (
                  <Loader />
                ) : (
                  <ListeDossiers dossiers={reponse.resultats} />
                )}
              </div>
              {!isPending && (
                <div className="fr-col-12 fr-mt-2w">
                  <Pagination
                    count={Math.ceil((1 * reponse.total) / reponse.taille)}
                    defaultPage={requete.page}
                    showFirstLast={true}
                    getPageLinkProps={(pageNumber: number) => ({
                      ...construireLien({
                        changement: {
                          page: pageNumber,
                        },
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
};
