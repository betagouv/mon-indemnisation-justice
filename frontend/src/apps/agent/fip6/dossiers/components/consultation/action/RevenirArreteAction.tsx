import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { DossierDetail, EtatDossierType } from "@common/models";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { DossierManagerInterface } from "@fip6/services/dossier.ts";
import { useInjection } from "inversify-react";
import React, { useCallback, useState } from "react";

const peutRevenirALArrete = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): boolean =>
  [
    EtatDossierType.OK_VERIFIE,
    EtatDossierType.OK_A_INDEMNISER,
    EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
  ].includes(dossier.etat.etat) && agent.instruit(dossier);

const _modale = createModal({
  id: "modale-action-revenir-arrete",
  isOpenedByDefault: false,
});
export const RevenirArreteModale = ({
  dossier,
  agent,
  onRevenu,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  onRevenu: () => void | Promise<void>;
}) => {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Indique si la sauvegarde de la décision est en cours
  const [sauvegardeEnCours, setSauvegardeEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState<boolean>(false);

  const revenirALArrete = useCallback(async () => {
    setSauvegardeEnCours(true);

    await dossierManager.revenirArrete(dossier);
    onRevenu();
    setSauvegardeEnCours(false);
  }, [dossier.id]);

  return peutRevenirALArrete({ dossier, agent }) ? (
    <_modale.Component
      title=" Faire revenir le dossier à l'édition de l'arrêté"
      iconId="fr-icon-arrow-go-back-line"
    >
      <p>
        Faire revenir le dossier{" "}
        <pre style={{ display: "inline" }}>{dossier.reference}</pre>, concernant{" "}
        <b>{dossier.requerant.nomComplet()}</b>, à la phase d'édition de
        l'arrêté de paiement ?
      </p>

      <ButtonsGroup
        className="fr-mt-3w"
        alignment="right"
        inlineLayoutWhen="always"
        buttonsIconPosition="right"
        buttonsSize="small"
        buttons={[
          {
            priority: "tertiary no outline",
            children: sauvegardeEnCours ? (
              <i>Sauvegarde en cours ...</i>
            ) : (
              "Annuler"
            ),
            onClick: () => _modale.close(),
            disabled: sauvegardeEnCours,
          },
          {
            children: "Revenir à l'arrêté",
            priority: "primary",
            iconId: "fr-icon-arrow-go-back-line",
            disabled: sauvegardeEnCours,
            onClick: () => revenirALArrete(),
          },
        ]}
      />
    </_modale.Component>
  ) : (
    <></>
  );
};

export const revenirArreteBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): ButtonProps[] => {
  return peutRevenirALArrete({ dossier, agent })
    ? [
        {
          children: "Revenir à l'arrêté",
          priority: "secondary",
          iconId: "fr-icon-arrow-go-back-line",
          onClick: () => _modale.open(),
        },
      ]
    : [];
};
