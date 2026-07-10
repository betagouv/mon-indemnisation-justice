import { DossierManagerInterface } from "@/apps/agent/fip6/services/dossier";
import { Agent, DossierDetail, EtatDossierType } from "@/common/models";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Download from "@codegouvfr/react-dsfr/Download";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { ToggleSwitch } from "@codegouvfr/react-dsfr/ToggleSwitch";
import { useInjection } from "inversify-react";
import { observer } from "mobx-react-lite";
import React, { useCallback, useState } from "react";

const _modale = createModal({
  id: "modale-action-envoyer-pour-indemnisation",
  isOpenedByDefault: false,
});

const estAEnvoyerPourIndemnisation = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): boolean =>
  (agent.instruit(dossier) || agent.estLiaisonBudget()) &&
  dossier.etat.etat === EtatDossierType.OK_A_INDEMNISER;

const component = observer(function EnvoyerPourIndemnisationActionModale({
  dossier,
  agent,
  onTermine,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onTermine: () => void | Promise<void>;
}) {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Indique que l'agent a déclaré avoir transmis les fichiers au Bureau du Budget
  const [elementsTransmis, setElementsTransmis]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const envoyerPourIndemnisation = useCallback(async () => {
    setSauvegarderEnCours(true);

    await dossierManager.transmettreAFIP3(dossier);

    await onTermine();
    _modale.close();
    setSauvegarderEnCours(false);
  }, [dossier.id]);

  return (
    <_modale.Component
      size="large"
      title=" Envoyer pour indemnisation"
      iconId="fr-icon-send-plane-line"
    >
      <p>
        Télécharger l'ensemble des documents requis pour procéder à
        l'indemnisation et transmettez-les au Bureau du Budget pour marquer le
        dossier en attente de paiement.
      </p>

      <Download
        label="Télécharger les documents à transmettre"
        details="Arrêté de paiement, RIB, pièce d'identité"
        linkProps={{
          href: `${document.location.origin}/agent/document/dossier/${dossier.id}/documents-a-transmettre`,
          target: "_self",
          download: true,
        }}
      />

      <ToggleSwitch
        label="Les fichiers sont transmis au bureau du budget"
        checked={elementsTransmis}
        onChange={(checked) => setElementsTransmis(checked)}
      />

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttonsSize="small"
        buttons={[
          {
            children: "Annuler",
            priority: "tertiary no outline",
            onClick: () => {
              _modale.close();
              setElementsTransmis(false);
            },
          },
          {
            children: "Confirmer la transmission",
            iconId: "fr-icon-send-plane-line",
            priority: "primary",
            disabled: sauvegardeEnCours || !elementsTransmis,
            onClick: async () => envoyerPourIndemnisation(),
          },
        ]}
      />
    </_modale.Component>
  );
});

export const envoyerPourIndemnisationBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return estAEnvoyerPourIndemnisation({ dossier, agent })
    ? [
        {
          children: "Envoyer pour indemnisation",
          iconId: "fr-icon-send-plane-line",
          onClick: () => _modale.open(),
        } as ButtonProps,
      ]
    : [];
};

export { component as EnvoyerPourIndemnisationActionModale };
