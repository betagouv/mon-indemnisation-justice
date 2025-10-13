import { Administration, Agent } from "@/common/models";
import { observer } from "mobx-react-lite";
import React, { ChangeEvent, FormEvent, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { plainToInstance } from "class-transformer";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { useInjection } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { TypeAdministration } from "@/common/models/Agent.ts";

const ValidationAgentLigne = ({
  agent,
  editable = false,
}: {
  agent: Agent;
  editable: boolean;
}) => {
  const reinitialiser = () => {};

  const [administration, setAdministration] = useState<Administration>(
    agent.administration as Administration,
  );

  console.log(administration);
  const [roles, setRoles] = useState<string[]>(agent.roles);

  return (
    <div key={`ligne-agent-${agent.id}`}>
      <div className="fr-col-3 fr-grid-row fr-grid-row--gutters">
        <p>
          {agent.nomComplet()}
          <br />
          <span className="fr-text--sm">{agent.courriel}</span>
        </p>
      </div>
      <div className="fr-col-4 fr-grid-row fr-grid-row--gutters">
        {administration ? (
          <p>
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
              <span className="fr-text--sm">
                première connexion le{" "}
                {agent.datePremiereConnexion.toLocaleString("fr-FR", {
                  weekday: "long",
                  day: "numeric",
                  month: "long",
                  year: "numeric",
                  hour: "numeric",
                  minute: "numeric",
                })}
              </span>
            )}
          </p>
        ) : (
          <Select
            label="Rattachement :"
            className="fr-select"
            disabled={!editable}
            id={`selection-administration-${agent.id}`}
            nativeSelectProps={{
              onChange: (e: ChangeEvent<HTMLSelectElement>) => {
                if (e.target.value) {
                  setAdministration(
                    Administration.pourType(
                      e.target.value as TypeAdministration,
                    ),
                  );
                }
              },
            }}
          >
            <option value="" disabled hidden>
              Selectionnez une option
            </option>
            {Administration.liste().map((administration) => (
              <option value={administration.type} key={administration.type}>
                {administration.libelle}
              </option>
            ))}
          </Select>
        )}
      </div>
      <div className="fr-col-4 fr-grid-row fr-grid-row--right fr-grid-row--middle">
        {administration && (
          <fieldset
            className="fr-fieldset"
            aria-labelledby="storybook-form-legend storybook-form-messages"
          >
            <legend
              className="fr-fieldset__legend--regular fr-fieldset__legend"
              id="storybook-form-legend"
            >
              {" "}
              Permissions
            </legend>

            {administration?.roles.map((role: string) => (
              <div
                key={`permission-${agent.id}-${role.toLowerCase()}`}
                className="fr-fieldset__element fr-m-0"
              >
                <div className="fr-checkbox-group">
                  <input
                    id={`input-permission-${agent.id}-${role.toLowerCase()}`}
                    type="checkbox"
                    // TODO utiliser des classes pour les rôles et mieux gérer les impositions
                    disabled={
                      !editable ||
                      (role == "ROLE_AGENT_DOSSIER" &&
                        roles.some((r) =>
                          [
                            "ROLE_AGENT_REDACTEUR",
                            "ROLE_AGENT_ATTRIBUTEUR",
                            "ROLE_AGENT_VALIDATEUR",
                          ].includes(r),
                        ))
                    }
                    checked={roles.includes(role)}
                    onChange={(e) =>
                      setRoles((roles) =>
                        roles
                          .filter((r) => r != role)
                          .concat(e.target.checked ? [role] : []),
                      )
                    }
                    aria-describedby="checkbox-53-messages"
                  />
                  <label
                    className="fr-label"
                    htmlFor={`input-permission-${agent.id}-${role.toLowerCase()}`}
                  >
                    {role}
                    <span className="fr-hint-text">role (description)</span>
                  </label>
                </div>
              </div>
            ))}

            <div
              className="fr-messages-group"
              id="storybook-form-messages"
              aria-live="polite"
            ></div>
          </fieldset>
        )}
      </div>
      <div className="fr-col-1">
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

const DeclarationNouvelAgent = observer(
  ({ agent, onSaved }: { agent: Agent; onSaved: (agent: Agent) => void }) => {
    const [actif, setActif] = useState(false);
    const [erreur, setErreur] = useState(null);
    const save = async () => {
      try {
        const response = await fetch("/agent/gestion/nouvel-agent.json", {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            prenom: agent.prenom,
            nom: agent.nom,
            courriel: agent.courriel,
          }),
        });

        const data = await response.json();
        if (response.ok) {
          onSaved(plainToInstance(Agent, data));
          agent = new Agent();
          setActif(false);
          setErreur(null);
        } else {
          setErreur(data?.courriel ?? null);
        }
      } catch (e) {
        console.error(e);
      }
    };

    return (
      <>
        {!actif ? (
          <ul className="fr-btns-group fr-btns-group--inline-md fr-btns-group--center">
            <li>
              <button
                type="button"
                className="fr-btn fr-my-2w"
                onClick={() => setActif(true)}
              >
                Ajouter un agent
              </button>
            </li>
          </ul>
        ) : (
          <>
            <div className="fr-col-12">
              <fieldset
                className="fr-fieldset"
                id="storybook-form"
                aria-labelledby="storybook-form-legend"
              >
                <div className="fr-grid-row fr-col-12 fr-mb-2w">
                  <legend
                    className="fr-fieldset__legend--regular fr-fieldset__legend fr-col-12 fr-text--bold fr-text--lg"
                    id="storybook-form-legend"
                  >
                    Déclarer un nouvel agent
                  </legend>
                  <p className="fr-m-1w">
                    L'agent que vous créez disposera de toutes les permissions
                    que vous lui accordez ici dès sa première connexion.
                  </p>
                </div>
                <div className="fr-grid-row fr-col-12 fr-grid-row--gutters">
                  <div className="fr-col-4">
                    <div className="fr-fieldset__element">
                      <div className="fr-input-group" id="input-group-80">
                        <label className="fr-label" htmlFor="input-65">
                          Prénom
                          <span className="fr-hint-text">&zwnj;</span>
                        </label>
                        <input
                          className="fr-input"
                          placeholder="Red"
                          id="nouvel-agent-prenom"
                          defaultValue={""}
                          onInput={(e: FormEvent<HTMLInputElement>) =>
                            (agent.prenom = (
                              e.target as HTMLInputElement
                            ).value)
                          }
                          type="text"
                        />
                      </div>
                    </div>
                  </div>
                  <div className="fr-col-4">
                    <div className="fr-fieldset__element">
                      <div className="fr-input-group" id="input-group-81">
                        <label className="fr-label" htmlFor="input-66">
                          Nom<span className="fr-hint-text">&zwnj;</span>
                        </label>
                        <input
                          className="fr-input"
                          aria-describedby="input-66-messages"
                          defaultValue={""}
                          onInput={(e: FormEvent<HTMLInputElement>) =>
                            (agent.nom = (e.target as HTMLInputElement).value)
                          }
                          placeholder="Acteur"
                          id="input-66"
                          type="text"
                        />
                        <div
                          className="fr-messages-group"
                          id="input-66-messages"
                          aria-live="polite"
                        ></div>
                      </div>
                    </div>
                  </div>
                  <div className="fr-col-4">
                    <div className="fr-fieldset__element">
                      <div className="fr-input-group">
                        <label
                          className="fr-label"
                          htmlFor="declarer-nouvel-agent-champs-courriel"
                        >
                          Adresse courriel
                          <span className="fr-hint-text">Requis</span>
                        </label>
                        <input
                          id="declarer-nouvel-agent-champs-courriel"
                          className="fr-input"
                          aria-describedby="declarer-nouvel-agent-message-courriel"
                          placeholder="red.acteur@justice.gouv.fr"
                          defaultValue={""}
                          onInput={(e: FormEvent<HTMLInputElement>) => {
                            agent.courriel = (
                              e.target as HTMLInputElement
                            ).value;
                            setErreur(null);
                          }}
                          type="text"
                        />
                        {erreur && (
                          <div
                            className="fr-messages-group fr-message--error"
                            id="declarer-nouvel-agent-message-courriel"
                            aria-live="polite"
                          >
                            {erreur}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </fieldset>
            </div>
            <div className="fr-col-12">
              <ul className="fr-btns-group fr-btns-group--inline-md fr-btns-group--right">
                <li>
                  <button className="fr-btn fr-btn--tertiary-no-outline">
                    Annuler
                  </button>
                  <button
                    type="button"
                    className="fr-btn fr-btn--primary"
                    disabled={!agent.estCourrielValide()}
                    onClick={() => save()}
                  >
                    Déclarer cet agent
                  </button>
                </li>
              </ul>
            </div>
          </>
        )}
      </>
    );
  },
);

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
    queryFn: agentManager.agentsActifs,
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

      <div className="fr-grid-row fr-grid-row--right fr-my-1w fr-grid-row--middle">
        <button className="fr-btn fr-btn--sm" disabled={false}>
          Ajouter un agent
        </button>
      </div>

      <div className="fr-grid-row fr-grid-row--gutters fr-text--bold">
        <div className="fr-col-3">Nom & adresse courriel</div>
        <div className="fr-col-4">Administration</div>
        <div className="fr-col-4">Niveau d'accès</div>
        <div className="fr-col-1"></div>
      </div>

      <div className="fr-col-3">
        {agents.map((agent) => (
          <ValidationAgentLigne
            key={agent.courriel}
            agent={agent}
            editable={!sauvegardeEnCours}
          />
        ))}
      </div>
    </>
  );
};
