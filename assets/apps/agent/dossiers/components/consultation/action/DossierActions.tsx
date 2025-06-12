import {
  deciderBoutons,
  DeciderModale,
} from "@/apps/agent/dossiers/components/consultation/action/DecisionAction";
import {
  attribuerBoutons,
  AttribuerModale as AttribuerActionModale,
} from "./AttributionAction";
import {
  VerifierAcceptationModale as VerifierAcceptationActionModale,
  verifierAcceptationBoutons,
} from "./VerificationAcceptationAction";
import {
  CloturerModale as CloturerActionModale,
  cloturerBoutons,
} from "./CloturerAction";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, { useState } from "react";
import { Agent, DossierDetail } from "@/apps/agent/dossiers/models";

export const DossierActions = function DossierActionBar({
  dossier,
  agent,
  onDecide,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onDecide?: () => void;
}) {
  const [decision, setDecision]: [boolean, (boolean) => void] =
    useState<boolean>(null);

  const [sauvegardeEnCours, setSauvegardeEnCours]: [
    boolean,
    (boolean) => void,
  ] = useState<boolean>(false);

  return (
    <>
      {/** Actions sur le dossier */}

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttonsIconPosition="right"
        buttons={
          [
            ...cloturerBoutons({ dossier, agent }),
            ...attribuerBoutons({ dossier, agent }),
            ...deciderBoutons({
              dossier,
              agent,
              sauvegardeEnCours,
              setSauvegardeEnCours,
              setDecision,
            }),
            ...verifierAcceptationBoutons({ dossier, agent }),
          ] as [ButtonProps, ...ButtonProps[]]
        }
      />

      {/** Modales d'action sur le dossier */}
      <CloturerActionModale dossier={dossier} agent={agent} />
      <AttribuerActionModale dossier={dossier} agent={agent} />
      <DeciderModale
        dossier={dossier}
        agent={agent}
        decision={decision}
        onDecide={onDecide}
      />
      <VerifierAcceptationActionModale dossier={dossier} agent={agent} />
    </>
  );
};
