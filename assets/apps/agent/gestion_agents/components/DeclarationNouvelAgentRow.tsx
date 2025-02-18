import { Agent } from "@/apps/agent/gestion_agents/models";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";

export const DeclarationNouvelAgentRow = observer(
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
      <tr>
        <td colSpan="4">
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
                            onInput={(e) => (agent.prenom = e.target.value)}
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
                            onInput={(e) => (agent.nom = e.target.value)}
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
                        <div className="fr-input-group" id="input-group-82">
                          <label className="fr-label" htmlFor="input-67">
                            Adresse courriel
                            <span className="fr-hint-text">Requis</span>
                          </label>
                          <input
                            className="fr-input"
                            aria-describedby="input-67-messages"
                            placeholder="red.acteur@justice.gouv.fr"
                            defaultValue={""}
                            onInput={(e) => {
                              agent.courriel = e.target.value;
                              setErreur(null);
                            }}
                            id="input-67"
                            type="text"
                          />
                          {erreur && (
                            <div
                              className="fr-messages-group fr-message--error"
                              id="input-67-messages"
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
        </td>
      </tr>
    );
  },
);
