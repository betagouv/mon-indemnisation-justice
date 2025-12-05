import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Download from "@codegouvfr/react-dsfr/Download";
import React from "react";
import { createFileRoute } from "@tanstack/react-router";
import { fr } from "@codegouvfr/react-dsfr";

export const Route = createFileRoute("/agent/fdo/foire-aux-questions")({
  component: () => (
    <div>
      <h1 className="fr-my-2w">Foire aux questions</h1>

      <section id="question-quand-remettre-attestation">
        <h4>Dans quels cas remettre l'attestation ?</h4>
        <p>
          Certaines situations particulières peuvent donner lieu à une
          indemnisation, alors même qu’il n’y a pas d’erreur opérationnelle.
        </p>

        <ol>
          <li>
            <strong>Cas de violences conjugales</strong>
            <p className="fr-mt-2w">
              Lorsqu’une intervention est effectuée sur appel de la victime ou
              de ses voisins, l’attestation peut être remise à la victime.
            </p>
          </li>
          <li>
            <strong>
              Cas des interventions menées avec une brigade cynophile
            </strong>
            <p className="fr-mt-2w">
              Lorsqu’un chien spécialisé dans la recherche de produits
              stupéfiants effectue un marquage entraînant un bris de porte,
              l’attestation peut être remise si le marquage du chien était
              erroné (absence de produits stupéfiants, présence de rats morts
              …).
            </p>
          </li>
          <li>
            <strong>Cas de squat</strong>
            <p className="fr-mt-2w">
              L’attestation doit être remise au propriétaire lorsque ce-dernier
              avait engagé une procédure d’expulsion avant l’intervention, même
              si les squatteurs étaient les mis en cause.
            </p>
          </li>
          <li>
            <strong>
              Cas de dénonciation mensongère d’une urgence vitale avérée
            </strong>
            <p className="fr-mt-2w">
              Lorsque l’auteur de la dénonciation mensongère n’est pas
              identifié, l’attestation doit être remise à la personne dont le
              logement a été endommagé. En cas d’appel d’un voisin sur la base
              d’une erreur d’appréciation de la situation (mauvaise désignation
              du logement ou mauvaise perception de l’origine des cris),
              l’attestation doit être remise à la personne dont le logement a
              été endommagé, lorsque ce voisin était de bonne foi (sans
              intention de nuire). Ce cas est distinct de la dénonciation
              mensongère.
            </p>
          </li>
          <li>
            <strong>Cas d’usurpation d’identité ou d’homonymie</strong>
            <p className="fr-mt-2w">
              En cas d’homonymie, l’attestation doit être remise à la personne
              dont le logement a été endommagé. Lorsqu’un bris de porte résulte
              d’une usurpation d’identité, l’attestation doit être remise si
              l’auteur de l’usurpation est connu.
            </p>
          </li>
          <li>
            <strong>
              Cas des interventions dans des associations d’aide ou d’insertion
              sociale
            </strong>
            <p className="fr-mt-2w">
              L’attestation doit être remise au responsable de l’association
              (Croix-Rouge, Samu social, etc.), même si cette association
              hébergeait le mis en cause.
            </p>
          </li>
          <li>
            <strong>Cas de déménagement</strong>
            <p className="fr-mt-2w">
              En cas de déménagement du mis en cause, l’attestation doit être
              remise au nouvel occupant du logement (locataire ou propriétaire).
            </p>
          </li>
          <li>
            <strong>Cas de flagrance Lorsque l’auteur</strong>
            <p className="fr-mt-2w">
              d’une infraction, poursuivi par les forces de l’ordre, se réfugie
              chez une personne tierce à l’opération de police judiciaire,
              l’attestation doit être remise à cette dernière.
            </p>
          </li>
        </ol>
      </section>

      <section id="question-comment-requisitionner-serrurier">
        <h4>Comment réquisitionner un serrurier lors d’une intervention ?</h4>
        <p className="fr-my-1w">
          Afin de faciliter les interventions et d’assurer la transparence du
          dispositif,{" "}
          <span className="fr-text--bold">Mon Indemnisation Justice</span>{" "}
          intègre désormais une fonctionnalité dédiée aux{" "}
          <span className="fr-text--bold">Forces de l’ordre</span> pour la
          <span className="fr-text--bold">
            réquisition d’un serrurier labellisé
          </span>
          .
        </p>
        <p className="fr-my-1w">
          Cette évolution vise également à améliorer la qualité du service rendu
          aux usagers en collaborant avec des serruriers sensibilisés aux bonnes
          pratiques, membres du Label « Serruriers de France ».
        </p>

        <p className="fr-my-1w">
          Vous pouvez trouver un serrurier labellisé depuis la plateforme Mon
          Indemnisation Justice en vous connectant à votre espace.
        </p>
        <p className="fr-my-1w">Étapes :</p>
        <ol className="r-list">
          <li>
            Connectez-vous à l’espace Forces de l’ordre sur Mon Indemnisation
            Justice.
          </li>
          <li>Cliquez sur « Réquisitionner un serrurier ».</li>
          <li>Saisissez l’adresse ou le code postal du lieu d’intervention.</li>
          <li>
            Une liste de serruriers labellisés apparaîtra avec leurs
            coordonnées.
          </li>
        </ol>

        <p className="fr-text--bold fr-my-1w">
          N’hésitez pas à nous remonter toute difficulté rencontrée.
        </p>
      </section>
    </div>
  ),
});
