import "@/apps/_init.ts";
import "@/style/agents.css";
import { Chargement } from "@common/composants/dsfr/Chargement";
import { QueryClientProvider } from "@tanstack/react-query";
import { Link, LinkProps, RouterProvider } from "@tanstack/react-router";
import React, { JSX, StrictMode } from "react";
import { CacheBuster } from "react-cache-refresh";
import { queryClient } from "./query";

import { container } from "./container";

import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { ErreurComposant } from "@common/composants/erreur/ErreurComposant.tsx";
import { NonTrouveComposant } from "@common/composants/erreur/NonTrouveComposant.tsx";
import * as Sentry from "@sentry/react";
import { Provider } from "inversify-react";
import ReactDOM from "react-dom/client";
import { RouteurFIP6 } from "./routeur";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
  Link,
});

declare global {
  interface Window {
    dsfr: any;
  }
}

declare module "@codegouvfr/react-dsfr/spa" {
  interface RegisterLink {
    Link: (props: LinkProps) => JSX.Element;
  }
}

ReactDOM.createRoot(document.body, {
  // Error reporting: captures all errors
  onUncaughtError: Sentry.reactErrorHandler(),
  onCaughtError: Sentry.reactErrorHandler(),
  onRecoverableError: Sentry.reactErrorHandler(),
}).render(
  <StrictMode>
    <CacheBuster
      currentAppVersion={import.meta.env.VITE_MIJ_VERSION || "dev"}
      loadingComponent={
        <Chargement
          titre="Mon Indemnisation Justice"
          message="Chargement en cours..."
        />
      }
      hideConsoleLogs={import.meta.env.PROD}
      metaJsonPath={"/meta/version.json"}
    >
      <QueryClientProvider client={queryClient}>
        <Provider container={container}>
          <RouterProvider
            router={RouteurFIP6}
            defaultErrorComponent={({ error, info, reset }) => (
              <ErreurComposant
                erreur={error}
                action={
                  <ButtonsGroup
                    inlineLayoutWhen="always"
                    alignment="center"
                    buttonsIconPosition="left"
                    buttonsEquisized={false}
                    buttons={[
                      {
                        children: "Revenir à l'accueil",
                        linkProps: {
                          to: "/",
                        },
                      },
                    ]}
                  />
                }
              />
            )}
            defaultNotFoundComponent={(props) => <NonTrouveComposant />}
          />
        </Provider>
      </QueryClientProvider>
    </CacheBuster>
  </StrictMode>,
);
