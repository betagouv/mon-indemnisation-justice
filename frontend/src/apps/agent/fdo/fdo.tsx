import { QueryClientProvider } from "@tanstack/react-query";
import React, { StrictMode } from "react";
import "@/apps/_init.ts";
import { RouterProvider } from "@tanstack/react-router";
import { Link } from "@tanstack/react-router";
import { createRoot } from "react-dom/client";
import { Provider } from "inversify-react";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { container } from "./services";
import { router } from "./router.ts";
import { queryClient } from "./query-client.ts";

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

createRoot(document.body).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider router={router} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
