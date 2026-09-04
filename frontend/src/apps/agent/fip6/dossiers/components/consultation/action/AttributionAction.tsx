import { ButtonProps } from "@codegouvfr/react-dsfr/Button";

import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { DossierDetail, Redacteur } from "@common/models";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { DossierManagerInterface } from "@fip6/services/dossier.ts";
import { useInjection } from "inversify-react";
import { default as React, useCallback, useState } from "react";

const _modale = createModal({
  id: "modale-action-attribution",
  isOpenedByDefault: false,
});

const estAAttribuer = ({
  dossier,
  agent,
  redacteurs,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  redacteurs: Redacteur[];
}) =>
  dossier.estBrisDePorte() && // TODO supprimer ce test pour élargir aux autres dossiers
  agent.estAttributeur() &&
  dossier.estAAttribuer();

export const AttribuerActionModale = ({
  dossier,
  agent,
  redacteurs,
  onAttribue,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  redacteurs: Redacteur[];
  onAttribue: () => void | Promise<void>;
}) => {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Représente le rédacteur à attribuer, présentement en cours de sélection dans le menu déroulant
  const [attributaire, setAttributaire]: [
    Redacteur | undefined,
    (redacteur: Redacteur | undefined) => void,
  ] = useState(dossier.redacteur);

  // Indique si l'attribution du rédacteur est activée (= clic sur l'icône "crayon" à côté du rédacteur attribué, seulement octroyé aux agents attributeur)
  const [attributionEnCours, setAttributionEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegarderEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const valider = useCallback(async () => {
    if (!!attributaire && attributaire?.id != dossier.redacteur?.id) {
      setSauvegarderEnCours(true);

      await dossierManager.attribuer(dossier, attributaire);
      await onAttribue();

      setAttributaire(undefined);
      setAttributionEnCours(false);
    }
  }, [dossier, attributaire]);

  return estAAttribuer({ dossier, agent, redacteurs }) ? (
    <_modale.Component title="Attribuer le dossier">
      <>
        <div className="fr-select-group fr-col-12 fr-mb-0">
          <label className="fr-label" htmlFor="dossier-select-attributaire">
            Rédacteur :
          </label>
          <select
            className="fr-select"
            id="dossier-select-attributaire"
            disabled={sauvegarderEnCours}
            defaultValue={attributaire?.id || ""}
            onChange={(e) => {
              !!e.target.value &&
                setAttributaire(
                  redacteurs.find(
                    (redacteur) => redacteur.id === parseInt(e.target.value),
                  ),
                );
            }}
          >
            <option value="" disabled hidden>
              Sélectionnez un rédacteur
            </option>
            {redacteurs.map((redacteur: Redacteur) => (
              <option value={redacteur.id} key={redacteur.id}>
                {redacteur.nom}
              </option>
            ))}
          </select>
        </div>

        <ButtonsGroup
          className="fr-mt-3w"
          inlineLayoutWhen="always"
          alignment="right"
          buttons={[
            {
              priority: "tertiary no outline",
              disabled: sauvegarderEnCours,
              children: sauvegarderEnCours ? (
                <i>Sauvegarde en cours ...</i>
              ) : (
                <>Annuler</>
              ),
            },
            {
              disabled: sauvegarderEnCours || !attributaire,
              onClick: () => valider(),
              children: "Attribuer",
            },
          ]}
        />
      </>
    </_modale.Component>
  ) : (
    <></>
  );
};

export const attribuerBoutons = ({
  dossier,
  agent,
  redacteurs,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  redacteurs: Redacteur[];
}): ButtonProps[] => {
  return estAAttribuer({ dossier, agent, redacteurs })
    ? [
        {
          children: "Attribuer",
          iconId: "fr-icon-user-star-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};
