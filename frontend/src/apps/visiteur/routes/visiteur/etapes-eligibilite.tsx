import { EtapesEligibilite } from "@/apps/visiteur/components/EtapesEligibilite";
import { useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute } from "@tanstack/react-router";
import * as React from "react";

function EtapesEligibiliteRoute() {
  const navigate = useVisiteurNavigate();
  return (
    <EtapesEligibilite
      onPrecedent={() => navigate({ to: "/visiteur/test-eligibilite" })}
      onTerminer={(reponses) => navigate({ to: "/visiteur/resultat", state: { reponses } })}
    />
  );
}

export const Route = createFileRoute("/visiteur/etapes-eligibilite")({
  component: EtapesEligibiliteRoute,
});
