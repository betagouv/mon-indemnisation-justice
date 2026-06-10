import { createFileRoute, Navigate, useRouteContext } from "@tanstack/react-router";
import React from "react";
import { RoleAgent } from "@/common/models/Agent.ts";

export const Route = createFileRoute("/")({
  component: RouteComponent,
});

function RouteComponent() {
  const { agent } = useRouteContext({
    from: Route.fullPath,
  });

  return (
    <>
      {agent.aRole(RoleAgent.DOSSIER) ? (
        <Navigate to="/dossiers" search={{} as any} />
      ) : (
        <Navigate to="/mon-compte" />
      )}
    </>
  );
}
