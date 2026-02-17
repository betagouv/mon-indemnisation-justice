import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant";
import { container } from "@/apps/requerant/container";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  redirect,
  useNavigate,
} from "@tanstack/react-router";
import { instanceToPlain } from "class-transformer";
import React, { useId } from "react";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {
  Dossier,
  getListeRapportAuLogement,
  getRapportAuLogementLibelle,
  RapportAuLogement,
} from "@/apps/requerant/models";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { useForm } from "@tanstack/react-form";
import { useInjection } from "inversify-react";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/components/Loader.tsx";
import {
  ErreurResourceInconnue,
  RouteurRequerant,
} from "@/apps/requerant/routeur";
import { Input } from "@codegouvfr/react-dsfr/Input";
import classes from "@/apps/requerant/style/form.module.css";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { FormRadioButtons } from "@/apps/requerant/composants/champs/form/FormRadioButtons.tsx";
import { FormSelect } from "@/apps/requerant/composants/champs/form/FormSelect.tsx";

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
      console.log("Not found");
      throw notFound({
        data: {
          titre: `Impossible de trouver le dossier ${params.reference}`,
          message: "Le dossier n'existe pas ou ne vous est pas accessible.",
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
    defaultValues: instanceToPlain(dossier) as Partial<Dossier>,
    listeners: {
      onChangeDebounceMs: 500,
      onChange: async ({ formApi }) => {
        await dossierManager.modifierDossier(reference, formApi.state.values);
      },
    },
    onSubmit: async ({ value, formApi }) => {
      await naviguer({
        to: "../2-infos-requerant",
        search: {} as any,
      });
    },
  });

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
                name="requerant.estPersonneMorale"
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

          <formulaire.Subscribe
            selector={(state) => state.values.requerant?.estPersonneMorale}
            children={(estPersonneMorale) => (
              <>
                {estPersonneMorale !== undefined && (
                  <>
                    <div className="fr-col-12">
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
                                          event.target
                                            .value as RapportAuLogement,
                                        );
                                      }
                                    },
                                  }}
                                >
                                  <option value="" disabled hidden>
                                    Selectionnez une option
                                  </option>
                                  {getListeRapportAuLogement(
                                    estPersonneMorale,
                                  ).map((rapportAuLogement) => (
                                    <option
                                      key={rapportAuLogement}
                                      value={rapportAuLogement}
                                    >
                                      {getRapportAuLogementLibelle(
                                        rapportAuLogement,
                                      )}
                                    </option>
                                  ))}
                                </FormSelect>
                              );
                            }}
                          />
                        </div>

                        <formulaire.Subscribe
                          selector={(state) => state.values.rapportAuLogement}
                          children={(rapportAuLogement) => (
                            <>
                              {rapportAuLogement === "AUT" && (
                                <div className="fr-col-lg-6 fr-col-12">
                                  <formulaire.Field
                                    name="descriptionRapportAuLogement"
                                    children={(field) => {
                                      return (
                                        <FormInput
                                          label="Précisez"
                                          estRequis={
                                            rapportAuLogement === "AUT"
                                          }
                                          nativeInputProps={{
                                            onChange: (e) =>
                                              field.setValue(e.target.value),
                                            maxLength: 255,
                                          }}
                                        />
                                      );
                                    }}
                                  />
                                </div>
                              )}
                            </>
                          )}
                        />
                      </div>
                    </div>
                  </>
                )}
              </>
            )}
          />
        </section>

        <formulaire.Subscribe
          selector={(state) => [
            state.values.requerant?.estPersonneMorale,
            state.values.rapportAuLogement,
            state.values.descriptionRapportAuLogement,
          ]}
          children={([
            estPersonneMorale,
            rapportAuLogement,
            descriptionRapportAuLogement,
          ]) => (
            <>
              {estPersonneMorale !== undefined &&
                rapportAuLogement !== undefined &&
                (rapportAuLogement !== "AUT" ||
                  !!descriptionRapportAuLogement) && (
                  <section>
                    <TitreSection>
                      Informations sur le bris de porte
                    </TitreSection>

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
                                  placeholder:
                                    "Intervention survenue ce matin ...",
                                  rows: 10,
                                  cols: 50,
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

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-12 fr-col-lg-6">
                        <formulaire.Field
                          name="requerant.adresse.ligne1"
                          children={(field) => {
                            return (
                              <FormInput
                                label="Adresse du logement concerné par le bris de porte"
                                nativeInputProps={{
                                  maxLength: 255,
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
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
                                  onChange: (e) =>
                                    field.setValue(e.target.value),
                                }}
                                champ={field}
                              />
                            );
                          }}
                        />
                      </div>
                      <div className="fr-col-lg-2 fr-col-4">
                        <formulaire.Field
                          name="adresse.commune.codePostal"
                          children={(field) => {
                            return (
                              <>
                                <FormInput
                                  label="Code postal"
                                  nativeInputProps={{
                                    onChange: (e) =>
                                      field.setValue(e.target.value),
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
                          name="adresse.commune.nom"
                          children={(field) => {
                            return (
                              <>
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
                              </>
                            );
                          }}
                        />
                      </div>
                    </div>
                    <div className="fr-col-12">
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
                                    checked: field.state.value === false,
                                    onChange: (e) => field.setValue(false),
                                  },
                                },
                                {
                                  label: "Non",
                                  nativeInputProps: {
                                    checked: field.state.value === true,
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
            </>
          )}
        />
      </form>
    </>
  );
}
