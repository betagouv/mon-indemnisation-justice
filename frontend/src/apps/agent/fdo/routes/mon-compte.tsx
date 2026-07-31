import { MonCompte } from "@/apps/agent/_commun/composants/MonCompte.tsx";
import { AgentFDOContexte } from "@fdo/routeur/contexte.ts";
import { createFileRoute, useLoaderData } from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/mon-compte")({
  loader: async ({ context }) => ({
    contexte: context,
  }),
  component: RouteComponent,
});

function RouteComponent() {
  const { contexte }: { contexte: AgentFDOContexte } = useLoaderData({} as any);
  return <MonCompte agent={contexte.agent} />;
}
