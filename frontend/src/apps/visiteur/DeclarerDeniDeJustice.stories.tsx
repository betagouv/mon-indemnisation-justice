import React from "react";
import type { Meta, StoryObj } from "@storybook/react";

import { DeniDeJusticeApp } from "./DeniDeJusticeApp";
import { DeclarerDeniDeJustice } from "./components/DeclarerDeniDeJustice";
import { TestEligibilite } from "./components/TestEligibilite";
import { EtapesEligibilite } from "./components/EtapesEligibilite";
import { ResultatEligibilite } from "./components/ResultatEligibilite";

const meta: Meta = {
  title: "Pages/Déni de Justice",
  parameters: {
    layout: "fullscreen",
  },
};

export default meta;

export const FluxComplet: StoryObj<typeof DeniDeJusticeApp> = {
  name: "Flux complet (navigation réelle)",
  render: () => <DeniDeJusticeApp />,
};

export const Landing: StoryObj<typeof DeclarerDeniDeJustice> = {
  name: "1 — Déclarer un déni de justice",
  render: () => <DeclarerDeniDeJustice />,
};

export const GatewayEligibilite: StoryObj<typeof TestEligibilite> = {
  name: "2 — Test d'éligibilité (passerelle)",
  render: () => <TestEligibilite onPrecedent={() => {}} onCommencer={() => {}} />,
};

export const EtapesEligibiliteStory: StoryObj<typeof EtapesEligibilite> = {
  name: "3 — Étapes du test (5 étapes)",
  render: () => <EtapesEligibilite onPrecedent={() => {}} onTerminer={() => {}} />,
};

export const ResultatEligible: StoryObj<typeof ResultatEligibilite> = {
  name: "4a — Résultat éligible",
  render: () => (
    <ResultatEligibilite
      reponses={{
        dansLesDelais: "oui",
        decisionsJustice: ["toutes_decisions"],
        possedeToutesDecisions: "oui",
        actionContentieuse: "non",
        piecesProc: ["assignation", "ecritures"],
        possedeDocumentsMiseEnEtat: "oui",
        preuvesDiligences: "oui",
      }}
      onRecommencer={() => {}}
      onDeposerDossier={() => {}}
      onPrecedent={() => {}}
    />
  ),
};

export const ResultatNonEligible: StoryObj<typeof ResultatEligibilite> = {
  name: "4b — Résultat non éligible",
  render: () => (
    <ResultatEligibilite
      reponses={{
        dansLesDelais: "non",
        decisionsJustice: [],
        possedeToutesDecisions: "non",
        actionContentieuse: "oui",
        piecesProc: [],
        possedeDocumentsMiseEnEtat: "non",
        preuvesDiligences: "non",
      }}
      onRecommencer={() => {}}
      onDeposerDossier={() => {}}
      onPrecedent={() => {}}
    />
  ),
};
