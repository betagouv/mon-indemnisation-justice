import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant";
import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
import "@/style/index.css";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { headerFooterDisplayItem } from "@codegouvfr/react-dsfr/Display";
import Footer from "@codegouvfr/react-dsfr/Footer";
import { Header } from "@codegouvfr/react-dsfr/Header";
import {
  createRootRouteWithContext,
  type LinkProps,
  NotFoundRouteProps,
  Outlet,
  useLoaderData,
} from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import * as React from "react";

const EspaceRequerant = () => {
  const { contexte }: { contexte: ContexteUsager } = useLoaderData({} as any);

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
          to: "/",
          title: "Accueil - Mon Indemnisation Justice",
        }}
        serviceTitle={
          <>
            Mon Indemnisation Justice
            {document.location.hostname !==
              "mon-indemnisation.justice.gouv.fr" && (
              <>
                <br />
                <Badge as="span" noIcon severity="warning" className="fr-mx-1v">
                  Plateforme test
                </Badge>
              </>
            )}
          </>
        }
        navigation={[
          {
            text: "Mes demandes",
            linkProps: {
              to: "/requerant/mes-demandes",
              activeOptions: { exact: true },
            },
          },
        ]}
        quickAccessItems={[
          {
            iconId: "fr-icon-account-circle-line",
            text: `${contexte.usager.personne?.prenom} ${contexte.usager.personne?.nom.toUpperCase()}`,
            linkProps: {
              href: "",
            },
          },
          {
            iconId: "fr-icon-logout-box-r-line",
            text: "Déconnexion",
            linkProps: {
              href: `${window.location.origin}/deconnexion`,
            } as LinkProps,
          },
        ]}
      />

      <main role="main" className="fr-p-2w">
        <div className="fr-container fr-container--fluid">
          <Outlet />
        </div>
      </main>

      <Footer
        accessibility="non compliant"
        contentDescription=""
        bottomItems={[headerFooterDisplayItem]}
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

export const Route = createRootRouteWithContext<ContexteUsager>()({
  beforeLoad: async ({ context }) => {
    {
      // TODO rediriger si pas connecté
    }
  },
  loader: async ({ context }) => ({ contexte: context }),
  component: () => <EspaceRequerant />,
  notFoundComponent: (props: NotFoundRouteProps) => (
    <NonTrouveComposant {...props} />
  ),
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
