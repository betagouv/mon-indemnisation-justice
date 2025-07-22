import { createModal } from "@codegouvfr/react-dsfr/Modal";
import {
  Agent,
  DossierDetail,
  EtatDossier,
  EtatDossierType,
} from "@/apps/agent/dossiers/models";
import { observer } from "mobx-react-lite";
import React, { useCallback, useState } from "react";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { plainToInstance } from "class-transformer";

const _modale = createModal({
  id: "modale-action-emarquer-indemnise",
  isOpenedByDefault: false,
});

const estEnAttenteIndemnisation = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): boolean =>
  agent.estLiaisonBudget() &&
  dossier.etat.etat === EtatDossierType.OK_EN_ATTENTE_PAIEMENT;

const component = observer(function EnvoyerPourIndemnisationActionModale({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) {
  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const marquerIndemnise = useCallback(async () => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/marquer-indemnise.json`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
      },
    );

    if (response.ok) {
      const data = await response.json();
      dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
    }

    _modale.close();
    setSauvegarderEnCours(false);
  }, [dossier.id]);

  return (
    <_modale.Component
      size="large"
      title=" Marquer indemnisé"
      iconId="fr-icon-check-line"
    >
      <p>
        Ce dossier a été transmis au Bureau du Budget le{" "}
        {dossier.etat.dateEntree.toLocaleString("fr-FR", {
          weekday: "long",
          day: "numeric",
          month: "long",
          year: "numeric",
        })}
        .
      </p>

      <p>
        Si vous avez été notifié du versement de l'indemnité, vous pouvez
        marquer le dossier comme indemnisé.
      </p>

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttonsSize="small"
        buttons={[
          {
            children: sauvegardeEnCours ? "Sauvegarde en cours..." : "Annuler",
            priority: "tertiary no outline",
            onClick: () => {
              _modale.close();
            },
          },
          {
            children: "Marquer indemnisé",
            iconId: "fr-icon-check-line",
            priority: "primary",
            disabled: sauvegardeEnCours,
            onClick: async () => marquerIndemnise(),
          },
        ]}
      />
    </_modale.Component>
  );
});

export const marquerIndemniseBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estEnAttenteIndemnisation({ dossier, agent })
    ? [
        {
          children: "Marquer comme indemnisé",
          iconId: "fr-icon-check-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};

export { component as MarquerIndemniseActionModale };
