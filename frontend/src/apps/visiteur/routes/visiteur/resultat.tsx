import { ResultatEligibilite } from "@/apps/visiteur/components/ResultatEligibilite";
import { REQUERANT_URL, useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute, useLocation } from "@tanstack/react-router";
import * as React from "react";

function ResultatEligibiliteRoute() {
  const navigate = useVisiteurNavigate();
  const { state } = useLocation();
  const reponses = state?.reponses;

  React.useEffect(() => {
    if (!reponses) {
      navigate({ to: "/visiteur/test-eligibilite" });
    }
  }, [reponses, navigate]);

  if (!reponses) return null;

  return (
    <ResultatEligibilite
      reponses={reponses}
      onRecommencer={() => navigate({ to: "/visiteur/test-eligibilite" })}
      onDeposerDossier={() => {
        window.location.href = REQUERANT_URL;
      }}
      onPrecedent={() => navigate({ to: "/visiteur/" })}
    />
  );
}

export const Route = createFileRoute("/visiteur/resultat")({
  component: ResultatEligibiliteRoute,
});
