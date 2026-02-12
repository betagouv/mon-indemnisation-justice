import { container } from "@/apps/requerant/container";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { createFileRoute, notFound, useNavigate } from "@tanstack/react-router";
import React, { FormEventHandler, KeyboardEvent } from "react";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { ChampCivilite } from "@/apps/requerant/dossier/components/Civilite.tsx";
import { Dossier } from "@/apps/requerant/models";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { TitreSection } from "@/apps/requerant/composants/TitreSection.tsx";
import { useForm } from "@tanstack/react-form";
import { SchemaValidationEtatCivil } from "@/apps/requerant/formulaires/brisDePorte/EtatCivil.schema.ts";
import { useInjection } from "inversify-react";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { instanceToPlain } from "class-transformer";
import { Loader } from "@/common/components/Loader.tsx";

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
  "/requerant/demande/bris-de-porte/$id/1-etat-civil",
)({
  component: Etape1EtatCivil,
  pendingComponent: Loader,
  notFoundComponent: DossierInconnu,
  loader: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .aDossier(params.id);

    if (!dossier) {
      console.log("Not found");
      throw notFound({
        data: {
          titre: `Impossible de trouver le dossier ${params.id}`,
          message: "Le dossier n'existe pas ou ne vous est pas accessible.",
        },
        throw: true,
      });
    }

    return { reference: params.id, dossier };
  },
});

function Etape1EtatCivil() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Récupérer la référence depuis le paramètre de la route
  const { reference, dossier }: { reference: string; dossier: Dossier } =
    Route.useParams();

  const formulaire = useForm({
    canSubmitWhenInvalid: true,
    validators: {
      onSubmit: SchemaValidationEtatCivil,
      // TODO retirer à la fin du développement
      //onChange: SchemaValidationEtatCivil,
    },
    defaultValues: dossier ? instanceToPlain(dossier) : {},
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
                      legend="Votre demande d'indemnisation concerne une personne morale (société, entreprise, association, fondation etc.)"
                      options={[
                        {
                          label: "Oui",
                          nativeInputProps: {
                            checked: field.getValue() == true,
                            onChange: () => {
                              field.setValue(true);
                            },
                          },
                        },
                        {
                          label: "Non",
                          nativeInputProps: {
                            checked: field.getValue() == false,
                            onChange: () => {
                              field.setValue(false);
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
                                  <Input
                                    label="Raison sociale"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
                                  <Input
                                    label="SIREN / SIRET"
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
                        </div>

                        <div className="fr-grid-row fr-grid-row--gutters">
                          <div className="fr-col-lg-6 fr-col-12">
                            <formulaire.Field
                              name="requerant.adresse.ligne1"
                              children={(field) => {
                                return (
                                  <Input
                                    label="Adresse"
                                    nativeInputProps={{
                                      placeholder: "Numéro de voie, rue",
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
                                    }}
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
                                  <Input
                                    label="Complément d'adresse"
                                    nativeInputProps={{
                                      placeholder: "Étage, escalier",
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
                                    }}
                                  />
                                );
                              }}
                            />
                          </div>

                          <div className="fr-col-lg-2 fr-col-4">
                            <formulaire.Field
                              name="requerant.adresse.codePostal"
                              children={(field) => {
                                return (
                                  <Input
                                    label="Code postal"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 5,
                                    }}
                                  />
                                );
                              }}
                            />
                          </div>
                          <div className="fr-col-lg-10 fr-col-8">
                            <formulaire.Field
                              name="requerant.adresse.commune"
                              children={(field) => {
                                return (
                                  <Input
                                    label="Ville"
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
                                  <ChampCivilite
                                    civilite={undefined}
                                    setCivilite={(civilite) =>
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
                                  <Input
                                    label="Prénom(s)"
                                    nativeInputProps={{
                                      placeholder: "Premier prénom",
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                    }}
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
                                  <Input
                                    label="Nom de naissance"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                    }}
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
                                  <Input
                                    label="Nom d'usage"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                    }}
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
                                  <Input
                                    label="Courriel professionnel"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                    }}
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
                                  <Input
                                    label="Numéro de téléphone professionnel"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                    }}
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
                                  <ChampCivilite
                                    civilite={field.getValue()}
                                    setCivilite={(civilite) =>
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
                                  <Input
                                    label="Prénom(s)"
                                    nativeInputProps={{
                                      placeholder: "Prénom(s)",
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
                                  <Input
                                    label="Nom d'usage"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
                                  <Input
                                    label="Nom de naissance"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
                                  <Input
                                    label="Adresse courriel"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      disabled: true,
                                      maxLength: 255,
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
                                  <Input
                                    label="Téléphone"
                                    nativeInputProps={{
                                      type: "tel",
                                      pattern: "(0,+){1}[0-9]{8,}",
                                      onChange: (e) =>
                                        field.handleChange(e.target.value),
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
                                  <Input
                                    label="Date de naissance"
                                    nativeInputProps={{
                                      type: "date",
                                      onChange: (e) =>
                                        field.handleChange(
                                          new Date(e.target.value),
                                        ),
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
                                        field.setValue(e.target.value),
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
                            name="requerant.communeNaissance"
                            children={(field) => {
                              return (
                                <>
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
                                    />
                                  </div>
                                  <div className="fr-col-lg-4 fr-col-8">
                                    <formulaire.Field
                                      name="requerant.adresse.commune"
                                      children={(field) => {
                                        return (
                                          <Select
                                            disabled={true}
                                            label="Ville de naissance"
                                            nativeSelectProps={{
                                              onChange: (e) =>
                                                field.setValue(e.target.value),
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
                                  <Input
                                    label="Adresse"
                                    nativeInputProps={{
                                      placeholder: "Numéro de voie, rue",
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
                                  <Input
                                    label="Complément d'adresse (facultatif)"
                                    nativeInputProps={{
                                      placeholder: "Étage, escalier",
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
                                  />
                                );
                              }}
                            />
                          </div>
                          <div className="fr-col-lg-2 fr-col-4">
                            <formulaire.Field
                              name="requerant.adresse.codePostal"
                              children={(field) => {
                                return (
                                  <Input
                                    label="Code postal"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 5,
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
                                  />
                                );
                              }}
                            />
                          </div>
                          <div className="fr-col-lg-10 fr-col-8">
                            <formulaire.Field
                              name="requerant.adresse.commune"
                              children={(field) => {
                                return (
                                  <Input
                                    label="Ville"
                                    nativeInputProps={{
                                      onChange: (e) =>
                                        field.setValue(e.target.value),
                                      maxLength: 255,
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
