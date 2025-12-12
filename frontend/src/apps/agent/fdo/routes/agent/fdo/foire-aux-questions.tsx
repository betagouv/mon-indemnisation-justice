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
        <p>
          Certaines situations particulières peuvent donner lieu à une
          indemnisation, alors même qu’il n’y a pas d’erreur opérationnelle.
        </p>

        <Accordion label="Que faire en cas de violences conjugales ?">
          <p className="fr-mt-2w">
            Lorsqu’une intervention est effectuée sur appel de la victime ou de
            ses voisins, l’attestation peut être remise à la victime.
          </p>
        </Accordion>

        <Accordion label="Que faire en cas d'intervention menée avec une brigade cynophile ?">
          <p>
            Lorsqu’un chien spécialisé dans la recherche de produits stupéfiants
            effectue un marquage entraînant un bris de porte, l’attestation peut
            être remise si le marquage du chien était erroné (absence de
            produits stupéfiants, présence de rats morts …).
          </p>
        </Accordion>

        <Accordion label="Que faire en cas de squat ?">
          <p className="fr-mt-2w">
            L’attestation doit être remise au propriétaire lorsque ce-dernier
            avait engagé une procédure d’expulsion avant l’intervention, même si
            les squatteurs étaient les mis en cause.
          </p>
        </Accordion>
        <Accordion label="Que faire en cas de dénonciation mensongère d’une urgence vitale avérée ?">
          <p className="fr-mt-2w">
            Lorsque l’auteur de la dénonciation mensongère n’est pas identifié,
            l’attestation doit être remise à la personne dont le logement a été
            endommagé. En cas d’appel d’un voisin sur la base d’une erreur
            d’appréciation de la situation (mauvaise désignation du logement ou
            mauvaise perception de l’origine des cris), l’attestation doit être
            remise à la personne dont le logement a été endommagé, lorsque ce
            voisin était de bonne foi (sans intention de nuire). Ce cas est
            distinct de la dénonciation mensongère.
          </p>
        </Accordion>

        <Accordion label="Que faire en cas d’usurpation d’identité ou d’homonymie ?">
          <p className="fr-mt-2w">
            En cas d’homonymie, l’attestation doit être remise à la personne
            dont le logement a été endommagé. Lorsqu’un bris de porte résulte
            d’une usurpation d’identité, l’attestation doit être remise si
            l’auteur de l’usurpation est connu.
          </p>
        </Accordion>
        <Accordion label="Que faire en cas d'intervention dans des associations d’aide ou d’insertion sociale ?">
          <p className="fr-mt-2w">
            L’attestation doit être remise au responsable de l’association
            (Croix-Rouge, Samu social, etc.), même si cette association
            hébergeait le mis en cause.
          </p>
        </Accordion>
        <Accordion label="Que faire en cas de déménagement ?">
          <p className="fr-mt-2w">
            En cas de déménagement du mis en cause, l’attestation doit être
            remise au nouvel occupant du logement (locataire ou propriétaire).
          </p>
        </Accordion>
        <Accordion label="Que faire en cas de flagrance Lorsque l’auteur ?">
          <p className="fr-mt-2w">
            d’une infraction, poursuivi par les forces de l’ordre, se réfugie
            chez une personne tierce à l’opération de police judiciaire,
            l’attestation doit être remise à cette dernière.
          </p>
        </Accordion>
      </section>
    </div>
  ),
});
