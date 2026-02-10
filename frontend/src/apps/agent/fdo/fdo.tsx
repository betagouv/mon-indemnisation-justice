import { QueryClientProvider } from "@tanstack/react-query";
import React, { StrictMode } from "react";
import { RouterProvider } from "@tanstack/react-router";
import { createRoot } from "react-dom/client";
import { Provider } from "inversify-react";
import { RouteurFDO, container, queryClient } from "./_init";
import "@/style/agents.css";

createRoot(document.body).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider router={RouteurFDO} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
