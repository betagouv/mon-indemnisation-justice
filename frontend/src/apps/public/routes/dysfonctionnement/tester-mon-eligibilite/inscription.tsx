import { Authentification } from "@/apps/public/components/authentification/Authentification";
import { Layout } from "@/apps/public/components/Layout";
import { getCriteres } from "@/apps/public/services/eligibiliteStore";
import {
  clearEtatAuthentification,
  getEtatAuthentification,
  sauvegarderEtatAuthentification,
} from "@/apps/public/services/authentificationStore";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { createFileRoute, redirect } from "@tanstack/react-router";
import React from "react";

function InscriptionRoute() {
  const { etat } = Route.useLoaderData();

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Inscription ou connexion"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un déni de justice",
            linkProps: { to: "/dysfonctionnement/tester-mon-eligibilite/" },
          },
        ]}
      />

      <h1>Inscription ou connexion</h1>

      <Authentification
        etatInitial={etat}
        onEtatChange={(nouvelEtat) => sauvegarderEtatAuthentification(nouvelEtat)}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/inscription")({
  component: InscriptionRoute,
  beforeLoad: () => {
    // Le test d'éligibilité est un pré-requis pour s'inscrire : pas de résultat en session, retour au test.
    if (getCriteres().length === 0) {
      clearEtatAuthentification();
      throw redirect({ to: "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite", replace: true });
    }
  },
  loader: () => ({
    etat: getEtatAuthentification(),
  }),
});
