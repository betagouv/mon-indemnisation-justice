import * as React from "react";
import {
  createRootRouteWithContext,
  type LinkProps,
  Outlet,
} from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import { Header } from "@codegouvfr/react-dsfr/Header";
import Badge from "@codegouvfr/react-dsfr/Badge";
import Footer from "@codegouvfr/react-dsfr/Footer";
import "@/style/index.css";
import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";

const EspaceRequerant = () => {
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
                <Badge as="span" noIcon severity="warning" className="fr-mx-1v">
                  Plateforme test
                </Badge>
              </>
            )}
          </>
        }
        navigation={[]}
        quickAccessItems={[]}
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

export const Route = createRootRouteWithContext<ContexteUsager>()({
  beforeLoad: async ({ context }) => {
    {
      // TODO rediriger si pas connecté
    }
  },
  loader: async ({ context }) => ({ contexte: context }),
  component: () => <EspaceRequerant />,
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
