import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
import { ErreurComposant } from "@/common/composants/erreur/ErreurComposant";
import "@/style/index.css";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { headerFooterDisplayItem } from "@codegouvfr/react-dsfr/Display";
import Footer from "@codegouvfr/react-dsfr/Footer";
import { Header } from "@codegouvfr/react-dsfr/Header";
import * as Sentry from "@sentry/react";
import { createRootRouteWithContext, type LinkProps, Outlet, useLoaderData } from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import * as React from "react";
import { useMemo } from "react";

const EspaceRequerant = () => {
  const { contexte }: { contexte: ContexteUsager } = useLoaderData({} as any);

  // On affiche les outils de développement de TanStack Router en fonction de la variable d'environnement VITE_TANSTACK_AFFICHER_DEVTOOLS
  // ou à défaut uniquement en mode développement.
  const afficherTanstackDevtools = useMemo<boolean>(
    () =>
      import.meta.env.VITE_TANSTACK_AFFICHER_DEVTOOLS != null
        ? /^true$/i.test(import.meta.env.VITE_TANSTACK_AFFICHER_DEVTOOLS)
        : import.meta.env.DEV,
    [],
  );

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
          href: `${window.location.origin}/`,
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
              href: `${window.location.origin}/requerant/deconnexion`,
              target: "_self",
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
            target: "_self",
          } as LinkProps
        }
      />

      {afficherTanstackDevtools && <TanStackRouterDevtools />}
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
  component: () => (
    <Sentry.ErrorBoundary
      fallback={({ error, componentStack, resetError }) => (
        <ErreurComposant erreur={error as Error} />
      )}
    >
      <EspaceRequerant />
    </Sentry.ErrorBoundary>
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
