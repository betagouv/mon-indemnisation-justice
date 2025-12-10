import * as React from "react";
import {
  createRootRouteWithContext,
  type LinkProps,
  Outlet,
  redirect,
  useLoaderData,
} from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import { AgentContext } from "@/apps/agent/_commun/contexts";
import { Header } from "@codegouvfr/react-dsfr/Header";
import Badge from "@codegouvfr/react-dsfr/Badge";
import Footer from "@codegouvfr/react-dsfr/Footer";
import "@/style/index.css";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";

const EspaceFDO = () => {
  const { contexte }: { contexte: AgentContext } = useLoaderData({} as any);

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
                  Espace FDO
                </Badge>
                <Badge as="span" noIcon severity="warning" className="fr-mx-1v">
                  Plateforme test
                </Badge>
              </>
            )}
          </>
        }
        navigation={[
          {
            text: "Accueil",
            linkProps: {
              to: "/agent/fdo",
              activeOptions: { exact: true },
            },
          },
          {
            text: "Déclaration d'erreur opérationnelle",
            menuLinks: [
              {
                text: "Mes déclarations",
                linkProps: {
                  to: "/agent/fdo/erreur-operationnelle/mes-declarations",
                  activeOptions: { exact: true },
                },
              },
              {
                text: "Nouvelle déclaration",
                linkProps: {
                  to: "/agent/fdo/erreur-operationnelle/nouvelle-declaration",
                  activeOptions: { exact: false },
                },
              },
            ],
          },
          {
            text: "Réquisition d'un serrurier",
            linkProps: {
              href: "https://www.serruriers-de-france.com/gouv/",
              target: "_blank",
            },
          },
          {
            text: "Foire aux questions",
            linkProps: {
              to: "/agent/fdo/foire-aux-questions",
              activeOptions: { exact: true },
            },
          },
        ]}
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
            linkProps: {},
          },
          {
            iconId: "fr-icon-logout-box-r-line",
            text: contexte.incarnePar ? (
              <>Redevenir {contexte.incarnePar}</>
            ) : (
              "Déconnexion"
            ),
            linkProps: {
              href: contexte.incarnePar
                ? `${window.location.origin}/agent/fip6/agents/gestion?_switch_user=_exit`
                : `${window.location.origin}/agent/deconnexion`,
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
          } as LinkProps
        }
      />

      <TanStackRouterDevtools />
    </>
  );
};

export const Route = createRootRouteWithContext<AgentContext>()({
  beforeLoad: async ({ context }) => {
    {
      if (!context.agent.estFDO()) {
        throw redirect({
          href: `${window.location.origin}/agent/`,
        });
      }
    }
  },
  loader: async ({ context }) => ({ contexte: context }),
  component: () => <EspaceFDO />,
  notFoundComponent: () => {
    return (
      <div>
        {/* TODO s'inspirer de pages d'erreur comme celles-ci https://ui.mantine.dev/category/error-pages/ */}
        <p>Oups ce chemin n'existe pas</p>
      </div>
    );
  },
  scripts: () =>
    import.meta.env.DEV
      ? [
          {
            type: "module",
            children: `import RefreshRuntime from "/_build/@react-refresh";
RefreshRuntime.injectIntoGlobalHook(window)
window.$RefreshReg$ = () => {}
window.$RefreshSig$ = () => (type) => type`,
          },
        ]
      : [],
});
