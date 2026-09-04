import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document, DossierDetail, Redacteur } from "@common/models";
import {
  deciderIndemnisationBoutons,
  DeciderIndemnisationModale,
} from "@fip6/dossiers/components/consultation/action/DeciderIndemnisationAction.tsx";
import {
  deciderRejetBoutons,
  DeciderRejetModale,
} from "@fip6/dossiers/components/consultation/action/DeciderRejetAction.tsx";
import { demarrerInstructionBoutons } from "@fip6/dossiers/components/consultation/action/DemarrerInstructionAction.tsx";
import {
  EnvoyerPourIndemnisationActionModale,
  envoyerPourIndemnisationBoutons,
} from "@fip6/dossiers/components/consultation/action/EnvoyerPourIndemnisationActionModale.tsx";
import {
  genererArretePaiementBoutons,
  GenererArretePaiementModale,
} from "@fip6/dossiers/components/consultation/action/GenererArretePaiementAction.tsx";
import {
  MarquerIndemniseActionModale,
  marquerIndemniseBoutons,
} from "@fip6/dossiers/components/consultation/action/MarquerIndemniseActionModale.tsx";
import {
  signerCourrierBoutons,
  SignerCourrierModale,
} from "@fip6/dossiers/components/consultation/action/SignerCourrierAction.tsx";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import React from "react";

import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import {
  AttribuerActionModale,
  attribuerBoutons,
} from "./AttributionAction.tsx";
import {
  cloturerBoutons,
  CloturerModale as CloturerActionModale,
} from "./CloturerAction.tsx";
import {
  signerArretePaiementBoutons,
  SignerArretePaiementModale,
} from "./SignerArretePaiementAction.tsx";

export const DossierActions = function DossierActionBar({
  dossier,
  agent,
  redacteurs,
  onImprime,
  onDecide,
  onSigne,
  onTermine,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
  redacteurs: Redacteur[];
  onImprime: (document: Document) => void | Promise<void>;
  onDecide?: () => void;
  onSigne?: () => void;
  onTermine: () => void | Promise<void>;
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
            ...attribuerBoutons({ dossier, agent, redacteurs }),
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
      <AttribuerActionModale
        dossier={dossier}
        agent={agent}
        redacteurs={redacteurs}
        onAttribue={onTermine}
      />
      <DeciderRejetModale
        dossier={dossier}
        agent={agent}
        onDecide={onDecide}
        onImprime={onImprime}
      />
      <DeciderIndemnisationModale
        key={dossier.id}
        dossier={dossier}
        agent={agent}
        onDecide={onDecide}
        onImprime={onImprime}
      />
      <SignerCourrierModale
        dossier={dossier}
        agent={agent}
        onSigne={onSigne}
        onImprime={onImprime}
      />
      <GenererArretePaiementModale
        dossier={dossier}
        agent={agent}
        onImprime={onImprime}
      />
      <SignerArretePaiementModale
        dossier={dossier}
        agent={agent}
        onImprime={onImprime}
      />
      <EnvoyerPourIndemnisationActionModale
        dossier={dossier}
        agent={agent}
        onTermine={onTermine}
      />
      <MarquerIndemniseActionModale
        dossier={dossier}
        agent={agent}
        onTermine={onTermine}
      />
    </>
  );
};
