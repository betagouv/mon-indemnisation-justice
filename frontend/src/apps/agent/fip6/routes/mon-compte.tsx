import { AgentContext } from "@/apps/agent/_commun/contexts";
import { createFileRoute, useLoaderData } from "@tanstack/react-router";
import React from "react";
import { MonCompte } from "@/apps/agent/_commun/composants/MonCompte.tsx";

export const Route = createFileRoute("/mon-compte")({
  loader: async ({ context }) => ({
    contexte: context,
  }),
  component: RouteComponent,
});

function RouteComponent() {
  const { contexte }: { contexte: AgentContext } = useLoaderData({} as any);
  return <MonCompte agent={contexte.agent} />;
}
