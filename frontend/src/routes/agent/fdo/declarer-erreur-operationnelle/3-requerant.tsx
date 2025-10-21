import { createFileRoute } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import placeholder from "lodash/fp/placeholder";

export const Route = createFileRoute(
  "/agent/fdo/declarer-erreur-operationnelle/3-requerant",
)({
  component: Page,
});

function Page() {
  return (
    <div>
      <h1>Nouvelle déclaration d'erreur opérationnelle</h1>

      <Stepper
        currentStep={3}
        stepCount={3}
        title="Informations du requérant"
      />

      <div
        className="fr-grid-row fr-grid-row--gutters fr-mt-2w"
        style={{ alignItems: "center" }}
      >
        <Input
          className="fr-col-lg-4"
          label="Nom"
          nativeInputProps={{ type: "text", placeholder: "DUPONT" }}
        />

        <Input
          className="fr-col-lg-4"
          label="Prénom"
          nativeInputProps={{ type: "text", placeholder: "Martin" }}
        />

        <Input
          className="fr-col-lg-4"
          label="Téléphone"
          nativeInputProps={{ type: "text", placeholder: "06 00 00 00 00" }}
        />

        <Input
          className="fr-col-lg-4"
          label="Courriel"
          nativeInputProps={{
            type: "email",
            placeholder: "martin.dupont@courriel.fr",
          }}
        />

        <Checkbox
          className="fr-col-lg-8"
          options={[
            {
              label: "Le requérant n’a pas d’adresse courriel",
              nativeInputProps: {},
            },
          ]}
        />

        <Input
          className="fr-col-lg-12"
          label="Commentaire à destination du dossier requérant"
          textArea
          nativeTextAreaProps={{
            placeholder: "Bref descriptif de l’intervention...",
            rows: 6,
          }}
        />
      </div>

      <div className="fr-grid-row fr-grid-row--gutters fr-mt-2w">
        <ButtonsGroup
          className="fr-col-lg-12 fr-p-0"
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="small"
          buttons={[
            {
              children: "Envoyer",
              priority: "primary",
              className: "fr-mr-0",
              onClick: () => alert("TODO: enregistrer réellement"),
            },
          ]}
        />
      </div>
    </div>
  );
}
