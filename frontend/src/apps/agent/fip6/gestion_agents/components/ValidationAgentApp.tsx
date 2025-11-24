import { router } from "@/apps/agent/fip6/_init";
import { Route } from "@/apps/agent/fip6/routes/agents/gestion";
import { Administration, Agent } from "@/common/models";
import { estCourrielValide } from "@/common/services/courriel.ts";
import { useNavigate } from "@tanstack/react-router";
import React, { ChangeEvent, useCallback, useState } from "react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useInjection } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import "@/style/index.css";
import { Loader } from "@/common/components/Loader.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import {
  RoleAgent,
  TypeAdministration,
  TypeRoleAgent,
} from "@/common/models/Agent.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Button, ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { AgentContext } from "@/apps/agent/_commun/contexts";

const ValidationAgentLigne = ({
  agent,
  editer,
  agentEditeur,
  incarner,
}: {
  agent: Agent;
  editer: () => void;
  agentEditeur: Agent;
  incarner: (agent: Agent) => void;
}) => {
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
          <b> {agent.administration.libelle}</b>
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
            ...(agentEditeur.estBetagouv()
              ? [
                  {
                    children: "Se connecter en tant que",
                    priority: "secondary",
                    iconId: "fr-icon-account-pin-circle-line",
                    onClick: () => incarner(agent),
                  } as ButtonProps,
                ]
              : []),
          ]}
        />
      </div>
    </div>
  );
};

interface ValidationAgent {
  creation: boolean;
  prenom: string;
  nom: string;
  courriel: string;
  administration?: Administration;
  roles?: Map<TypeRoleAgent, boolean>;
}

const validationAgent = (agent?: Agent): ValidationAgent => ({
  creation: !agent?.id,
  prenom: agent?.prenom ?? "",
  nom: agent?.nom ?? "",
  courriel: agent?.courriel ?? "",
  administration: agent?.administration,
  roles: agent?.administration
    ? new Map(
        agent.administration
          .roles()
          .map((r) => [
            r.type,
            agent?.aRole(r) ?? agent.administration != Administration.MJ,
          ]),
      )
    : undefined,
});

const FormulaireEditionAgent = ({
  agent,
  onEdite,
}: {
  agent?: Agent;
  onEdite: (agent: Agent) => void;
}) => {
  const agentManager = useInjection<AgentManagerInterface>(
    AgentManagerInterface.$,
  );

  const [etape, setEtape] = useState<"edition" | "sauvegarde" | "resultat">(
    "edition",
  );

  const [validation, setValidation] = useState<ValidationAgent>(
    validationAgent(agent),
  );

  console.log(validation.administration);

  const aRole = useCallback(
    (...roles: RoleAgent[]) =>
      validation.roles
        ?.entries()
        .some(
          (e: [TypeRoleAgent, boolean]) =>
            e[1] && roles.map((r) => r.type).includes(e[0]),
        ),
    [validation.roles],
  );

  return (
    <>
      <div className="fr-grid-row fr-grid-row--gutters fr-mt-3w">
        {etape === "resultat" ? (
          <Alert
            severity="success"
            title={
              validation.creation
                ? "L'agent a bien été créé"
                : "L'agent a bien été mis à jour"
            }
          />
        ) : (
          <>
            <div className="fr-col-6">
              <h5>Informations</h5>
              <Input
                label="Prénom"
                disabled={!validation.creation}
                nativeInputProps={{
                  value: agent?.prenom,
                  onChange: (event) =>
                    setValidation({
                      ...validation,
                      prenom: event.target.value,
                    }),
                }}
              />
              <Input
                label="Nom"
                disabled={!validation.creation}
                nativeInputProps={{
                  value: agent?.nom,
                  onChange: (event) =>
                    setValidation({ ...validation, nom: event.target.value }),
                }}
              />
              <Input
                label="Adressse courriel"
                disabled={!validation.creation}
                state={
                  validation.courriel && !estCourrielValide(validation.courriel)
                    ? "error"
                    : undefined
                }
                stateRelatedMessage={
                  validation.courriel && !estCourrielValide(validation.courriel)
                    ? "L'adresse courriel n'est pas valide"
                    : undefined
                }
                nativeInputProps={{
                  type: "email",
                  value: agent?.courriel,
                  onChange: (event) => {
                    const administration =
                      validation.administration ??
                      Administration.pourCourriel(event.target.value)?.at(0);
                    setValidation({
                      ...validation,
                      courriel: event.target.value,
                      administration: administration,
                      roles: administration
                        ? new Map(
                            administration
                              .roles()
                              .map((r) => [
                                r.type,
                                administration != Administration.MJ,
                              ]),
                          )
                        : undefined,
                    });
                  },
                }}
              />

              <Select
                label="Administration"
                disabled={!validation.creation}
                nativeSelectProps={{
                  value: validation.administration?.type,
                  onChange: (event) => {
                    const administration = event.target.value
                      ? Administration.pourType(
                          event.target.value as TypeAdministration,
                        )
                      : undefined;

                    setValidation({
                      ...validation,
                      administration: administration,
                      roles: administration
                        ? new Map(
                            administration
                              .roles()
                              .map((r) => [
                                r.type,
                                administration != Administration.MJ,
                              ]),
                          )
                        : undefined,
                    });
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

              {validation.roles ? (
                <Checkbox
                  options={validation.roles
                    .entries()
                    .toArray()
                    .map(
                      ([typeRole, actif]: [
                        typeRole: TypeRoleAgent,
                        actif: boolean,
                      ]) => {
                        const role = RoleAgent.pourType(typeRole) as RoleAgent;

                        return {
                          label: role.libelle,
                          hintText: role.description,
                          nativeInputProps: {
                            name: `permission-${role.type}`,
                            checked: validation.roles?.get(role.type) || false,
                            disabled:
                              role === RoleAgent.DOSSIER &&
                              aRole(
                                RoleAgent.ATTRIBUTEUR,
                                RoleAgent.REDACTEUR,
                                RoleAgent.VALIDATEUR,
                                RoleAgent.LIAISON_BUDGET,
                              ),
                            onChange: (
                              event: ChangeEvent<HTMLInputElement>,
                            ) => {
                              const roles = validation.roles?.set(
                                role.type,
                                event.target.checked,
                              );

                              if (
                                event.target.checked &&
                                [
                                  RoleAgent.ATTRIBUTEUR,
                                  RoleAgent.REDACTEUR,
                                  RoleAgent.VALIDATEUR,
                                  RoleAgent.LIAISON_BUDGET,
                                ].includes(role)
                              ) {
                                roles?.set(RoleAgent.DOSSIER.type, true);
                              }

                              setValidation({
                                ...validation,
                                roles: roles,
                              });
                            },
                          },
                        };
                      },
                    )}
                />
              ) : (
                <p>
                  Vous devez d'abord choisir une administration pour pouvoir
                  attribuer des permissions.
                </p>
              )}
            </div>
          </>
        )}

        <div className="fr-col-12">
          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsSize="small"
            buttons={
              etape === "resultat"
                ? [
                    {
                      children: "Terminer",
                      priority: "secondary",
                      onClick: () => modaleEditionAgent.close(),
                    },
                  ]
                : [
                    {
                      children: "Annuler",
                      priority: "tertiary no outline",
                      disabled: false,
                      onClick: () => modaleEditionAgent.close(),
                    },
                    {
                      children: "Réinitialiser",
                      priority: "secondary",
                      onClick: () => setValidation(validationAgent(agent)),
                    },
                    {
                      children:
                        etape === "sauvegarde"
                          ? "Sauvegarde en cours..."
                          : "Sauvegarder l'agent",
                      priority: "primary",
                      disabled:
                        etape === "sauvegarde" ||
                        !validation.prenom ||
                        !validation.nom ||
                        !validation.courriel ||
                        !estCourrielValide(validation.courriel) ||
                        !validation.administration,
                      onClick: async () => {
                        setEtape("sauvegarde");
                        onEdite(
                          await agentManager.editerAgent({
                            prenom: validation.prenom,
                            nom: validation.nom,
                            courriel: validation.courriel,
                            administration:
                              validation.administration as Administration,
                            roles: validation.roles
                              ?.entries()
                              .toArray()
                              .filter(([_, actif]) => actif)
                              .map(
                                ([type, _]) =>
                                  RoleAgent.pourType(type) as RoleAgent,
                              ),
                          }),
                        );
                        setEtape("resultat");
                      },
                    },
                  ]
            }
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
  const { context }: { context: AgentContext } = Route.useLoaderData();
  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  const agentManager = useInjection<AgentManagerInterface>(
    AgentManagerInterface.$,
  );

  const queryClient = useQueryClient();

  const {
    isPending,
    isError,
    data: agents = [],
    error,
  } = useQuery<Agent[]>({
    queryKey: ["fip6-agents"],
    queryFn: () => agentManager.agentsActifs(),
  });

  const [agentSelectionne, selectionnerAgent] = useState<Agent | undefined>();

  return (
    <>
      <modaleEditionAgent.Component
        size="large"
        title=" Gérer l'agent"
        iconId="fr-icon-settings-5-fill"
      >
        <FormulaireEditionAgent
          key={
            agentSelectionne ? `agent-${agentSelectionne.id}` : "nouvel-agent"
          }
          agent={agentSelectionne}
          onEdite={(agent: Agent) =>
            queryClient.setQueryData(["fip6-agents"], (agents: Agent[]) =>
              [agent].concat(...agents.filter((a) => a.id !== agent.id)),
            )
          }
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
              children="Ajouter un agent"
              title="Ajouter un agent"
              iconId="fr-icon-add-line"
              iconPosition="right"
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
              agentEditeur={context.agent}
              incarner={(agent: Agent) =>
                naviguer({
                  href: `${window.location.origin}/agent/?_switch_user=${agent.identifiant}`,
                })
              }
              editer={() => {
                selectionnerAgent(agent);
                modaleEditionAgent.open();
              }}
            />
          ))}
        </>
      )}
    </>
  );
};
