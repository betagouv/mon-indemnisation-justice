import "@/apps/_init.ts";
import { Link, LinkProps, RouterProvider } from "@tanstack/react-router";
import React, { JSX, StrictMode } from "react";
import ReactDOM from "react-dom/client";

import { RouteurPublic } from "@/apps/public/routeur";
import { container } from "@/apps/public/container";
import { queryClient } from "@/apps/public/query";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { QueryClientProvider } from "@tanstack/react-query";
import { Provider } from "inversify-react";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";

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

const root = ReactDOM.createRoot(document.getElementById("react-app")!);
root.render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider router={RouteurPublic} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
