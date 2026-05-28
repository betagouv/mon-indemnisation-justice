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
        Ce service vous permet de signaler une situation de déni de justice
        afin de faire valoir vos droits et, le cas échéant, engager des
        démarches pour obtenir qu'une juridiction statue dans un délai
        raisonnable ou sur le fond de l'affaire.
      </p>

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
            Coordonnées complètes (adresse postale, téléphone, adresse
            électronique)
          </li>
          <li>
            Description détaillée de la situation, exposant les faits et
            les démarches engagées
          </li>
          <li>
            Chronologie précise de la procédure (dates de saisine,
            audiences, relances, etc.)
          </li>
        </ul>

        <p>Éléments relatifs à la procédure :</p>
        <ul>
          <li>
            Copie de la requête, assignation ou déclaration introductive
            d'instance
          </li>
          <li>Copie des conclusions et mémoires déposés</li>
          <li>
            Copie des actes de procédure (convocations, notifications,
            significations)
          </li>
          <li>
            Copie des décisions de justice déjà rendues, le cas échéant
            (jugement, ordonnance, arrêt)
          </li>
        </ul>

        <p>Justificatifs des démarches effectuées :</p>
        <ul>
          <li>Copies des courriers adressés à la juridiction</li>
          <li>Copies des relances (courriers, emails)</li>
          <li>Accusés de réception ou preuves de dépôt</li>
          <li>Tout échange avec le greffe ou les services judiciaires</li>
        </ul>

        <p>Éléments permettant d'établir le déni de justice :</p>
        <ul>
          <li>
            Documents attestant de l'absence de réponse de la juridiction
          </li>
          <li>
            Éléments démontrant un délai anormalement long de traitement
          </li>
          <li>
            Toute pièce utile illustrant une impossibilité d'obtenir une
            décision
          </li>
        </ul>

        <p>Le cas échéant :</p>
        <ul>
          <li>
            Mandat ou pouvoir si vous agissez pour le compte d'un tiers
          </li>
          <li>
            Coordonnées et qualité de votre représentant (avocat,
            mandataire)
          </li>
          <li>
            Tout document complémentaire que vous jugez utile à l'examen
            de votre situation
          </li>
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
