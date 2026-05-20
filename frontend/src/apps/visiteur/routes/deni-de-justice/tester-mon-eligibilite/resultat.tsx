import { ResultatEligibilite } from "@/apps/visiteur/components/ResultatEligibilite";
import { REQUERANT_URL, useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute, redirect, useLocation } from "@tanstack/react-router";
import * as React from "react";

function ResultatEligibiliteRoute() {
  const navigate = useVisiteurNavigate();
  const { state } = useLocation();

  return (
    <ResultatEligibilite
      reponses={state.reponses!}
      onRecommencer={() => navigate({ to: "/deni-de-justice/tester-mon-eligibilite/test-eligibilite" })}
      onDeposerDossier={() => {
        window.location.href = REQUERANT_URL;
      }}
      onPrecedent={() => navigate({ to: "/deni-de-justice/tester-mon-eligibilite/" })}
    />
  );
}

export const Route = createFileRoute("/deni-de-justice/tester-mon-eligibilite/resultat")({
  component: ResultatEligibiliteRoute,
  beforeLoad: ({ location }) => {
    if (!location.state?.reponses) {
      throw redirect({ to: "/deni-de-justice/tester-mon-eligibilite/test-eligibilite" } as any);
    }
  },
});
