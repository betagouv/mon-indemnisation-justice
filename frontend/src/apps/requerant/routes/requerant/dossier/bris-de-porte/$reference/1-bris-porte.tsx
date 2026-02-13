import { SelectionCivilite } from "@/apps/requerant/composants/SelectionCivilite";
import { container } from "@/apps/requerant/container";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useNavigate,
} from "@tanstack/react-router";
import { instanceToPlain } from "class-transformer";
import React, { KeyboardEvent } from "react";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {
  Civilite,
  Dossier,
  type QualiteRequerant,
  QualiteRequerants,
} from "@/apps/requerant/models";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { useForm } from "@tanstack/react-form";
import { SchemaValidationEtatCivil } from "@/apps/requerant/formulaires/brisDePorte/EtatCivil.schema.ts";
import { useInjection } from "inversify-react";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/components/Loader.tsx";
import { ErreurResourceInconnue } from "@/apps/requerant/routeur";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { Input } from "@codegouvfr/react-dsfr/Input";

const DossierInconnu = ({
  data: { titre, message },
}: {
  data: { titre: string; message: string };
}) => {
  console.log({ titre, message });

  return (
    <div className="fr-grid-row fr-grid-row--gutters">
      <div className="fr-col-12">
        <Alert severity={"error"} title={titre} description={message} />
      </div>
    </div>
  );
};

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/1-bris-porte",
)({
  component: Etape1EtatCivil,
  pendingComponent: Loader,
  notFoundComponent: (props: NotFoundRouteProps) => {
    const { titre, message } = props.data as ErreurResourceInconnue;
    return (
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-12">
          <Alert
            severity={"error"}
            title={titre ?? "Page introuvable"}
            description={message}
          />
        </div>
      </div>
    );
  },
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

function Etape1EtatCivil() {
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
        to: "../2-bris-de-porte",
        search: {} as any,
      });
    },
  });

  return (
    <>
      <h1>Déclarer un bris de porte</h1>

      <section>
        <Stepper
          currentStep={1}
          stepCount={3}
          title={"Informations relatives au bris de porte"}
          nextTitle={"Données personnelles"}
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
        <section>
          <div className="fr-grid-row">
            <div className="fr-col-12">
              <formulaire.Field
                name="requerant.estPersonneMorale"
                children={(field) => {
                  return (
                    <RadioButtons
                      orientation="horizontal"
                      legend="Je suis"
                      options={[
                        {
                          label: "Une personne physique",
                          nativeInputProps: {
                            checked: field.state.value == false,
                            onChange: () => {
                              field.setValue(false);
                            },
                          },
                        },
                        {
                          label: "Une personne morale",
                          nativeInputProps: {
                            checked: field.state.value == true,
                            onChange: () => {
                              field.setValue(true);
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
                            name="description"
                            children={(field) => {
                              return (
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
                                      }
                                    },
                                  }}
                                >
                                  <option value="" disabled hidden>
                                    Selectionnez une option
                                  </option>
                                  {Object.entries(QualiteRequerants).map(
                                    ([key, label]) => (
                                      <option key={key} value={key}>
                                        {label}
                                      </option>
                                    ),
                                  )}
                                </Select>
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
                                    name="description"
                                    children={(field) => {
                                      return (
                                        <Input
                                          label="Précisez"
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
          ]}
          children={([estPersonneMorale, rapportAuLogement]) => (
            <>
              {estPersonneMorale !== undefined &&
                rapportAuLogement !== undefined && (
                  <section>
                    <TitreSection>
                      Informations sur le bris de porte
                    </TitreSection>

                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-12">
                        <formulaire.Field
                          name="description"
                          children={(field) => {
                            return (
                              <Input
                                label="Décrivez-nous l’intervention"
                                textArea
                                nativeTextAreaProps={{
                                  placeholder:
                                    "Intervention survenue ce matin ...",
                                  onChange: (e) => console.log(e.target.value),
                                  rows: 10,
                                  cols: 50,
                                }}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>
                    <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-lg-4 fr-col-offset-8 fr-col-12">
                        <formulaire.Field
                          name="description"
                          children={(field) => {
                            return (
                              <Input
                                label="Date de l'opération de police judiciaire"
                                nativeInputProps={{
                                  type: "date",
                                  onChange: (e) => console.log(e.target.value),
                                }}
                              />
                            );
                          }}
                        />
                      </div>
                    </div>

                    <div className="fr-col-12">
                      <div className="fr-grid-row fr-grid-row--gutters">
                        <div className="fr-col-6">
                          <formulaire.Field
                            name="description"
                            children={(field) => {
                              return (
                                <Input
                                  label="Adresse du logement concerné par le bris de porte"
                                  nativeInputProps={{
                                    onChange: (e) =>
                                      console.log(e.target.value),
                                    maxLength: 255,
                                  }}
                                />
                              );
                            }}
                          />
                        </div>
                        <div className="fr-col-12">
                          <formulaire.Field
                            name="description"
                            children={(field) => {
                              return (
                                <Input
                                  label="Complément d'adresse"
                                  hintText={"Facultatif"}
                                  nativeInputProps={{
                                    onChange: (e) =>
                                      console.log(e.target.value),
                                    maxLength: 255,
                                  }}
                                />
                              );
                            }}
                          />
                        </div>
                        <div className="fr-col-lg-2 fr-col-4">
                          <formulaire.Field
                            name="description"
                            children={(field) => {
                              return (
                                <Input
                                  label="Code postal"
                                  nativeInputProps={{
                                    onChange: (e) =>
                                      console.log(e.target.value),
                                    maxLength: 5,
                                  }}
                                />
                              );
                            }}
                          />
                        </div>
                        <div className="fr-col-lg-10 fr-col-8">
                          <formulaire.Field
                            name="description"
                            children={(field) => {
                              return (
                                <Input
                                  label="Ville"
                                  nativeInputProps={{
                                    onChange: (e) =>
                                      console.log(e.target.value),
                                    maxLength: 255,
                                  }}
                                />
                              );
                            }}
                          />
                        </div>
                      </div>
                    </div>
                    <div className="fr-col-12">
                      <formulaire.Field
                        name="description"
                        children={(field) => {
                          return (
                            <RadioButtons
                              legend="S'agit-il d'une porte blindée ?"
                              orientation="horizontal"
                              options={[
                                {
                                  label: "Oui",
                                  nativeInputProps: {
                                    onChange: (e) =>
                                      console.log(e.target.checked),
                                  },
                                },
                                {
                                  label: "Non",
                                  nativeInputProps: {
                                    checked: true,
                                    onChange: (e) =>
                                      console.log(e.target.checked),
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
                    disabled: estPersonneMorale === undefined,
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
