import * as React from "react";
import {
  createRootRouteWithContext,
  type LinkProps,
  Outlet,
  useLoaderData,
} from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import { AgentContext } from "@/routers/contexts/AgentContext.ts";
import { Header } from "@codegouvfr/react-dsfr/Header";
import Badge from "@codegouvfr/react-dsfr/Badge";
import Footer from "@codegouvfr/react-dsfr/Footer";
import "@/style/index.css";
import { Agent } from "@/common/models";

const EspaceFDO = () => {
  const { agent }: { agent: Agent } = useLoaderData({} as any);

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
                text: "Nouvelle déclaration",
                linkProps: {
                  to: "/agent/fdo/erreur-operationnelle/nouvelle-declaration",
                  activeOptions: { exact: false },
                },
              },
              {
                text: "Mes déclarations",
                linkProps: {
                  to: "/agent/fdo/erreur-operationnelle/mes-declarations",
                  activeOptions: { exact: true },
                },
              },
            ],
          },
          {
            text: "Réquisition d'un serrurier",
            linkProps: {
              to: "/agent/fdo/requisition-serrurier",
              activeOptions: { exact: true },
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
            text: agent.nomComplet(),
            linkProps: {},
          },
          {
            iconId: "fr-icon-logout-box-r-line",
            text: "Déconnexion",
            linkProps: {
              to: "/agent/deconnexion" as string,
              href: "/agent/deconnexion",
              reloadDocument: true,
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
            to: "/mentions-legales" as string,
            href: "/mentions-legales",
            reloadDocument: true,
          } as LinkProps
        }
      />

      <TanStackRouterDevtools />
    </>
  );
};

export const Route = createRootRouteWithContext<AgentContext>()({
  loader: async ({ context }) => ({ agent: await context.agent }),
  component: () => <EspaceFDO />,
  notFoundComponent: () => {
    return (
      <div>
        {/* TODO s'inspirer de pages d'erreur comme celles-ci https://ui.mantine.dev/category/error-pages/ */}
        <p>Oups ce chemin n'existe pas</p>
      </div>
    );
  },
});
