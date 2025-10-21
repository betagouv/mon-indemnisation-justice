import React from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Input } from "@codegouvfr/react-dsfr/Input";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";

export const Route = createFileRoute(
  "/agent/fdo/declarer-erreur-operationnelle/1-operation",
)({
  component: () => <Composant />,
});

const Composant = () => {
  const naviguer = useNavigate({
    from: "/agent/fdo/declarer-erreur-operationnelle/1-operation",
  });

  return (
    <div>
      <h1>Nouvelle déclaration d'erreur opérationnelle</h1>

      <Stepper
        currentStep={1}
        stepCount={3}
        title="Eléments relatifs à l’erreur opérationnelle"
        nextTitle="Complément d’information pour le Ministère de la Justice "
      />

      <div className="fr-grid-row fr-grid-row--gutters fr-mt-2w">
        <Input
          className="fr-col-lg-4 fr-col-offset-lg-8--right"
          label="Date de l'opération"
          nativeInputProps={{ type: "date" }}
        />

        <Input
          className="fr-col-lg-6"
          label="Adresse de l'erreur opérationnelle"
          nativeInputProps={{
            type: "text",
            placeholder: "Numéro de voie, rue",
          }}
        />

        <Input
          className="fr-col-lg-2 fr-col-offset-lg-4--right"
          label="Complément"
          nativeInputProps={{
            type: "text",
            placeholder: "Étage, escalier",
          }}
        />

        <Input
          className="fr-col-lg-4"
          label="Code postal"
          nativeInputProps={{
            type: "text",
          }}
        />

        <Input
          className="fr-col-lg-4 fr-col-offset-lg-4--right"
          label="Ville"
          nativeInputProps={{
            type: "text",
          }}
        />

        <Input
          className="fr-col-lg-12"
          label="Commentaire à destination du Ministère de la Justice"
          textArea
        />

        <ButtonsGroup
          className="fr-col-lg-12 fr-p-0"
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="small"
          buttons={[
            {
              children: "Continuer",
              priority: "primary",
              onClick: () =>
                naviguer({
                  to: "/agent/fdo/declarer-erreur-operationnelle/2-complement",
                }),
            },
          ]}
        />
      </div>
    </div>
  );
};
