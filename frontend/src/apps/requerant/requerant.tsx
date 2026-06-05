import "@/apps/_init.ts";
import { queryClient } from "@/apps/requerant/query";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { Chargement } from "@/common/composants/dsfr/Chargement";
import { ErreurComposant } from "@/common/composants/erreur/ErreurComposant";
import { NonTrouveComposant } from "@/common/composants/erreur/NonTrouveComposant";
import { Button } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { QueryClientProvider } from "@tanstack/react-query";
import { Link, LinkProps, RouterProvider } from "@tanstack/react-router";
import React, { JSX, StrictMode } from "react";

import { container } from "@/apps/requerant/container.ts";
import { Provider } from "inversify-react";
import ReactDOM from "react-dom/client";

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { Crisp } from "crisp-sdk-web";
import { CacheBuster } from "react-cache-refresh";

let theme: ColorScheme | "system" = "system";
try {
  theme = localStorage.getItem("scheme") as ColorScheme;
} catch (error) {
  theme = "system";
}

startReactDsfr({
  defaultColorScheme: theme,
  Link,
});

if (import.meta.env?.VITE_CRISP_WEBSITE_ID) {
  Crisp.configure(import.meta.env?.VITE_CRISP_WEBSITE_ID);
}

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

const root = ReactDOM.createRoot(document.body);
root.render(
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
            router={RouteurRequerant}
            defaultErrorComponent={({ error, info, reset }) => (
              <ErreurComposant
                erreur={error}
                retour={
                  Crisp.isCrispInjected() ? (
                    <Button
                      className="fr-my-2w"
                      onClick={() => Crisp.chat.show()}
                    >
                      Contactez-nous
                    </Button>
                  ) : undefined
                }
                action={
                  <ButtonsGroup
                    inlineLayoutWhen="always"
                    alignment="center"
                    buttonsIconPosition="left"
                    buttonsEquisized={false}

                    buttons={[
                      {
                        children: "Revenir à la liste de mes demandes",
                        linkProps: {
                          to: "/requerant/mes-demandes",
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
