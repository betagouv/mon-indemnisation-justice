import React, { useState } from "react";
import { DeclarerDeniDeJustice } from "./components/DeclarerDeniDeJustice";
import { TestEligibilite } from "./components/TestEligibilite";
import { EtapesEligibilite, type ReponsesEligibilite } from "./components/EtapesEligibilite";
import { ResultatEligibilite } from "./components/ResultatEligibilite";

type Page = "landing" | "test-eligibilite" | "etapes-eligibilite" | "resultat";

export const DeniDeJusticeApp = () => {
  const [page, setPage] = useState<Page>("landing");
  const [reponses, setReponses] = useState<ReponsesEligibilite | null>(null);

  if (page === "test-eligibilite") {
    return (
      <TestEligibilite
        onPrecedent={() => setPage("landing")}
        onCommencer={() => setPage("etapes-eligibilite")}
      />
    );
  }

  if (page === "etapes-eligibilite") {
    return (
      <EtapesEligibilite
        onPrecedent={() => setPage("test-eligibilite")}
        onTerminer={(rep) => {
          setReponses(rep);
          setPage("resultat");
        }}
      />
    );
  }

  if (page === "resultat" && reponses) {
    return (
      <ResultatEligibilite
        reponses={reponses}
        onRecommencer={() => {
          setReponses(null);
          setPage("test-eligibilite");
        }}
        onDeposerDossier={() => {
          window.location.href = "/requerant/";
        }}
        onPrecedent={() => setPage("landing")}
      />
    );
  }

  return <DeclarerDeniDeJustice onNext={() => setPage("test-eligibilite")} />;
};
