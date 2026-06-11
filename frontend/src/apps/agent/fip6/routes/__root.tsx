import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";
import { container } from "@/apps/agent/fip6/container.ts";
import { CompteurDossiers, DossierManagerInterface } from "@/apps/agent/fip6/services/dossier.ts";
import { RoleAgent } from "@/common/models/Agent.ts";
import Badge from "@codegouvfr/react-dsfr/Badge";
import Footer from "@codegouvfr/react-dsfr/Footer";
import { Header } from "@codegouvfr/react-dsfr/Header";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";
import { createRootRouteWithContext, type LinkProps, Outlet, redirect, useLoaderData } from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import * as React from "react";
import { useMemo } from "react";

export const Route = createRootRouteWithContext<AgentContext>()({
  beforeLoad: async ({ context }) => {
    {
      if (!context.agent.estMinistere()) {
        throw redirect({
          href: `${window.location.origin}/agent/fdo`,
        });
      }
    }
  },
  loader: async ({ context }) => ({
    contexte: context,
    compteurDossiers: await container
      .get(DossierManagerInterface.$)
      .compteursDossiers(),
  }),
  component: () => <EspaceRedacteur />,
  notFoundComponent: () => {
    return (
      <div>
        {/* TODO s'inspirer de pages d'erreur comme celles-ci https://ui.mantine.dev/category/error-pages/ */}
        <p>Oups vous êtes perdu</p>
      </div>
    );
  },
});

type NavItems = Extract<
  React.ComponentProps<typeof Header>["navigation"],
  any[]
>;
type NavItem = NavItems[number];
type MenuLink = Extract<NavItem, { menuLinks: any[] }>["menuLinks"][number];

const EspaceRedacteur = () => {
  const {
    contexte,
    compteurDossiers,
  }: { contexte: AgentContext; compteurDossiers: CompteurDossiers } =
    useLoaderData({} as any);

  const liens = useMemo((): NavItem[] => {
    const { agent } = contexte;

    const liens: NavItem[] = [];

    if (agent.aRole(RoleAgent.REDACTEUR)) {
      liens.push({
        text: <>Mes dossiers</>,
        linkProps: {
          to: "/dossiers/mes-dossiers",
        },
      });
    }

    if (agent.aRole(RoleAgent.DOSSIER)) {
      liens.push({
        text: (
          <>
            Rechercher un dossier
            <span
              className="fr-icon fr-icon--sm fr-icon-search-line fr-ml-1w"
              aria-hidden="true"
            ></span>
          </>
        ),
        linkProps: {
          to: "/dossiers/rechercher",
        },
      });
    }

    if (
      agent.aAuMoinsUnRole(
        RoleAgent.BETAGOUV,
        RoleAgent.ATTRIBUTEUR,
        RoleAgent.REDACTEUR,
        RoleAgent.VALIDATEUR,
        RoleAgent.LIAISON_BUDGET,
      )
    )
      // Afficher les listes de dossiers par action (dans l'ordre chronologique
      // du cycle de vie d'un dossier)
      liens.push({
        text: "Dossiers",
        menuLinks: [
          // Liste des dossiers à catégoriser (si BETAGOUV)
          ...(agent.aRole(RoleAgent.BETAGOUV)
            ? [
                {
                  text: (
                    <>
                      À catégoriser
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["a-categoriser"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/a-categoriser",
                  },
                },
              ]
            : []),
          // Liste des dossiers à attribuer (si ATTRIBUTEUR)
          ...(agent.aRole(RoleAgent.ATTRIBUTEUR)
            ? [
                {
                  text: (
                    <>
                      À attribuer
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["a-attribuer"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/a-attribuer",
                  },
                },
              ]
            : []),
          // Liste des dossiers en instruction / à instruire (si REDACTEUR)
          ...(agent.aRole(RoleAgent.REDACTEUR)
            ? [
                {
                  text: (
                    <>
                      À instruire
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["a-instruire"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/a-instruire",
                  },
                },
                {
                  text: (
                    <>
                      En cours d'instruction
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["en-instruction"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/en-instruction",
                  },
                },
              ]
            : []),
          // Liste des décisions à signer (si VALIDATEUR)
          ...(agent.aRole(RoleAgent.VALIDATEUR)
            ? [
                {
                  text: (
                    <>
                      Rejet à signer
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["rejet-a-signer"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/rejet-a-signer",
                  },
                },
                {
                  text: (
                    <>
                      Proposition d'indemnisation à signer
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["proposition-a-signer"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/proposition-a-signer",
                  },
                },
              ]
            : []),
          // Liste des dossiers dont la déclaration d'acceptation a été retournée
          // et doit être vérifiée (si REDACTEUR)
          ...(agent.aRole(RoleAgent.REDACTEUR)
            ? [
                {
                  text: (
                    <>
                      À vérifier
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["a-verifier"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/a-verifier",
                  },
                },
              ]
            : []),
          // Liste des arrêtés de paiement à signer (si VALIDATEUR)
          ...(agent.aRole(RoleAgent.VALIDATEUR)
            ? [
                {
                  text: (
                    <>
                      Arrêté à signer
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["arrete-a-signer"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/arrete-a-signer",
                  },
                },
              ]
            : []),
          // Liste des dossiers à synchroniser avec FIP3 (si LIAISON_BUDGET)
          ...(agent.aRole(RoleAgent.LIAISON_BUDGET)
            ? [
                {
                  text: (
                    <>
                      À transmettre au Bureau du Budget
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["a-transmettre"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/a-transmettre",
                  },
                },
                {
                  text: (
                    <>
                      En attente de paiement
                      <Badge
                        as={"p"}
                        small={true}
                        className={"fr-badge--blue-ecume"}
                        children={compteurDossiers["en-attente-indemnisation"]}
                      />
                    </>
                  ),
                  linkProps: {
                    to: "/dossiers/en-attente-indemnisation",
                  },
                },
              ]
            : []),
        ] as MenuLink[],
      });

    if (agent.estGestionnairePersonnel()) {
      liens.push({
        text: "Gestion des agents",
        linkProps: {
          to: "/agents/gestion",
        },
      });
    }

    // Si l'agent n'a aucun accès, on se replie sur un lien vers la page
    // "Mon compte", sur laquelle il doit probablement déjà être
    if (liens.length === 0) {
      liens.push({
        text: "Mon compte",
        linkProps: {
          to: "/mon-compte",
        },
      });
    }

    return liens;
  }, [contexte.agent]);

  return (
    <>
      <Header
        brandTop={
          <>
            Ministère
            <br /> Justice
          </>
        }
        homeLinkProps={{
          href: "/",
          target: "_self",
          title: "Accueil - Mon Indemnisation Justice",
        }}
        serviceTitle={
          <>
            Mon Indemnisation Justice
            {document.location.hostname !==
              "mon-indemnisation.justice.gouv.fr" && (
              <>
                <br />
                <Badge as="span" noIcon severity="info" className="fr-mx-1v">
                  Espace agent
                </Badge>
                <Badge as="span" noIcon severity="warning" className="fr-mx-1v">
                  Plateforme test
                </Badge>
              </>
            )}
          </>
        }
        navigation={liens}
        quickAccessItems={[
          {
            iconId: "fr-icon-account-circle-line",
            text: contexte.incarnePar ? (
              <Tooltip
                title={`Incarné par ${contexte.incarnePar}`}
                kind="hover"
              >
                {contexte.agent.nomComplet()}
              </Tooltip>
            ) : (
              contexte.agent.nomComplet()
            ),
            linkProps: {
              to: "/mon-compte",
            },
          },
          {
            iconId: "fr-icon-logout-box-r-line",
            text: contexte.incarnePar ? (
              <>Redevenir {contexte.incarnePar}</>
            ) : (
              "Déconnexion"
            ),
            linkProps: {
              href: contexte.urlDeconnexion,
              target: "_self",
            } as LinkProps,
          },
        ]}
      />

      <main role="main">
        <div className="fr-container fr-container--fluid">
          <Outlet />
        </div>
      </main>

      <Footer
        accessibility="non compliant"
        contentDescription=""
        termsLinkProps={
          {
            href: `${window.location.origin}/mentions-legales`,
            target: "_self",
          } as LinkProps
        }
      />

      <TanStackRouterDevtools />
    </>
  );
};
