import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import Download from "@codegouvfr/react-dsfr/Download";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Select } from "@codegouvfr/react-dsfr/Select";

export const Route = createFileRoute(
  "/agent/fdo/declarer-erreur-operationnelle/2-complement",
)({
  component: Page,
});

const ModaleAjoutFichier = createModal({
  id: "modale-ajouter-fichier-declaration-erreur-operationnelle",
  isOpenedByDefault: false,
});

function Page() {
  const naviguer = useNavigate({
    from: "/agent/fdo/declarer-erreur-operationnelle/2-complement",
  });

  return (
    <>
      <ModaleAjoutFichier.Component
        size="large"
        title="Ajouter un document"
        buttons={[
          {
            children: "Ajouter",
          },
        ]}
      >
        <div className="fr-grid-row fr-grid-row--gutters">
          <Upload
            className="fr-col-lg-6"
            label="Fichier à téléverser"
            hint="Taille maximale : 10 Mo. Format pdf uniquement."
            nativeInputProps={{
              accept: "application/pdf,image/*",
            }}
          />

          <Select
            label="Type de document"
            className="fr-col-lg-6"
            nativeSelectProps={{}}
          >
            <option value="" disabled hidden>
              Sélectionnez un type
            </option>

            <option value="pv_intervention">PV d'intervention</option>
            <option value="photo_porte">Photo de la porte endommagée</option>
          </Select>
        </div>
      </ModaleAjoutFichier.Component>

      <div style={{ display: "flex", flexDirection: "column", gap: "1.5vh" }}>
        <h1 className="fr-m-0">Nouvelle déclaration d'erreur opérationnelle</h1>

        <Stepper
          className="fr-m-0"
          currentStep={2}
          stepCount={3}
          title="Complément d’information pour le Ministère de la Justice"
          nextTitle="Informations du requérant"
        />

        <div className="fr-grid-row">
          <h6 className="fr-m-0">
            Informations concernant l’opération de police judiciaire
          </h6>
        </div>

        <div className="fr-grid-row fr-grid-row--gutters">
          <Input
            className="fr-col-lg-4 fr-m-0"
            label="Nom du service enquêteur"
            nativeInputProps={{ type: "text" }}
          />

          <Input
            className="fr-col-lg-4 fr-m-0"
            label="Courriel"
            nativeInputProps={{ type: "email" }}
          />

          <Input
            className="fr-col-lg-4 fr-m-0"
            label="Téléphone"
            nativeInputProps={{ type: "text" }}
          />
        </div>

        <div className="fr-grid-row">
          <h6 className="fr-m-0">Document justificatifs</h6>
        </div>

        <div
          className="fr-grid-row fr-grid-row--gutters"
          style={{ alignItems: "baseline" }}
        >
          <div className="fr-col-lg-4">
            <Download
              details="PDF - 88 Ko"
              label="PV d'intervention"
              linkProps={{}}
            />
          </div>

          <div className="fr-col-lg-4">
            <Download
              label="Photo de la porte d'entrée"
              details="JPEG - 792 Ko"
              linkProps={{}}
            />
          </div>

          <div className="fr-col-lg-4">
            <Button
              children="Ajouter un document"
              iconId="fr-icon-add-line"
              size="small"
              priority="secondary"
              linkProps={{
                onClick: () => ModaleAjoutFichier.open(),
              }}
            />
          </div>
        </div>

        <div className="fr-grid-row fr-grid-row--gutters">
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
                    to: "/agent/fdo/declarer-erreur-operationnelle/3-requerant",
                  }),
              },
            ]}
          />
        </div>
      </div>
    </>
  );
}
