import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { CallOut } from "@codegouvfr/react-dsfr/CallOut";
import { Layout } from "@/apps/public/components/Layout";
import { usePublicNavigate } from "@/apps/public/routeur";
import { createFileRoute } from "@tanstack/react-router";
import React from "react";

function AccueilVisiteur() {
  const navigate = usePublicNavigate();
  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Déclarer un déni de justice"
        homeLinkProps={{ href: "/" }}
        segments={[]}
      />

      <h1>Déclarer un déni de justice</h1>

      <p className="fr-mb-3w">
       Ce service vous permet de déclarer une situation susceptible de constituer un déni de justice en raison
        d’un délai de traitement que vous estimez anormalement long dans le cadre d’une procédure judiciaire terminée, 
        afin de solliciter l’examen de votre droit à réparation des préjudices qui pourraient en résulter. 
      </p>
      <p>Ce dispositif ne permet ni de contester une décision de justice ni d’obtenir des informations sur l’état d’avancement d’une procédure en cours. </p>

      <CallOut
        iconId="fr-icon-information-line"
        title="Liste des documents demandés pour une déclaration de déni de justice"
        bodyAs="div"
      >
        <p>
          Avant de commencer votre démarche, assurez-vous de disposer de
          l'ensemble des pièces nécessaires. Une préparation complète de
          votre dossier facilitera son traitement et vous permettra de mener
          votre déclaration dans de bonnes conditions.
        </p>

        <p>
          Documents et informations à fournir pour une déclaration de déni
          de justice :
        </p>
        <ul>
          <li>Pièce d'identité en cours de validité</li>
          <li>
           Décision de justice ayant mis fin à la procédure (jugement, arrêt, ordonnance) 
          </li>
          <li>
            Acte introductif d’instance (requête, assignation, déclaration d’appel, citation, plainte avec constitution de partie civile) 
          </li>
          <li>
            Tout document permettant d’établir les principales étapes de la procédure (convocations, avis d’audience, notifications, significations, courriers de la juridiction)
          </li>
          <li>Justificatifs des démarches effectuées auprès de la juridiction ou du greffe (courriers, relances, accusés de réception, échanges électroniques)</li>
        </ul>
      </CallOut>

      <div className="fr-mt-3w fr-grid-row fr-grid-row--center">
        <Button
          size="large"
          onClick={() => navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite" })}
        >
          Tester mon éligibilité à l'indemnisation
        </Button>
      </div>
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/")({
  component: AccueilVisiteur,
});
