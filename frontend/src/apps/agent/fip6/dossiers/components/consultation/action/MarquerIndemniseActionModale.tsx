import { DossierManagerInterface } from "@/apps/agent/fip6/services/dossier";
import { Agent, DossierDetail, EtatDossierType } from "@/common/models";
import { dateChiffre, dateSimple } from "@/common/services/date.ts";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useInjection } from "inversify-react";
import { observer } from "mobx-react-lite";
import React, { useCallback, useState } from "react";

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
  agent.estLiaisonBudget() ||
  (agent.instruit(dossier) &&
    dossier.etat.etat === EtatDossierType.OK_EN_ATTENTE_PAIEMENT);

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

  const [dateIndemnisation, setDateIndemnisation] = useState<Date>(
    dossier.etat.dateEntree,
  );

  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const marquerIndemnise = useCallback(async () => {
    setSauvegarderEnCours(true);
    await dossierManager.marquerIndemnise(dossier, dateIndemnisation);
    await onTermine();

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
        {dateSimple(dossier.etat.dateEntree, {
          masquerAnneeSiCourante: true,
          jourDeLaSemaine: true,
        })}
        .
      </p>

      <p>
        Si vous avez été notifié du versement de l'indemnité, vous pouvez
        marquer le dossier comme indemnisé.
      </p>

      <Input
        label="Date du virement"
        nativeInputProps={{
          type: "date",
          defaultValue: dateChiffre(dateIndemnisation),
          onChange: (e) => setDateIndemnisation(new Date(e.target.value)),
          min: dateChiffre(dossier.etat.dateEntree),
          max: dateChiffre(new Date()),
        }}
      />

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
            disabled:
              sauvegardeEnCours ||
              dateIndemnisation < dossier.etat.dateEntree ||
              dateIndemnisation > new Date(),
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
