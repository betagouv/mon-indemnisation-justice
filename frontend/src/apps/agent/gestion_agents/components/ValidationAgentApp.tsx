import { Administration, Agent } from "@/common/models";
import React, { ChangeEvent, FormEvent, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { useInjection } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import "@/style/index.css";
import { dateSimple } from "@/common/services/date.ts";
import { Loader } from "@/common/components/Loader.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

const ValidationAgentLigne = ({
  agent,
  editable = false,
}: {
  agent: Agent;
  editable: boolean;
}) => {
  const reinitialiser = () => {};

  const [administration, setAdministration] = useState<Administration>(
    agent.administration,
  );

  const [roles, setRoles] = useState<string[]>(agent.roles);

  return (
    <div
      key={`ligne-agent-${agent.id}`}
      className="fr-grid-row mij-liste fr-py-2v"
    >
      <div className="fr-col-4">
        <p className="fr-m-0">
          {agent.nomComplet()}
          <br />
          <span className="fr-text--xs">{agent.courriel}</span>
        </p>
      </div>
      <div className="fr-col-3">
        {administration ? (
          <p className="fr-m-0">
            Rattaché {administration.estLibelleFeminin ? "à la" : "au"}
            <b> {administration.libelle}</b>
            {!agent.administration && (
              <a
                className="fr-link fr-icon-edit-line fr-link--icon-right"
                role="button"
                title="Changer"
                onClick={() => reinitialiser()}
              ></a>
            )}
            <br />
            {agent && (
              <span className="fr-text--xs fr-m-0">
                première connexion le {dateSimple(agent.datePremiereConnexion)}
              </span>
            )}
          </p>
        ) : (
          <>pas encore rattaché à une administration</>
        )}
      </div>
      <div className="fr-col-3">
        {agent.roles.length ? (
          <ul style={{ listStyle: "none", listStylePosition: "outside" }}>
            {agent.roles.map((role) => (
              <li>{role.libelle}</li>
            ))}
          </ul>
        ) : (
          <i>Pas encore de permission accordée</i>
        )}
        <ul></ul>
      </div>
      <div className="fr-col-2">
        {/*
          <div className="fr-grid-row fr-grid-row--right">
            {!validation.estValide() ? (
              <span
                className="fr-icon-close-line fr-text-default--error"
                aria-hidden="true"
              ></span>
            ) : (
              !validation.estInchange() && (
                <span
                  className="fr-icon-check-line fr-text-default--success"
                  aria-hidden="true"
                ></span>
              )
            )}
          </div>
          */}
      </div>
    </div>
  );
};

export const ValidationAgentApp = () => {
  const agentManager = useInjection<AgentManagerInterface>(
    AgentManagerInterface.$,
  );

  const {
    isPending,
    isError,
    data: agents = [],
    error,
  } = useQuery<Agent[]>({
    queryKey: ["fip6-agents"],
    queryFn: () => agentManager.agentsActifs(),
  });

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState(false);

  let nouvelAgent = new Agent();

  const sauvegarder = async () => {
    setSauvegardeEnCours(true);
    //await liste.sauvegarder(!preDeclaration);
    setSauvegardeEnCours(false);
  };

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h2>Gestion des agents</h2>
        </div>
      </div>

      {isError && (
        <Alert
          title="Erreur lors de la récupération de la liste des agents"
          closable={false}
          description={error.message}
          severity="error"
        />
      )}

      {isPending ? (
        <Loader />
      ) : (
        <>
          <div className="fr-grid-row fr-grid-row--right fr-my-1w fr-grid-row--middle">
            <button className="fr-btn fr-btn--sm" disabled={false}>
              Ajouter un agent
            </button>
          </div>

          <div className="fr-grid-row fr-text--bold">
            <div className="fr-col-4">Nom & adresse courriel</div>
            <div className="fr-col-3">Administration</div>
            <div className="fr-col-3">Niveau d'accès</div>
            <div className="fr-col-2"></div>
          </div>

          {agents.map((agent) => (
            <ValidationAgentLigne
              key={agent.courriel}
              agent={agent}
              editable={!sauvegardeEnCours}
            />
          ))}
        </>
      )}
    </>
  );
};
