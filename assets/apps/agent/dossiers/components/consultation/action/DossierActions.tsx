import {
  deciderBoutons,
  DeciderModale,
} from "@/apps/agent/dossiers/components/consultation/action/DecisionAction";
import {
  attribuerBoutons,
  AttribuerModale as AttribuerActionModale,
} from "./AttributionAction";
import {
  verifierAcceptationBoutons,
  VerifierAcceptationModale as VerifierAcceptationActionModale,
} from "./VerificationAcceptationAction";
import {
  cloturerBoutons,
  CloturerModale as CloturerActionModale,
} from "./CloturerAction";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React from "react";
import { Agent, DossierDetail } from "@/apps/agent/dossiers/models";
import {
  confirmerBoutons,
  ConfirmerModale,
} from "@/apps/agent/dossiers/components/consultation/action/ConfirmationAction.tsx";
import {
  genererArretePaiementBoutons,
  GenererArretePaiementModale,
} from "@/apps/agent/dossiers/components/consultation/action/GenererArretePaiementAction.tsx";
import {
  EnvoyerPourIndemnisationActionModale,
  envoyerPourIndemnisationBoutons,
} from "@/apps/agent/dossiers/components/consultation/action/EnvoyerPourIndemnisationActionModale.tsx";
import {
  MarquerIndemniseActionModale,
  marquerIndemniseBoutons,
} from "@/apps/agent/dossiers/components/consultation/action/MarquerIndemniseActionModale.tsx";

export const DossierActions = function DossierActionBar({
  dossier,
  agent,
  onDecide,
  onEdite,
  onSigne,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onDecide?: () => void;
  onEdite?: () => void;
  onSigne?: () => void;
}) {
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
            }),
            ...confirmerBoutons({ dossier, agent }),
            ...verifierAcceptationBoutons({ dossier, agent }),
            ...genererArretePaiementBoutons({ dossier, agent }),
            ...envoyerPourIndemnisationBoutons({ dossier, agent }),
            ...marquerIndemniseBoutons({ dossier, agent }),
          ] as [ButtonProps, ...ButtonProps[]]
        }
      />

      {/** Modales d'action sur le dossier */}
      <CloturerActionModale dossier={dossier} agent={agent} />
      <AttribuerActionModale dossier={dossier} agent={agent} />
      <DeciderModale dossier={dossier} agent={agent} onDecide={onDecide} />
      <ConfirmerModale
        dossier={dossier}
        agent={agent}
        onEdite={onEdite}
        onSigne={onSigne}
      />
      <VerifierAcceptationActionModale dossier={dossier} agent={agent} />
      <GenererArretePaiementModale dossier={dossier} agent={agent} />
      <EnvoyerPourIndemnisationActionModale dossier={dossier} agent={agent} />
      <MarquerIndemniseActionModale dossier={dossier} agent={agent} />
    </>
  );
};
