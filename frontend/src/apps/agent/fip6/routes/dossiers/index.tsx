import "@/style/agents.css";
import { Agent } from "@common/models";
import { RoleAgent } from "@common/models/Agent";
import {
  createFileRoute,
  Navigate,
  useRouteContext,
} from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/dossiers/")({
  component: IndexDossiers,
});

function IndexDossiers() {
  const { agent }: { agent: Agent } = useRouteContext({
    from: Route.fullPath,
  });

  return agent.aRole(RoleAgent.DOSSIER) ? (
    <Navigate to="/dossiers/rechercher" search={{} as any} />
  ) : (
    <Navigate to="/" search={{} as any} />
  );
}
