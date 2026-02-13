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
import { Input } from "@codegouvfr/react-dsfr/Input";
import { Civilite, Dossier, Requerant } from "@/apps/requerant/models";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { useForm } from "@tanstack/react-form";
import { SchemaValidationEtatCivil } from "@/apps/requerant/formulaires/brisDePorte/EtatCivil.schema.ts";
import { useInjection } from "inversify-react";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/components/Loader.tsx";
import { ErreurResourceInconnue } from "@/apps/requerant/routeur";
import { CheckInput } from "@/apps/requerant/composants/champs/check/CheckInput.tsx";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";

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
      onSubmit: SchemaValidationEtatCivil,
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
          title={"Données personnelles"}
          nextTitle={"Informations relatives au bris de porte"}
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
                      state={!field.state.meta.isValid ? "error" : "default"}
                      stateRelatedMessage={
                        !field.state.meta.isValid ? (
                          <>{field.state.meta.errors.at(0)?.message}</>
                        ) : (
                          <></>
                        )
                      }
                    />
                  );
                }}
              />
            </div>
          </div>
        </section>
        <formulaire.Subscribe
          selector={(state) => state.values.requerant?.estPersonneMorale}
          children={(estPersonneMorale) => (
            <>
              {estPersonneMorale == true && (
                <>
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
                      <section className="pr-form-section fr-py-2w">
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
                </>
              )}
              {estPersonneMorale == false && (
                <>
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
                                    state={
                                      !field.state.meta.isValid
                                        ? "error"
                                        : "default"
                                    }
                                    stateRelatedMessage={
                                      !field.state.meta.isValid ? (
                                        <>
                                          {
                                            field.state.meta.errors.at(0)
                                              ?.message
                                          }
                                        </>
                                      ) : (
                                        <></>
                                      )
                                    }
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
                                            state={
                                              !field.state.meta.isValid
                                                ? "error"
                                                : "default"
                                            }
                                            stateRelatedMessage={
                                              !field.state.meta.isValid ? (
                                                <>
                                                  {
                                                    field.state.meta.errors.at(
                                                      0,
                                                    )?.message
                                                  }
                                                </>
                                              ) : (
                                                <></>
                                              )
                                            }
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
                </>
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
