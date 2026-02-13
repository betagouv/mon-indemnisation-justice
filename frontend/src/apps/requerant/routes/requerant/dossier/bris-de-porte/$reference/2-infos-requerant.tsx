import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React, { useState } from "react";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {
  type QualiteRequerant,
  QualiteRequerants,
} from "@/apps/requerant/models/QualiteRequerant.ts";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/2-infos-requerant",
)({
  component: Etape2BrisDePorte,
});

function Etape2BrisDePorte() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const [qualiteRequerant, setQualiteRequerant] =
    useState<QualiteRequerant>("LOC");

  return (
    <>
      <h1>Déclarer un bris de porte</h1>

      <section>
        <Stepper
          currentStep={1}
          stepCount={3}
          title={"Données personnelles"}
          nextTitle={"Informations relatives au bris de porte"}
        />
      </section>

      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-12">
          <section className="fr-py-2w">
            <TitreSection>Informations sur le bris de porte</TitreSection>

            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-12">
                <Input
                  label="Décrivez-nous l’intervention"
                  textArea
                  nativeTextAreaProps={{
                    placeholder: "Intervention survenue ce matin ...",
                    onChange: (e) => console.log(e.target.value),
                    rows: 10,
                    cols: 50,
                  }}
                />
              </div>
            </div>
            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-lg-4 fr-col-12">
                <Input
                  label="Date de l'opération de police judiciaire"
                  nativeInputProps={{
                    type: "date",
                    onChange: (e) => console.log(e.target.value),
                  }}
                />
              </div>
              <div className="fr-col-offset-8"></div>
              <div className="fr-col-12">
                <div className="fr-grid-row fr-grid-row--gutters">
                  <div className="fr-col-6">
                    <Input
                      label="Adresse du logement concerné par le bris de porte"
                      nativeInputProps={{
                        onChange: (e) => console.log(e.target.value),
                        maxLength: 255,
                      }}
                    />
                  </div>
                  <div className="fr-col-12">
                    <Input
                      label="Complément d'adresse"
                      hintText={"Facultatif"}
                      nativeInputProps={{
                        onChange: (e) => console.log(e.target.value),
                        maxLength: 255,
                      }}
                    />
                  </div>
                  <div className="fr-col-lg-2 fr-col-4">
                    <Input
                      label="Code postal"
                      nativeInputProps={{
                        onChange: (e) => console.log(e.target.value),
                        maxLength: 5,
                      }}
                    />
                  </div>
                  <div className="fr-col-lg-10 fr-col-8">
                    <Input
                      label="Ville"
                      nativeInputProps={{
                        onChange: (e) => console.log(e.target.value),
                        maxLength: 255,
                      }}
                    />
                  </div>
                </div>
              </div>
              <div className="fr-col-12">
                <RadioButtons
                  legend="S'agit-il d'une porte blindée ?"
                  orientation="horizontal"
                  options={[
                    {
                      label: "Oui",
                      nativeInputProps: {
                        onChange: (e) => console.log(e.target.checked),
                      },
                    },
                    {
                      label: "Non",
                      nativeInputProps: {
                        checked: true,
                        onChange: (e) => console.log(e.target.checked),
                      },
                    },
                  ]}
                />
              </div>
              <div className="fr-col-12">
                <div className="fr-grid-row fr-grid-row--gutters">
                  <div className="fr-col-lg-6 fr-col-12">
                    <Select
                      label="Vous effectuez votre demande en qualité de"
                      nativeSelectProps={{
                        onChange: (event) => {
                          console.log(event.target.value);
                          if (
                            Object.keys(QualiteRequerants).includes(
                              event.target.value,
                            )
                          ) {
                            setQualiteRequerant(
                              event.target.value as QualiteRequerant,
                            );
                          }
                        },
                      }}
                    >
                      <option value="" disabled hidden>
                        Selectionnez une option
                      </option>
                      {Object.entries(QualiteRequerants).map(([key, label]) => (
                        <option key={key} value={key}>
                          {label}
                        </option>
                      ))}
                    </Select>
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                    {qualiteRequerant === "AUT" && (
                      <Input
                        label="Précisez"
                        nativeInputProps={{
                          onChange: (e) => console.log(e.target.checked),
                          maxLength: 255,
                        }}
                      />
                    )}
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttonsIconPosition="right"
        buttons={[
          {
            priority: "secondary",
            children: "Revenir à l'étape suivante",
            nativeButtonProps: {
              type: "button",
            },
            onClick: () =>
              naviguer({
                from: Route.fullPath,
                to: "../1-bris-porte",
                search: {} as any,
              }),
          },
          {
            priority: "primary",
            children: "Valider et passer à l'étape suivante",
            nativeButtonProps: {
              type: "submit",
            },
            onClick: () =>
              naviguer({
                from: Route.fullPath,
                to: "../3-pieces-jointes",
                search: {} as any,
              }),
          },
        ]}
      />
    </>
  );
}
