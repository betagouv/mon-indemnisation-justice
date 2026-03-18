import { CodePostalInput } from "@/apps/requerant/composants/champs/CodePostalInput.tsx";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { FormSelect } from "@/apps/requerant/composants/champs/form/FormSelect.tsx";
import { FormSuggestedInput } from "@/apps/requerant/composants/champs/form/FormSuggeestedInput.tsx";
import { PaysSelect } from "@/apps/requerant/composants/champs/PaysSelect";
import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant";
import { SelectionCivilite } from "@/apps/requerant/composants/SelectionCivilite.tsx";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { container } from "@/apps/requerant/container.ts";
import {
  extraireDonneesInfosRequerant,
  SchemaValidationInfosRequerants,
} from "@/apps/requerant/formulaires/brisDePorte/2-infos-requerants.schema.ts";
import {
  Adresse,
  Civilite,
  Commune,
  Dossier,
  Pays,
} from "@/apps/requerant/models";
import { AdresseManagerInterface } from "@/apps/requerant/services/AdresseManager.ts";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import classes from "@/apps/requerant/style/form.module.css";
import { Loader } from "@/common/components/Loader";
import { dateChiffre } from "@/common/services/date.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { useForm, useStore } from "@tanstack/react-form";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useNavigate,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React, { useEffect, useState } from "react";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/2-infos-requerant",
)({
  component: Etape2InfosRequerant,
  pendingComponent: Loader,
  notFoundComponent: (props: NotFoundRouteProps) => (
    <NonTrouveComposant {...props} />
  ),
  loader: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .getDossier(params.reference);

    if (!dossier) {
      throw notFound({
        data: {
          titre: "Impossible de trouver le dossier",
          message: (
            <>
              Le dossier de référence <i>${params.reference}</i>n'existe pas ou
              ne vous est pas accessible.
            </>
          ),
        },
        throw: true,
      });
    }

    return { reference: params.reference, dossier };
  },
  shouldReload: true,
});

function Etape2InfosRequerant() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  const adresseManager = useInjection<AdresseManagerInterface>(
    AdresseManagerInterface.$,
  );

  // Récupérer la référence depuis le paramètre de la route
  const { reference, dossier }: { reference: string; dossier: Dossier } =
    Route.useLoaderData();

  const [codePostal, setCodePostal] = useState<string>(
    dossier.personnePhysique?.communeNaissance.codePostal ?? "",
  );
  const [listeCommunes, setListeCommunes] = useState<Commune[]>([]);

  useEffect(() => {
    if (codePostal.length === 5) {
      rafraichirListeCommunes(codePostal);
    }
  }, [reference, codePostal]);

  const rafraichirListeCommunes = async (codePostal: string) => {
    setListeCommunes(await adresseManager.listerCommunes(codePostal));
  };

  const formulaire = useForm({
    validators: {
      onSubmit: SchemaValidationInfosRequerants,
    },
    defaultValues: extraireDonneesInfosRequerant(dossier),
    listeners: {
      onChangeDebounceMs: 500,
      onChange: ({ formApi, fieldApi }) => {
        dossierManager.modifier(reference, formApi.state.values);
      },
    },
    onSubmit: async ({ value, formApi }) => {
      // Enregistrer le brouillon...
      await dossierManager.enregistrer(reference);
      // ...et passer à l'étape suivante
      await naviguer({
        to: "../3-pieces-jointes",
        search: {} as any,
      });
    },
  });

  const { estPersonneMorale, paysNaissance } = useStore(
    formulaire.store,
    (state) => ({
      estPersonneMorale: state.values.estPersonneMorale,
      paysNaissance: state.values.personnePhysique?.paysNaissance,
    }),
  );

  return (
    <>
      <h1>Déclarer un bris de porte</h1>

      <form
        onSubmit={async (e) => {
          e.preventDefault();
          e.stopPropagation();
          try {
            // Rafraîchir la validation avant la soumission
            formulaire.validate("submit");
            await formulaire.handleSubmit();
          } catch (e) {
            console.error(e);
          }
        }}
        className={classes.mijForm}
      >
        <section>
          <Stepper
            currentStep={2}
            stepCount={3}
            title={"Données personnelles"}
            nextTitle={"Documents à joindre à votre demande"}
          />
        </section>

        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-col-12">
            {estPersonneMorale ? (
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12">
                  <section className="fr-py-2w">
                    <TitreSection>Informations de la société</TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personneMorale.raisonSociale"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Raison sociale"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                estRequis={true}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personneMorale.siren"
                          children={(field) => {
                            return (
                              <FormInput
                                label="SIREN / SIRET"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                estRequis={true}
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
                          name="personnePhysique.adresse.ligne1"
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
                                estRequis={true}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personnePhysique.adresse.ligne2"
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
                          name="personnePhysique.adresse.codePostal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Code postal"
                                nativeInputProps={{
                                  // TODO charger la liste des communes
                                  maxLength: 5,
                                }}
                                estRequis={true}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                        <formulaire.Field
                          name="personnePhysique.adresse.commune"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Ville"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                estRequis={true}
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
                          name="personneMorale.representantLegal.civilite"
                          children={(field) => {
                            return (
                              <SelectionCivilite
                                civilite={field.state.value}
                                onChange={(civilite: Civilite) =>
                                  field.setValue(civilite)
                                }
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                        <formulaire.Field
                          name="personneMorale.representantLegal.prenom"
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
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personneMorale.representantLegal.nomNaissance"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom de naissance"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personneMorale.representantLegal.nom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom d'usage"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personneMorale.representantLegal.courriel"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Courriel professionnel"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personneMorale.representantLegal.telephone"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Numéro de téléphone professionnel"
                                nativeInputProps={{
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                                estRequis={true}
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
                          name="personnePhysique.personne.civilite"
                          children={(field) => {
                            return (
                              <SelectionCivilite
                                civilite={field.state.value as Civilite}
                                onChange={(civilite) =>
                                  field.setValue(civilite)
                                }
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-7 fr-col-8">
                        <formulaire.Field
                          name="personnePhysique.personne.prenom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Prénom(s)"
                                nativeInputProps={{
                                  placeholder: "Prénom(s)",
                                  defaultValue:
                                    [
                                      field.form.state.values.personnePhysique
                                        ?.personne.prenom,
                                      field.form.state.values.personnePhysique
                                        ?.prenom2,
                                      field.form.state.values.personnePhysique
                                        ?.prenom3,
                                    ]
                                      .filter(Boolean)
                                      ?.join(", ") || "",
                                  onChange: (e) => {
                                    const prenoms = e.target.value
                                      .split(",")
                                      .map((p) => p.trim());
                                    field.setValue(prenoms.at(0) || "");
                                    field.form.setFieldValue(
                                      "personnePhysique.prenom2",
                                      prenoms.at(1),
                                    );
                                    field.form.setFieldValue(
                                      "personnePhysique.prenom3",
                                      prenoms.at(2),
                                    );
                                  },
                                  maxLength: 255,
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-3 fr-col-6">
                        <formulaire.Field
                          name="personnePhysique.personne.nom"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom d'usage"
                                nativeInputProps={{
                                  defaultValue: field.state.value,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-4 fr-col-6">
                        <formulaire.Field
                          name="personnePhysique.personne.nomNaissance"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Nom de naissance"
                                nativeInputProps={{
                                  defaultValue: field.state.value,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-4 fr-col-6">
                        <formulaire.Field
                          name="personnePhysique.personne.courriel"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Adresse courriel"
                                nativeInputProps={{
                                  defaultValue: field.state.value,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  disabled: true,
                                  maxLength: 255,
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-4 fr-col-6">
                        <formulaire.Field
                          name="personnePhysique.personne.telephone"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Téléphone"
                                nativeInputProps={{
                                  type: "tel",
                                  //pattern: "[0-9]{7,}",
                                  defaultValue: field.state.value,
                                  onChange: (e) =>
                                    field.handleChange(e.target.value),
                                }}
                                champ={field}
                                estRequis={true}
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
                          name="personnePhysique.dateNaissance"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Date de naissance"
                                nativeInputProps={{
                                  type: "date",
                                  defaultValue: dateChiffre(field.state.value),
                                  onChange: (e) =>
                                    field.handleChange(
                                      new Date(e.target.value),
                                    ),
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-3 fr-col-6">
                        <formulaire.Field
                          name="personnePhysique.paysNaissance"
                          children={(field) => {
                            return (
                              <PaysSelect
                                label="Pays de naissance"
                                pays={field.state.value}
                                onSelectionne={(pays: Pays) =>
                                  field.setValue(pays)
                                }
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>

                      {!paysNaissance || paysNaissance?.estFrance() ? (
                        /* @ts-ignore*/
                        <formulaire.Field
                          name="personnePhysique.communeNaissance"
                          children={(field) => {
                            return (
                              <>
                                <div className="fr-col-lg-2 fr-col-4">
                                  <CodePostalInput
                                    label="Code postal"
                                    disabled={!paysNaissance}
                                    nativeInputProps={{
                                      defaultValue:
                                        field.state.value?.codePostal || "",
                                      onChange: async (e) => {
                                        setCodePostal(e.target.value);
                                      },
                                    }}
                                    estRequis={true}
                                    champ={field}
                                  />
                                </div>
                                <div className="fr-col-lg-4 fr-col-8">
                                  <FormSelect
                                    disabled={!listeCommunes.length}
                                    label="Ville de naissance"
                                    nativeSelectProps={{
                                      value: field.state.value?.id || "",
                                      onChange: (e) => {
                                        const id = parseInt(e.target.value);

                                        if (id) {
                                          const commune = listeCommunes.find(
                                            (commune: Commune) =>
                                              commune.id == id,
                                          );
                                          field.setValue(commune);
                                        } else {
                                          field.setValue(undefined);
                                        }
                                      },
                                    }}
                                    champ={field}
                                    estRequis={true}
                                  >
                                    <option value={""}>
                                      Sélectionnez une ville
                                    </option>
                                    {listeCommunes.map((commune: Commune) => (
                                      <option
                                        key={commune.id}
                                        value={commune.id}
                                      >
                                        {commune.nom}
                                      </option>
                                    ))}
                                  </FormSelect>
                                </div>
                              </>
                            );
                          }}
                        />
                      ) : (
                        <formulaire.Field
                          name="personnePhysique.villeNaissance"
                          children={(field) => {
                            return (
                              <div className="fr-col-lg-6 fr-col-12">
                                <FormInput
                                  label="Ville de naissance"
                                  nativeInputProps={{
                                    defaultValue: field.state.value || "",
                                    onChange: (e) =>
                                      field.setValue(e.target.value),
                                  }}
                                  champ={field}
                                  estRequis={true}
                                />
                              </div>
                            );
                          }}
                        />
                      )}
                    </div>

                    <TitreSection> Votre adresse de résidence</TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personnePhysique.adresse.ligne1"
                          children={(field) => {
                            return (
                              <FormSuggestedInput<Adresse>
                                label="Adresse"
                                nativeInputProps={{
                                  onChange: (e) => {
                                    field.setValue(e.target.value);
                                  },
                                  defaultValue: field.state.value,
                                }}
                                onSelectionne={(suggestion: Adresse) => {
                                  field.setValue(suggestion.ligne1);
                                  field.form.setFieldValue(
                                    "personnePhysique.adresse.commune",
                                    suggestion.commune,
                                  );
                                  field.form.setFieldValue(
                                    "personnePhysique.adresse.codePostal",
                                    suggestion.codePostal,
                                  );
                                  field.form.validateField(
                                    "personnePhysique.adresse",
                                    "submit",
                                  );

                                  return suggestion.ligne1;
                                }}
                                rafraichisseur={async (valeur: string) =>
                                  (
                                    await adresseManager.suggererAdresse(valeur)
                                  ).map((adresse: Adresse) => ({
                                    libelle: adresse.libelle,
                                    valeur: adresse,
                                  }))
                                }
                                // Ne rafraichir la liste des suggestions que le lorsque la valeur saisie atteint au moins 5 caractères non blancs
                                estARafraichir={(valeur: string) => {
                                  return (
                                    valeur.replaceAll(/\s+/g, "").length >= 5
                                  );
                                }}
                                estRequis={true}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>

                      <div className="fr-col-lg-6 fr-col-12">
                        <formulaire.Field
                          name="personnePhysique.adresse.ligne2"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Complément d'adresse"
                                nativeInputProps={{
                                  defaultValue: field.state.value || "",
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
                          name="personnePhysique.adresse.codePostal"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Code postal"
                                nativeInputProps={{
                                  defaultValue: field.state.value,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 5,
                                }}
                                champ={field}
                                estRequis={true}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                        <formulaire.Field
                          name="personnePhysique.adresse.commune"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Ville"
                                nativeInputProps={{
                                  defaultValue: field.state.value,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                  maxLength: 255,
                                }}
                                champ={field}
                                estRequis={true}
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
              children: "Revenir à l'étape précédente",
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
                role: "submit",
              },
            },
          ]}
        />
      </form>
    </>
  );
}
