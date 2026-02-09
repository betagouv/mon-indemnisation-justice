import { createFileRoute, useNavigate } from "@tanstack/react-router";
import React, { KeyboardEvent, useState } from "react";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import PersonneMorale from "@/apps/requerant/dossier/components/PersonneMorale.tsx";
import { Input } from "@codegouvfr/react-dsfr/Input";
import Civilite from "@/apps/requerant/dossier/components/Civilite.tsx";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";

export const Route = createFileRoute(
  "/requerant/demande/bris-de-porte/$id/1-etat-civil",
)({
  component: Etape1EtatCivil,
});

function Etape1EtatCivil() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const [estPersonneMorale, setPersonneMorale] = useState<boolean | undefined>(
    undefined,
  );

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

      <section>
        <div className="fr-grid-row">
          <div className="fr-col-12">
            <RadioButtons
              orientation="horizontal"
              legend="Votre demande d'indemnisation concerne une personne morale (société, entreprise, association, fondation etc.)"
              options={[
                {
                  label: "Oui",
                  nativeInputProps: {
                    checked: estPersonneMorale == true,
                    onChange: () => setPersonneMorale(true),
                  },
                },
                {
                  label: "Non",
                  nativeInputProps: {
                    checked: estPersonneMorale == false,
                    onChange: () => setPersonneMorale(false),
                  },
                },
              ]}
            />
          </div>
        </div>
      </section>

      {estPersonneMorale == true && (
        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-col-12">
            <section className="fr-py-2w">
              <TitreSection>Informations de la société</TitreSection>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Raison sociale"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="SIREN / SIRET"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>
              </div>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Adresse"
                    nativeInputProps={{
                      placeholder: "Numéro de voie, rue",
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>

                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Complément d'adresse"
                    nativeInputProps={{
                      placeholder: "Étage, escalier",
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
            </section>
          </div>
          <div className="fr-col-12">
            <section className="pr-form-section fr-py-2w">
              <TitreSection>Représentant légal de la société</TitreSection>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-2 fr-col-4">
                  <Civilite
                    civilite={undefined}
                    setCivilite={(civilite) => console.log(civilite)}
                  />
                </div>
                <div className="fr-col-lg-10 fr-col-8">
                  <Input
                    label="Prénom(s)"
                    nativeInputProps={{
                      placeholder: "Premier prénom",
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Nom de naissance"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Nom d'usage"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Courriel professionnel"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Numéro de téléphone professionnel"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>
              </div>
            </section>
          </div>
        </div>
      )}

      {estPersonneMorale == false && (
        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-col-12">
            <section className="fr-py-2w">
              <TitreSection>Votre identité</TitreSection>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-2 fr-col-4">
                  <Civilite
                    civilite={undefined}
                    setCivilite={(civilite) => console.log(civilite)}
                  />
                </div>
                <div className="fr-col-lg-7 fr-col-8">
                  <Input
                    label="Prénom(s)"
                    nativeInputProps={{
                      placeholder: "Prénom(s)",
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>
                <div className="fr-col-lg-3 fr-col-6">
                  <Input
                    label="Nom d'usage"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>
                <div className="fr-col-lg-4 fr-col-6">
                  <Input
                    label="Nom de naissance"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>
                <div className="fr-col-lg-4 fr-col-6">
                  <Input
                    disabled={true}
                    label="Adresse courriel"
                    nativeInputProps={{
                      onChange: (e) => console.log(e.target.value),
                      disabled: true,
                      maxLength: 255,
                    }}
                  />
                </div>
                <div className="fr-col-lg-4 fr-col-6">
                  <Input
                    label="Téléphone"
                    nativeInputProps={{
                      type: "tel",
                      pattern: "(0,+){1}[0-9]{8,}",
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>
              </div>

              <div className="fr-grid-row fr-my-3w">
                <h6 className="fr-m-0 fr-text-label--blue-france">
                  Votre naissance
                </h6>
              </div>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-3 fr-col-6">
                  <Input
                    label="Date de naissance"
                    nativeInputProps={{
                      type: "date",
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>

                <div className="fr-col-lg-3 fr-col-6">
                  <Select
                    label="Pays de naissance"
                    nativeSelectProps={{
                      onChange: (e) => console.log(e.target.value),
                    }}
                  >
                    <option value="" disabled hidden>
                      Sélectionnez un pays
                    </option>
                    {/* TODO aouter la liste des pays */}
                  </Select>
                </div>

                <div className="fr-col-lg-2 fr-col-4">
                  <Input
                    label="Code postal"
                    nativeInputProps={{
                      type: "text",
                      maxLength: 5,
                      pattern: "^[0-9]{0,5}$",
                      onFocus: (e) => {
                        // Mettre le curseur à la fin
                        e.target.setSelectionRange(
                          e.target.value.length + 1,
                          e.target.value.length + 1,
                        );
                      },
                      onKeyDown: (e: KeyboardEvent<HTMLInputElement>) => {
                        if (e.key !== "Backspace") {
                          const target: HTMLInputElement =
                            e.target as HTMLInputElement;

                          const projectedValue =
                            target.value.substring(
                              0,
                              target.selectionStart || 0,
                            ) +
                            e.key +
                            target.value.substring(target.selectionEnd || 0);

                          if (
                            !projectedValue.match(new RegExp(target.pattern))
                          ) {
                            e.stopPropagation();
                            e.preventDefault();
                          }
                        }
                      },
                      onChange: (e) => console.log(e.target.value),
                    }}
                  />
                </div>

                <div className="fr-col-lg-4 fr-col-8">
                  <Select
                    disabled={true}
                    label="Ville de naissance"
                    nativeSelectProps={{
                      onChange: (e) => console.log(e.target.value),
                    }}
                  >
                    <option value={""}>Sélectionnez une ville</option>
                    {/* TODO alimenter avec les communes liées au code postal */}
                  </Select>
                </div>
              </div>

              <div className="fr-grid-row fr-my-3w">
                <h6 className="fr-m-0 fr-text-label--blue-france">
                  Votre adresse de résidence
                </h6>
              </div>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Adresse"
                    nativeInputProps={{
                      placeholder: "Numéro de voie, rue",
                      onChange: (e) => console.log(e.target.value),
                      maxLength: 255,
                    }}
                  />
                </div>

                <div className="fr-col-lg-6 fr-col-12">
                  <Input
                    label="Complément d'adresse (facultatif)"
                    nativeInputProps={{
                      placeholder: "Étage, escalier",
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
            </section>
          </div>
        </div>
      )}

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttonsIconPosition="right"
        buttons={[
          {
            disabled: estPersonneMorale === undefined,
            priority: "primary",
            children: "Valider et passer à l'étape suivante",
            onClick: () =>
              naviguer({
                to: "../2-bris-de-porte",
              }),
          },
        ]}
      />
    </>
  );
}
