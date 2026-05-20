import { EtapesEligibilite } from "@/apps/visiteur/components/EtapesEligibilite";
import { useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute } from "@tanstack/react-router";
import * as React from "react";

function EtapesEligibiliteRoute() {
  const navigate = useVisiteurNavigate();
  return (
    <EtapesEligibilite
      onPrecedent={() => navigate({ to: "/deni-de-justice/tester-mon-eligibilite/test-eligibilite" })}
      onTerminer={(reponses) =>
        navigate({ to: "/deni-de-justice/tester-mon-eligibilite/resultat", state: { reponses } })
      }
    />
  );
}

export const Route = createFileRoute("/deni-de-justice/tester-mon-eligibilite/etapes-eligibilite")({
  component: EtapesEligibiliteRoute,
});
