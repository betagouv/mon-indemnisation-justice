import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import {
  Administration,
  RoleAgent,
  TypeAdministration,
} from "@common/models/Agent.ts";
import { RegisteredLinkProps } from "@codegouvfr/react-dsfr/src/link";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { ValidationAgentPage } from "@fip6/composants/gestion";
import { RechercheAgentRequete } from "@fip6/services/agent";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import React, { useMemo } from "react";

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

  const {
    p,
    a,
    r,
  }: {
    p: number;
    a?: string;
    r?: string;
  } = Route.useSearch();

  const requete: RechercheAgentRequete = useMemo(
    () => ({
      page: p || 1,
      taille: 20,
      actifs: false,
      administrations: a
        ?.split("|")
        .map((valeur) => Administration.pourType(valeur as TypeAdministration)),
      requete: r,
    }),
    [p, a, r],
  );

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
              search: {
                p: 1,
              } as any,
            });
          }
        }}
      >
        <ValidationAgentPage
          editeur={context.agent}
          naviguer={naviguer}
          requete={requete}
          construireLien={(changement) => {
            const nouvelleRequete: RechercheAgentRequete = {
              ...requete,
              ...changement,
            };

            return {
              to: Route.fullPath,
              search: {
                p: nouvelleRequete.page,
                a: nouvelleRequete.administrations
                  ?.map((administration) => administration.type)
                  .join("|"),
                r: nouvelleRequete.requete,
              },
            } as RegisteredLinkProps;
          }}
        />
      </Tabs>
    </>
  );
}
