import { Agent } from "@/common/models";
import React from "react";

export const MonCompte = ({ agent }: { agent: Agent }) => {
  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h1>Mon compte</h1>

        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-input-group fr-col-12 fr-col-lg-4 fr-col-offset-1--right">
            <fieldset
              className="fr-fieldset"
              aria-labelledby="legende-mon-compte"
            >
              <legend
                className="fr-fieldset__legend--regular fr-fieldset__legend"
                id="legende-mon-compte"
              >
                <h5>À propos de moi</h5>
              </legend>
              <div className="fr-fieldset__element">
                <div className="fr-input-group">
                  <label className="fr-label" htmlFor="mon-prenom">
                    Prénom
                  </label>
                  <input
                    id="mon-prenom"
                    className="fr-input"
                    type="text"
                    disabled
                    value={agent.prenom}
                  />
                </div>
              </div>
              <div className="fr-fieldset__element">
                <div className="fr-input-group">
                  <label className="fr-label" htmlFor="mon-nom">
                    Nom
                  </label>
                  <input
                    id="mon-nom"
                    className="fr-input"
                    type="text"
                    disabled
                    value={agent.nom}
                  />
                </div>
              </div>
              <div className="fr-fieldset__element">
                <div className="fr-input-group">
                  <label className="fr-label" htmlFor="mon-courriel">
                    Nom
                  </label>
                  <input
                    id="mon-courriel"
                    className="fr-input"
                    type="email"
                    disabled
                    value={agent.courriel}
                  />
                </div>
              </div>
            </fieldset>
          </div>

          <div className="fr-input-group fr-col-12 fr-col-lg-6">
            <fieldset
              className="fr-fieldset"
              aria-labelledby="legende-mes-permissions"
            >
              <legend
                className="fr-fieldset__legend--regular fr-fieldset__legend"
                id="legende-mon-compte"
              >
                <h5>Mes accès</h5>
              </legend>
            </fieldset>

            <div>
              {agent.administration.type === "PN" && (
                <p>
                  Vous êtes actuellement rattaché à la <b>Police Nationale</b>.
                </p>
              )}
              {agent.administration.type === "PP" && (
                <p>
                  Vous êtes actuellement rattaché à la{" "}
                  <b>Préfecture de Police</b>.
                </p>
              )}
              {agent.administration.type === "GN" && (
                <p>
                  Vous êtes actuellement rattaché à la{" "}
                  <b>Gendarmerie Nationale</b>.
                </p>
              )}
              {agent.administration.type === "MI" && (
                <p>
                  Vous êtes actuellement rattaché au{" "}
                  <b>Ministère de l'Intérieur'</b>.
                </p>
              )}
              {agent.administration.type === "MJ" && (
                <p>
                  Vous êtes actuellement rattaché au{" "}
                  <b>Ministère de la Justice</b>.
                </p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
