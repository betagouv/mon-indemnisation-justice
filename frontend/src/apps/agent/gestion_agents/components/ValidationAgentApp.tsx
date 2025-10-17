import { Administration, Agent } from "@/common/models";
import React, { ChangeEvent, useMemo, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { useInjection } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import "@/style/index.css";
import { dateSimple } from "@/common/services/date.ts";
import { Loader } from "@/common/components/Loader.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { RoleAgent, TypeAdministration } from "@/common/models/Agent.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Button } from "@codegouvfr/react-dsfr/Button";

const ValidationAgentLigne = ({
  agent,
  editer,
}: {
  agent: Agent;
  editer: () => void;
}) => {
  const [administration, setAdministration] = useState<Administration>(
    agent.administration,
  );

  const [roles, setRoles] = useState<RoleAgent[]>(agent.roles);

  return (
    <div
      key={`ligne-agent-${agent.id}`}
      className="fr-grid-row fr-grid-row--gutters mij-liste fr-py-2v"
    >
      <div className="fr-col-3">
        <p className="fr-m-0">
          {agent.nomComplet()}
          <br />
          <span className="fr-text--xs">{agent.courriel}</span>
        </p>
      </div>
      <div className="fr-col-3">
        <p className="fr-m-0">
          Rattaché {agent.administration.estLibelleFeminin ? "à la" : "au"}
          <b> {administration.libelle}</b>
        </p>
      </div>
      <div className="fr-col-4">
        {agent.roles.length ? (
          <ul
            style={{
              padding: 0,
              listStyle: "none",
              listStylePosition: "outside",
            }}
          >
            {agent.roles.map((role) => (
              <li key={`role-agent-${agent.id}-${role.type}`}>
                {role.libelle}
              </li>
            ))}
          </ul>
        ) : (
          <i>Pas encore de permission accordée</i>
        )}
        <ul></ul>
      </div>
      <div className="fr-col-2">
        <ButtonsGroup
          inlineLayoutWhen="never"
          buttonsSize="small"
          buttonsIconPosition="right"
          alignment="center"
          buttonsEquisized={false}
          buttons={[
            {
              children: "Gérer",
              priority: "secondary",
              iconId: "fr-icon-settings-5-line",
              onClick: () => editer(),
            },
          ]}
        />
      </div>
    </div>
  );
};

const FormulaireEditionAgent = ({
  agent,
  onEdite,
}: {
  agent: Agent;
  onEdite: (agent: Agent) => void;
}) => {
  const lectureSeule = useMemo(() => !!agent.id, [agent.id]);
  return (
    <>
      <div className="fr-grid-row fr-grid-row--gutters fr-mt-3w">
        <div className="fr-col-6">
          <h5>Informations</h5>
          <Input
            label="Prénom"
            disabled={!!agent.id}
            nativeInputProps={{
              defaultValue: agent?.prenom,
              onChange: (event) => {
                if (agent) {
                  agent.prenom = event.target.value;
                }
              },
            }}
          />
          <Input
            label="Nom"
            disabled={!!agent.id}
            nativeInputProps={{
              defaultValue: agent?.nom,
              onChange: (event) => {
                if (agent) {
                  agent.nom = event.target.value;
                }
              },
            }}
          />
          <Input
            label="Adressse courriel"
            disabled={!!agent.id}
            nativeInputProps={{
              type: "email",
              defaultValue: agent?.courriel,
              onChange: (event) => {
                if (agent) {
                  agent.courriel = event.target.value;
                }
              },
            }}
          />

          <Select
            label="Administration"
            disabled={!!agent.id}
            nativeSelectProps={{
              defaultValue: agent?.administration?.type,
              onChange: (event) => {
                if (agent) {
                  if (event.target.value) {
                    agent.administration = Administration.pourType(
                      event.target.value as TypeAdministration,
                    );
                  }
                }
              },
            }}
          >
            <option value={""}>Choisir</option>
            {Administration.liste().map((administration) => (
              <option value={administration.type} key={administration.type}>
                {administration.libelle}
              </option>
            ))}
          </Select>
        </div>

        <div className="fr-col-6">
          <h5>Permissions</h5>
          <Checkbox
            options={RoleAgent.liste().map((role: RoleAgent) => ({
              label: role.libelle,
              hintText: role.description,
              nativeInputProps: {
                name: `permission-${role.type}`,
                checked: agent.aRole(role),
                disabled:
                  role === RoleAgent.DOSSIER &&
                  agent.aAuMoinsUnRole(
                    RoleAgent.ATTRIBUTEUR,
                    RoleAgent.REDACTEUR,
                    RoleAgent.VALIDATEUR,
                    RoleAgent.LIAISON_BUDGET,
                  ),
                onChange: (event: ChangeEvent<HTMLInputElement>) => {
                  agent?.definirRole(role, event.target.checked);
                  if (
                    [
                      RoleAgent.ATTRIBUTEUR,
                      RoleAgent.REDACTEUR,
                      RoleAgent.VALIDATEUR,
                      RoleAgent.LIAISON_BUDGET,
                    ].includes(role) &&
                    !agent.aRole(role)
                  ) {
                    agent.ajouterRole(RoleAgent.DOSSIER);
                  }
                },
              },
            }))}
          />
        </div>
      </div>
    </>
  );
};

const modaleEditionAgent = createModal({
  id: "modale-edition-agent",
  isOpenedByDefault: false,
});

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

  const [agentSelectionne, selectionnerAgent] = useState<Agent | undefined>(
    agents.at(0) as Agent,
  );

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState(false);

  return (
    <>
      <modaleEditionAgent.Component
        key={agentSelectionne ? `agent-${agentSelectionne.id}` : "nouvel-agent"}
        size="large"
        title={
          agentSelectionne
            ? `Éditer les permissions de ${agentSelectionne.nomComplet()}`
            : "Ajouter un nouvel agent"
        }
      >
        <FormulaireEditionAgent
          key={
            agentSelectionne ? `agent-${agentSelectionne.id}` : "nouvel-agent"
          }
          agent={agentSelectionne ?? new Agent()}
          onEdite={(agent: Agent) => console.log(agent)}
        />
      </modaleEditionAgent.Component>

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
            <Button
              title="Ajouter un agent"
              iconId="fr-icon-add-line"
              size="medium"
              onClick={() => {
                selectionnerAgent(new Agent());
                modaleEditionAgent.open();
              }}
            />
          </div>

          <div className="fr-grid-row fr-grid-row--gutters mij-liste fr-py-3v fr-text--bold">
            <div className="fr-col-3">Nom & adresse courriel</div>
            <div className="fr-col-3">Administration</div>
            <div className="fr-col-4">Niveau d'accès</div>
            <div className="fr-col-2"></div>
          </div>

          {agents.map((agent) => (
            <ValidationAgentLigne
              key={agent.id}
              agent={agent}
              editer={() => {
                modaleEditionAgent.open();
              }}
            />
          ))}
        </>
      )}
    </>
  );
};
