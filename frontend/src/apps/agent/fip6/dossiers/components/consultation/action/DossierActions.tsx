import {
  deciderIndemnisationBoutons,
  DeciderIndemnisationModale,
} from "@/apps/agent/fip6/dossiers/components/consultation/action/DeciderIndemnisationAction.tsx";
import {
  deciderRejetBoutons,
  DeciderRejetModale,
} from "@/apps/agent/fip6/dossiers/components/consultation/action/DeciderRejetAction.tsx";
import {
  attribuerBoutons,
  AttribuerModale as AttribuerActionModale,
} from "./AttributionAction.tsx";
import {
  signerArretePaiementBoutons,
  SignerArretePaiementModale,
} from "./SignerArretePaiementAction.tsx";
import {
  cloturerBoutons,
  CloturerModale as CloturerActionModale,
} from "./CloturerAction.tsx";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React from "react";
import { Agent, DossierDetail } from "@/common/models";
import {
  signerCourrierBoutons,
  SignerCourrierModale,
} from "@/apps/agent/fip6/dossiers/components/consultation/action/SignerCourrierAction.tsx";
import {
  genererArretePaiementBoutons,
  GenererArretePaiementModale,
} from "@/apps/agent/fip6/dossiers/components/consultation/action/GenererArretePaiementAction.tsx";
import {
  EnvoyerPourIndemnisationActionModale,
  envoyerPourIndemnisationBoutons,
} from "@/apps/agent/fip6/dossiers/components/consultation/action/EnvoyerPourIndemnisationActionModale.tsx";
import {
  MarquerIndemniseActionModale,
  marquerIndemniseBoutons,
} from "@/apps/agent/fip6/dossiers/components/consultation/action/MarquerIndemniseActionModale.tsx";
import { demarrerInstructionBoutons } from "@/apps/agent/fip6/dossiers/components/consultation/action/DemarrerInstructionAction.tsx";

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
        buttonsSize="medium"
        buttonsIconPosition="right"
        buttons={
          [
            ...cloturerBoutons({ dossier, agent }),
            ...attribuerBoutons({ dossier, agent }),
            ...demarrerInstructionBoutons({ dossier, agent }),
            ...deciderRejetBoutons({
              dossier,
              agent,
            }),
            ...deciderIndemnisationBoutons({
              dossier,
              agent,
            }),
            ...signerCourrierBoutons({ dossier, agent }),
            ...genererArretePaiementBoutons({ dossier, agent }),
            ...signerArretePaiementBoutons({ dossier, agent }),
            ...envoyerPourIndemnisationBoutons({ dossier, agent }),
            ...marquerIndemniseBoutons({ dossier, agent }),
          ] as [ButtonProps, ...ButtonProps[]]
        }
      />

      {/** Modales d'action sur le dossier */}
      <CloturerActionModale dossier={dossier} agent={agent} />
      <AttribuerActionModale dossier={dossier} agent={agent} />
      <DeciderRejetModale dossier={dossier} agent={agent} onDecide={onDecide} />
      <DeciderIndemnisationModale
        key={dossier.id}
        dossier={dossier}
        agent={agent}
        onDecide={onDecide}
      />
      <SignerCourrierModale dossier={dossier} agent={agent} onSigne={onSigne} />
      <GenererArretePaiementModale dossier={dossier} agent={agent} />
      <SignerArretePaiementModale dossier={dossier} agent={agent} />
      <EnvoyerPourIndemnisationActionModale dossier={dossier} agent={agent} />
      <MarquerIndemniseActionModale dossier={dossier} agent={agent} />
    </>
  );
};
