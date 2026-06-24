import "@/apps/_init.ts";
import { ErreurComposant } from "@/common/composants/erreur/ErreurComposant";
import { NonTrouveComposant } from "@/common/composants/erreur/NonTrouveComposant";
import "@/style/agents.css";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { QueryClientProvider } from "@tanstack/react-query";
import { Link, LinkProps, RouterProvider } from "@tanstack/react-router";
import { Provider } from "inversify-react";
import React, { JSX, StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { container } from "./container";
import { queryClient } from "./query";
import { RouteurFDO } from "./routeur";

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

createRoot(document.body).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider
          router={RouteurFDO}
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
  </StrictMode>,
);
