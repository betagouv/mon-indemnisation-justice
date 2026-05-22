import "@/apps/_init.ts";
import { queryClient } from "@/apps/agent/fip6/query";
import { QueryClientProvider } from "@tanstack/react-query";
import { LinkProps, RouterProvider } from "@tanstack/react-router";
import React, { JSX, StrictMode } from "react";

import { container } from "@/apps/agent/fip6/container";

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { Provider } from "inversify-react";
import ReactDOM from "react-dom/client";
import { RouteurFIP6 } from "@/apps/agent/fip6/routeur";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
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

ReactDOM.createRoot(document.body).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider router={RouteurFIP6} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
