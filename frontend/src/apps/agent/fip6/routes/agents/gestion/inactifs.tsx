import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";
import { ValidationAgentPage } from "@/apps/agent/fip6/gestion_agents/components";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { RoleAgent } from "@/common/models/Agent.ts";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/agents/gestion/inactifs")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    if (!context.agent.aRole(RoleAgent.GESTION_PERSONNEL)) {
      alert("Pas autorisé");
    }
  },
  loader: ({ context }) => {
    return {
      context,
    };
  },
  component: GestionAgentsActifs,
});

function GestionAgentsActifs() {
  const { context }: { context: AgentContext } = Route.useLoaderData();
  const naviguer = useNavigate<typeof RouteurRequerant>({
    from: Route.fullPath,
  });

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h2>Gestion des agents</h2>
        </div>
      </div>

      <Tabs
        selectedTabId="inactifs"
        tabs={[
          {
            tabId: "actifs",
            label: "Agents actifs",
            iconId: "fr-icon-check-line",
          },
          {
            tabId: "inactifs",
            label: "Agents en attente de validation",
            iconId: "fr-icon-timer-line",
          },
        ]}
        onTabChange={(tab) => {
          if (tab == "actifs") {
            naviguer({
              from: Route.fullPath,
              to: "../actifs",
              search: true,
            });
          }
        }}
      >
        <ValidationAgentPage
          editeur={context.agent}
          naviguer={naviguer}
          requete={{ actifs: false }}
        />
      </Tabs>
    </>
  );
}
