import * as React from "react";
import { createRootRouteWithContext, Outlet } from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import { AgentContext } from "@/routers/contexts/AgentContext.ts";

const EspaceFDO = () => {
  return (
    <>
      <Outlet />
      <TanStackRouterDevtools />
    </>
  );
};

export const Route = createRootRouteWithContext<AgentContext>()({
  component: () => <EspaceFDO />,
  notFoundComponent: () => {
    return (
      <div>
        {/* TODO s'inspirer de pages d'erreur comme celles-ci https://ui.mantine.dev/category/error-pages/ */}
        <p>Oups ce chemin n'existe pas</p>
      </div>
    );
  },
});
