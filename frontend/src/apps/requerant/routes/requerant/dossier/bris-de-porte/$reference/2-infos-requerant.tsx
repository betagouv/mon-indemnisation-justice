import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React, { KeyboardEvent, useState } from "react";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {
  type QualiteRequerant,
  QualiteRequerants,
} from "@/apps/requerant/models/QualiteRequerant.ts";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { useInjection } from "inversify-react";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Civilite, Dossier } from "@/apps/requerant/models";
import { useForm } from "@tanstack/react-form";
import { instanceToPlain } from "class-transformer";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { SelectionCivilite } from "@/apps/requerant/composants/SelectionCivilite.tsx";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/2-infos-requerant",
)({
  component: Etape2BrisDePorte,
});

function Etape2BrisDePorte() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Récupérer la référence depuis le paramètre de la route
  const { reference, dossier }: { reference: string; dossier: Dossier } =
    Route.useLoaderData();

  const formulaire = useForm({
    canSubmitWhenInvalid: true,
    validators: {
      //onSubmit: TODO définir le schéma de validation,
    },
    defaultValues: instanceToPlain(dossier) as Partial<Dossier>,
    listeners: {
      onChangeDebounceMs: 500,
      onChange: async ({ formApi }) => {
        await dossierManager.modifierDossier(reference, formApi.state.values);
      },
    },
    onSubmit: async ({ value, formApi }) => {
      await naviguer({
        to: "../3-pieces-jointes",
        search: {} as any,
      });
    },
  });

  const [qualiteRequerant, setQualiteRequerant] =
    useState<QualiteRequerant>("LOC");

  return (
    <>
      <h1>Déclarer un bris de porte</h1>

      <section>
        <Stepper
          currentStep={2}
          stepCount={3}
          title={"Données personnelles"}
          nextTitle={"Documents à joindre à votre demande"}
        />
      </section>

      <form
        onSubmit={async (e) => {
          e.preventDefault();
          e.stopPropagation();
          try {
            await void formulaire.handleSubmit();
          } catch (e) {
            console.error(e);
          }
        }}
      >
        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-col-12">
            {dossier.requerant.estPersonneMorale ? (
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12">
                  <section className="fr-py-2w">
                    <TitreSection>Informations de la société</TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.raisonSociale"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Raison sociale"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.siren"
                          children={(field) => {
                            return (
                              <FormInput
                                label="SIREN / SIRET"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.adresse.ligne1"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Adresse"
                                nativeInputProps={{
                                  placeholder: "Numéro de voie, rue",
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.adresse.ligne2"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Complément d'adresse"
                                nativeInputProps={{
                                  placeholder: "Étage, escalier",
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-2 fr-col-4">
                        <formulaire.Field
                          name="requerant.adresse.commune.codePostal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Code postal"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    // TODO charger la liste des communes
                                    console.log(e.target.value),
                                  maxLength: 5,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                        <formulaire.Field
                          name="requerant.adresse.commune.nom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Ville"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>
                  </section>
                </div>
                <div className="fr-col-12">
                  <section>
                    <TitreSection>
                      Représentant légal de la société
                    </TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-2 fr-col-4">
                        <formulaire.Field
                          name="requerant.civiliteRepresentantLegal"
                          children={(field) => {
                            return (
                              <SelectionCivilite
                                civilite={field.state.value}
                                onChange={(civilite: Civilite) =>
                                  field.setValue(civilite)
                                }
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                        <formulaire.Field
                          name="requerant.prenomRepresentantLegal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Prénom(s)"
                                nativeInputProps={{
                                  placeholder: "Premier prénom",
                                  value: field.state.value,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.nomNaissanceRepresentantLegal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom de naissance"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.nomRepresentantLegal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom d'usage"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.courrielRepresentantLegal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Courriel professionnel"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.telephoneRepresentantLegal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Numéro de téléphone professionnel"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            ) : (
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12">
                  <section className="fr-py-2w">
                    <TitreSection>Votre identité</TitreSection>
                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-2 fr-col-4">
                        <formulaire.Field
                          name="requerant.civilite"
                          children={(field) => {
                            return (
                              <SelectionCivilite
                                civilite={field.state.value}
                                onChange={(civilite) =>
                                  field.setValue(civilite)
                                }
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-7 fr-col-8">
                        <formulaire.Field
                          name="requerant.prenom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Prénom(s)"
                                nativeInputProps={{
                                  placeholder: "Prénom(s)",
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-3 fr-col-6">
                        <formulaire.Field
                          name="requerant.nom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom d'usage"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-4 fr-col-6">
                        <formulaire.Field
                          name="requerant.nomNaissance"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom de naissance"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-4 fr-col-6">
                        <formulaire.Field
                          name="requerant.courriel"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Adresse courriel"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  disabled: true,
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-4 fr-col-6">
                        <formulaire.Field
                          name="requerant.telephone"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Téléphone"
                                nativeInputProps={{
                                  type: "tel",
                                  pattern: "(0,+){1}[0-9]{8,}",
                                  onChange: (e) =>
                                    field.handleChange(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>
                    <TitreSection>Votre naissance</TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-3 fr-col-6">
                        <formulaire.Field
                          name="requerant.dateNaissance"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Date de naissance"
                                nativeInputProps={{
                                  type: "date",
                                  onChange: (e) =>
                                    field.handleChange(
                                      new Date(e.target.value),
                                    ),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-3 fr-col-6">
                        <formulaire.Field
                          name="requerant.paysNaissance"
                          children={(field) => {
                            return (
                              <Select
                                label="Pays de naissance"
                                nativeSelectProps={{
                                  onChange: (e) =>
                                    // TODO sélectionner le pays
                                    //field.setValue(e.target.value),
                                    console.log(e.target.value),
                                }}
                              >
                                <option value="" disabled hidden>
                                  Sélectionnez un pays
                                </option>
                                {/* TODO ajouter la liste complète des pays */}
                                <option value={"FRA"}>France</option>
                                <option value={"CIV"}>Côte d'ivoire</option>
                                <option value={"BEL"}>Belgique</option>
                              </Select>
                            );
                          }}
                        />
                      </div>

                      <formulaire.Field
                        name="requerant.communeNaissance.codePostal"
                        children={(field) => {
                          return (
                            <>
                              <div className="fr-col-lg-2 fr-col-4">
                                <FormInput
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
                                    onKeyDown: (
                                      e: KeyboardEvent<HTMLInputElement>,
                                    ) => {
                                      if (e.key !== "Backspace") {
                                        const target: HTMLInputElement =
                                          e.target as HTMLInputElement;

                                        const projectedValue =
                                          target.value.substring(
                                            0,
                                            target.selectionStart || 0,
                                          ) +
                                          e.key +
                                          target.value.substring(
                                            target.selectionEnd || 0,
                                          );

                                        if (
                                          !projectedValue.match(
                                            new RegExp(target.pattern),
                                          )
                                        ) {
                                          e.stopPropagation();
                                          e.preventDefault();
                                        }
                                      }
                                    },
                                    onChange: (e) =>
                                      field.setValue(e.target.value),
                                  }}
                                  champ={field}
                                />
                              </div>
                              <div className="fr-col-lg-4 fr-col-8">
                                <formulaire.Field
                                  name="requerant.communeNaissance"
                                  children={(field) => {
                                    return (
                                      <Select
                                        disabled={true}
                                        label="Ville de naissance"
                                        nativeSelectProps={{
                                          onChange: (e) =>
                                            // TODO charger la liste des pays depuis un objet
                                            //field.setValue(e.target.value),
                                            console.log(e.target.value),
                                        }}
                                      >
                                        <option value={""}>
                                          Sélectionnez une ville
                                        </option>
                                        {/* TODO alimenter avec les communes liées au code postal */}
                                      </Select>
                                    );
                                  }}
                                />
                              </div>
                            </>
                          );
                        }}
                      />
                    </div>

                    <TitreSection> Votre adresse de résidence</TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.adresse.ligne1"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Adresse"
                                nativeInputProps={{
                                  placeholder: "Numéro de voie, rue",
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="requerant.adresse.ligne2"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Complément d'adresse (facultatif)"
                                nativeInputProps={{
                                  placeholder: "Étage, escalier",
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-2 fr-col-4">
                        <formulaire.Field
                          name="requerant.adresse.commune.codePostal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Code postal"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 5,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                        <formulaire.Field
                          name="requerant.adresse.commune.nom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Ville"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            )}
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
      </form>
    </>
  );
}
