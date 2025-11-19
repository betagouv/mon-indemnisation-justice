import { QueryClientProvider } from "@tanstack/react-query";
import React, { JSX, StrictMode } from "react";
import "@/apps/_init.ts";
import { Link, RouterProvider, type LinkProps } from "@tanstack/react-router";
import { createRoot } from "react-dom/client";
import { Provider } from "inversify-react";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { router } from "./_router.ts";
import { container } from "./_container.ts";
import { queryClient } from "./_queryClient.ts";

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
        <RouterProvider router={router} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
