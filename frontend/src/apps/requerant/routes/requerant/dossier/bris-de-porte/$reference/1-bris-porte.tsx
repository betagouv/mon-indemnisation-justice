import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { FormSelect } from "@/apps/requerant/composants/champs/form/FormSelect.tsx";
import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { container } from "@/apps/requerant/container";
import {
  Dossier,
  getLibelleTypePersonneMorale,
  getRapportAuLogementLibelle,
  RapportAuLogement,
  TypePersonneMoraleType,
  TypesPersonneMorale,
} from "@/apps/requerant/models";
import { RapportAuLogements } from "@/apps/requerant/models/RapportAuLogement.ts";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import classes from "@/apps/requerant/style/form.module.css";
import { Loader } from "@/common/components/Loader.tsx";
import { dateChiffre } from "@/common/services/date.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { useForm, useStore } from "@tanstack/react-form";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useNavigate,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React from "react";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/1-bris-porte",
)({
  component: Etape1BrisPorte,
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
export default Route;

function Etape1BrisPorte() {
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
    defaultValues: dossier as Partial<Dossier>,
    listeners: {
      onChangeDebounceMs: 500,
      onChange: async ({ formApi }) => {
        dossierManager.modifier(reference, formApi.state.values);
      },
    },
    onSubmit: async ({ value, formApi }) => {
      // Enregistrer le brouillon...
      await dossierManager.enregistrer(reference, formApi.state.values);
      // ...et passer à l'étape suivante
      await naviguer({
        to: "../2-infos-requerant",
        search: {} as any,
      });
    },
  });

  const {
    estPersonneMorale,
    typePersonneMorale,
    rapportAuLogement,
    descriptionRapportAuLogement,
  } = useStore(formulaire.store, (state) => ({
    estPersonneMorale: state.values.estPersonneMorale,
    typePersonneMorale: state.values.personneMorale?.typePersonneMorale,
    rapportAuLogement: state.values.rapportAuLogement,
    descriptionRapportAuLogement: state.values.descriptionRapportAuLogement,
  }));

  // @ts-ignore
  // @ts-ignore
  return (
    <>
      <h1>Déclarer un bris de porte</h1>

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
        className={classes.mijForm}
      >
        <section>
          <Stepper
            currentStep={1}
            stepCount={3}
            title={"Informations relatives au bris de porte"}
            nextTitle={"Données personnelles"}
          />
        </section>

        <section>
          <div className="fr-grid-row">
            <div className="fr-col-12">
              <formulaire.Field
                name="estPersonneMorale"
                children={(field) => {
                  return (
                    <FormRadioButtons
                      orientation="horizontal"
                      legend="Je suis"
                      champ={field}
                      estRequis={true}
                      options={[
                        {
                          label: "Une personne physique",
                          hintText: "Je suis un particulier",
                          nativeInputProps: {
                            checked: field.state.value == false,
                            onChange: () => {
                              field.setValue(false);

                              field.form.setFieldValue(
                                "rapportAuLogement",
                                undefined,
                              );
                            },
                          },
                        },
                        {
                          label: "Une personne morale",
                          hintText:
                            "Je représente une entreprise ou une association",
                          nativeInputProps: {
                            checked: field.state.value == true,
                            onChange: () => {
                              field.setValue(true);
                              field.form.setFieldValue(
                                "rapportAuLogement",
                                undefined,
                              );
                            },
                          },
                        },
                      ]}
                    />
                  );
                }}
              />
            </div>
          </div>

          {estPersonneMorale && (
            <div className="fr-col-12">
              <div className="fr-grid-row fr-grid-row--gutters">
                <formulaire.Field
                  name="personneMorale.typePersonneMorale"
                  children={(field) => {
                    return (
                      <FormSelect
                        className="fr-col-12 fr-col-lg-6"
                        label="Quel type de personne morale ?"
                        hint="Ex: professionnel privé, représentant d'une association ou assureur du logement d'un particulier"
                        estRequis={true}
                        champ={field}
                        nativeSelectProps={{
                          defaultValue: field.state.value ?? "",
                          onChange: (event) =>
                            field.setValue(
                              event.target.value as TypePersonneMoraleType,
                            ),
                        }}
                      >
                        <option value="" disabled hidden>
                          Selectionnez une option
                        </option>
                        {TypesPersonneMorale.map(
                          (type: TypePersonneMoraleType) => (
                            <option key={type} value={type}>
                              {getLibelleTypePersonneMorale(type)}
                            </option>
                          ),
                        )}
                      </FormSelect>
                    );
                  }}
                />
              </div>
            </div>
          )}

          {(estPersonneMorale === false || !!typePersonneMorale) && (
            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-lg-6 fr-col-12">
                <formulaire.Field
                  name="rapportAuLogement"
                  children={(field) => {
                    return (
                      <FormSelect
                        label="Vous effectuez votre demande en qualité de"
                        champ={field}
                        estRequis={true}
                        nativeSelectProps={{
                          defaultValue: field.state.value || "",
                          onChange: (event) => {
                            if (!!event.target.value) {
                              field.setValue(
                                event.target.value as RapportAuLogement,
                              );
                            }
                          },
                        }}
                      >
                        <option value="" disabled hidden>
                          Selectionnez une option
                        </option>
                        {RapportAuLogements.map((rapportAuLogement) => (
                          <option
                            key={rapportAuLogement}
                            value={rapportAuLogement}
                          >
                            {getRapportAuLogementLibelle(rapportAuLogement)}
                          </option>
                        ))}
                      </FormSelect>
                    );
                  }}
                />
              </div>

              {rapportAuLogement === "AUTRE" && (
                <div className="fr-col-lg-6 fr-col-12">
                  <formulaire.Field
                    name="descriptionRapportAuLogement"
                    children={(field) => {
                      return (
                        <FormInput
                          label="Précisez"
                          estRequis={rapportAuLogement === "AUTRE"}
                          nativeInputProps={{
                            onChange: (e) => field.setValue(e.target.value),
                            maxLength: 255,
                          }}
                        />
                      );
                    }}
                  />
                </div>
              )}
            </div>
          )}
        </section>

        {estPersonneMorale !== undefined &&
          rapportAuLogement !== undefined &&
          (rapportAuLogement !== "AUTRE" || !!descriptionRapportAuLogement) && (
            <section>
              <TitreSection>Informations sur le bris de porte</TitreSection>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-lg-4 fr-col-12">
                  <formulaire.Field
                    name="dateOperation"
                    children={(field) => {
                      return (
                        <FormInput
                          label="Date du bris de porte"
                          nativeInputProps={{
                            type: "date",
                            defaultValue: dateChiffre(field.state.value),
                            onChange: (e) =>
                              field.setValue(new Date(e.target.value)),
                          }}
                          estRequis={true}
                          champ={field}
                        />
                      );
                    }}
                  />
                </div>

                <div className="fr-col-lg-8 fr-col-12">
                  <formulaire.Field
                    name="description"
                    children={(field) => {
                      return (
                        <FormInput
                          label="Décrivez-nous l’intervention"
                          textArea
                          nativeTextAreaProps={{
                            placeholder: "Intervention survenue ce matin ...",
                            rows: 10,
                            cols: 50,
                            onChange: (e) => field.setValue(e.target.value),
                          }}
                          champ={field}
                        />
                      );
                    }}
                  />
                </div>
              </div>

              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12 fr-col-lg-6">
                  <formulaire.Field
                    name="adresse.ligne1"
                    children={(field) => {
                      return (
                        <FormInput
                          label="Adresse du logement concerné par le bris de porte"
                          nativeInputProps={{
                            maxLength: 255,
                            defaultValue: field.state.value,
                            onChange: (e) => field.setValue(e.target.value),
                          }}
                          estRequis={true}
                          champ={field}
                        />
                      );
                    }}
                  />
                </div>
                <div className="fr-col-12 fr-col-lg-6">
                  <formulaire.Field
                    name="adresse.ligne1"
                    children={(field) => {
                      return (
                        <FormInput
                          label="Complément d'adresse"
                          nativeInputProps={{
                            maxLength: 255,
                            defaultValue: field.state.value,
                            onChange: (e) => field.setValue(e.target.value),
                          }}
                          champ={field}
                        />
                      );
                    }}
                  />
                </div>
                <div className="fr-col-lg-2 fr-col-4">
                  <formulaire.Field
                    name="adresse.codePostal"
                    children={(field) => {
                      return (
                        <>
                          <FormInput
                            label="Code postal"
                            nativeInputProps={{
                              defaultValue: field.state.value,
                              onChange: (e) => field.setValue(e.target.value),
                              maxLength: 5,
                            }}
                            estRequis={true}
                            champ={field}
                          />
                        </>
                      );
                    }}
                  />
                </div>
                <div className="fr-col-lg-10 fr-col-8">
                  <formulaire.Field
                    name="adresse.commune"
                    children={(field) => {
                      return (
                        <>
                          <FormInput
                            label="Ville"
                            nativeInputProps={{
                              defaultValue: field.state.value,
                              onChange: (e) => field.setValue(e.target.value),
                              maxLength: 255,
                            }}
                            estRequis={true}
                            champ={field}
                          />
                        </>
                      );
                    }}
                  />
                </div>
              </div>
              <div className="fr-col-12">
                {/* @ts-ignore*/}
                <formulaire.Field
                  name="estPorteBlindee"
                  children={(field) => {
                    return (
                      <RadioButtons
                        legend="S'agit-il d'une porte blindée ?"
                        orientation="horizontal"
                        options={[
                          {
                            label: "Oui",
                            nativeInputProps: {
                              checked: field.state.value === true,
                              onChange: (e) => field.setValue(true),
                            },
                          },
                          {
                            label: "Non",
                            nativeInputProps: {
                              checked: field.state.value === false,
                              onChange: (e) => field.setValue(false),
                            },
                          },
                        ]}
                      />
                    );
                  }}
                />
              </div>
            </section>
          )}

        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttons={[
            {
              disabled: false, // TODO : désactiver pendant la sauvegarde
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
