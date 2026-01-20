import { useForm } from "@tanstack/react-form";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React, { ChangeEvent, useState } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { z } from "zod";
import {
  Civilite,
  CoordonneesRequerant,
  DeclarationFDOBrisPorte,
} from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { container } from "@/apps/agent/fdo/_init/_container.ts";
import { router } from "@/apps/agent/fdo/_init/_router.ts";
import { useInjection } from "inversify-react";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";

export const Route = createFileRoute(
  "/agent/fdo/bris-de-porte/$reference/3-usager",
)({
  beforeLoad: ({ params }) => {
    if (
      !container
        .get(DeclarationManagerInterface.$)
        .aDeclaration(params.reference)
    ) {
      throw redirect({
        to: "/agent/fdo/bris-de-porte/mes-declarations",
        replace: true,
        params,
      });
    }
  },
  loader: async ({
    params,
  }: {
    params: any;
  }): Promise<{
    declaration: DeclarationFDOBrisPorte;
    reference: string;
  }> => {
    const declaration = (await container
      .get(DeclarationManagerInterface.$)
      .getDeclaration(params.reference)) as DeclarationFDOBrisPorte;

    if (!declaration) {
      throw redirect({
        to: "/agent/fdo/bris-de-porte/mes-declarations",
        replace: true,
        params,
      });
    }

    return {
      reference: params.reference,
      declaration,
    };
  },
  component: Page,
});

const schemaRequerant = z.discriminatedUnion("enPresenceRequerant", [
  z.object({
    enPresenceRequerant: z.literal(false, {
      error: "Indiquez-nous si vous disposez des coordonnées du requérant",
    }),
    precisionsRequerant: z.any(),
    coordonneesRequerant: z.undefined(),
  }),
  z.object({
    enPresenceRequerant: z.literal(true, {
      error: "Indiquez-nous si vous disposez des coordonnées du requérant",
    }),
    precisionsRequerant: z.any(),
    coordonneesRequerant: z.object({
      civilite: z.custom<Civilite>((c) => c instanceof Civilite, {
        error: "La civilité du requérant est requise",
      }),
      nom: z
        .string({ error: "Le nom du requérant est requis" })
        .trim()
        .min(1, { error: "Le nom du requérant est requis" }),
      prenom: z
        .string({ error: "Le prénom du requérant est requis" })
        .trim()
        .min(1, { error: "Le prénom du requérant est requis" }),
      telephone: z
        .string({ error: "Le numéro de téléphone est requis" })
        .min(7, { error: "Le numéro de téléphone est requis" }),
      courriel: z.email({ error: "L'adresse courriel est requise" }),
    }),
  }),
]);

function Page() {
  const {
    declaration,
    reference,
  }: { declaration: DeclarationFDOBrisPorte; reference: string } =
    Route.useLoaderData();

  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

  const [declarationEnPresenceRequerant, setDeclarationEnPresenceRequerant] =
    useState<boolean | undefined>(declaration.enPresenceRequerant || undefined);

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);

  const form = useForm({
    defaultValues: {
      enPresenceRequerant: declaration.enPresenceRequerant,
      precisionsRequerant: declaration.precisionsRequerant,
      coordonneesRequerant: declaration.enPresenceRequerant
        ? (declaration.coordonneesRequerant ?? new CoordonneesRequerant())
        : undefined,
    },
    listeners: {
      onChange: async ({ formApi }) => {
        if (declaration.estBrouillon()) {
          declarationManager.mettreAJour(declaration, formApi.state.values);
        }
      },
      onChangeDebounceMs: 500,
    },
    validators: {
      onSubmit: schemaRequerant,
    },
    onSubmit: async ({ formApi }) => {
      setSauvegardeEnCours(true);
      try {
        if (declaration.estBrouillon()) {
          await declarationManager.enregistrer(declaration, {
            precisionsRequerant: formApi.state.values.precisionsRequerant,
            coordonneesRequerant: formApi.state.values.enPresenceRequerant
              ? formApi.state.values.coordonneesRequerant
              : undefined,
          });
        }

        await declarationManager.soumettre(declaration);
        await naviguer({
          to: "/agent/fdo/bris-de-porte/mes-declarations",
        });
      } catch (e) {
        alert(e);
      }
    },
  });

  return (
    <form
      style={{
        display: "flex",
        flexDirection: "column",
        gap: "2vh",
      }}
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
          await void form.handleSubmit();
        } catch (e) {
          console.error(e);
        }
      }}
    >
      <h1 className="fr-m-0">Nouvelle déclaration de bris de porte</h1>

      <Stepper
        className="fr-m-0"
        currentStep={3}
        stepCount={3}
        title="Informations concernant l'usager"
      />

      {/*
      <div className="fr-grid-row fr-m-0">
        <h6 className="fr-m-0 fr-text-label--blue-france">
          Civilité et contact de l'usager
        </h6>
      </div>
      */}

      {/*
      {declarationEnPresenceRequerant === undefined && (
        <div className="fr-grid-row">
          <Alert
            severity="info"
            title="La civilité de l’usager n’est pas obligatoire pour enregistrer un dossier."
            description="Si l’usager se présente, merci de remplir ces informations a posteriori afin de permettre l’instruction de dossier. "
          />
        </div>
      )}
      */}

      <div className="fr-grid-row">
        <form.Field
          name="enPresenceRequerant"
          children={(field) => {
            return (
              <RadioButtons
                className="fr-col-12 fr-m-0 fr-champ-requis"
                legend="J’ai les coordonnées de l’usager"
                orientation="horizontal"
                disabled={declaration.estSoumise()}
                state={!field.state.meta.isValid ? "error" : "default"}
                stateRelatedMessage={
                  !field.state.meta.isValid ? (
                    <>{field.state.meta.errors.at(0)?.message}</>
                  ) : (
                    <></>
                  )
                }
                options={[
                  {
                    label: "Oui",
                    nativeInputProps: {
                      defaultChecked: declarationEnPresenceRequerant === true,
                      onChange: (event: ChangeEvent<HTMLInputElement>) => {
                        setDeclarationEnPresenceRequerant(event.target.checked);
                        field.handleChange(event.target.checked);
                        if (!field.form.state.values.coordonneesRequerant) {
                          field.form.state.values.coordonneesRequerant =
                            new CoordonneesRequerant();
                        }
                      },
                    },
                  },
                  {
                    label: "Non",
                    nativeInputProps: {
                      defaultChecked: declarationEnPresenceRequerant === false,
                      onChange: (event: ChangeEvent<HTMLInputElement>) => {
                        setDeclarationEnPresenceRequerant(
                          !event.target.checked,
                        );
                        field.handleChange(!event.target.checked);
                      },
                    },
                  },
                ]}
              />
            );
          }}
        />
      </div>

      {declarationEnPresenceRequerant === true && (
        <div className="fr-grid-row fr-grid-row--gutters">
          <form.Field
            name="coordonneesRequerant.civilite"
            children={(field) => {
              return (
                <Select
                  className="fr-col-lg-2 fr-m-0 fr-champ-requis"
                  label="Civilité"
                  disabled={sauvegardeEnCours || declaration.estSoumise()}
                  nativeSelectProps={{
                    autoFocus: true,
                    value: field.state.value?.id ?? "",
                    onChange: (e) => {
                      field.handleChange(Civilite.from(e.target.value));
                    },
                  }}
                  state={!field.state.meta.isValid ? "error" : "default"}
                  stateRelatedMessage={
                    !field.state.meta.isValid ? (
                      <>{field.state.meta.errors.at(0)?.message}</>
                    ) : (
                      <></>
                    )
                  }
                >
                  <option value="" disabled hidden></option>
                  {Civilite.liste().map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.libelle}
                    </option>
                  ))}
                </Select>
              );
            }}
          />

          <form.Field
            name="coordonneesRequerant.nom"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0 fr-champ-requis"
                  label="Nom"
                  disabled={sauvegardeEnCours || declaration.estSoumise()}
                  nativeInputProps={{
                    type: "text",
                    placeholder: "DUPONT",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
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

          <form.Field
            name="coordonneesRequerant.prenom"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0 fr-champ-requis"
                  label="Prénom"
                  disabled={sauvegardeEnCours || declaration.estSoumise()}
                  nativeInputProps={{
                    type: "text",
                    placeholder: "Martin",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
                  state={!field.state.meta.isValid ? "error" : "default"}
                  stateRelatedMessage={
                    !field.state.meta.isValid ? (
                      <>{field.state.meta.errors.at(0)?.message}</>
                    ) : (
                      <>" "</>
                    )
                  }
                />
              );
            }}
          />

          <form.Field
            name="coordonneesRequerant.telephone"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-m-0 fr-champ-requis"
                  label="Téléphone"
                  disabled={sauvegardeEnCours || declaration.estSoumise()}
                  nativeInputProps={{
                    type: "text",
                    placeholder: "06 00 00 00 00",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
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

          <form.Field
            name="coordonneesRequerant.courriel"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-m-0 fr-champ-requis"
                  label="Courriel"
                  disabled={sauvegardeEnCours || declaration.estSoumise()}
                  nativeInputProps={{
                    type: "text",
                    placeholder: "martin.dupont@courriel.fr",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
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

          {/*
          <Checkbox
            className="fr-col-lg-8"
            options={[
              {
                label: "Le requérant n’a pas d’adresse courriel",
                nativeInputProps: {},
              },
            ]}
          />
          */}
        </div>
      )}

      {declarationEnPresenceRequerant !== undefined && (
        <div className="fr-grid-row">
          <form.Field
            name="precisionsRequerant"
            children={(field) => {
              return (
                <Input
                  label={
                    declarationEnPresenceRequerant
                      ? "Précisions concernant l'usager"
                      : "Précisions"
                  }
                  className="fr-col-lg-12"
                  textArea={true}
                  disabled={sauvegardeEnCours || declaration.estSoumise()}
                  nativeTextAreaProps={{
                    defaultValue: declaration.precisionsRequerant ?? "",
                    onChange: (e) => field.handleChange(e.target.value),
                    rows: 5,
                    placeholder: declarationEnPresenceRequerant
                      ? "Locataire et épouse du mis en cause"
                      : "Le logement était inoccupé",
                  }}
                />
              );
            }}
          />
        </div>
      )}

      <div className="fr-grid-row fr-grid-row--gutters fr-mt-1v">
        <ButtonsGroup
          className="fr-col-lg-12 fr-p-0"
          inlineLayoutWhen="always"
          alignment="right"
          buttonsSize="small"
          buttons={[
            {
              children: "Revenir à l'étape précédente",
              disabled: sauvegardeEnCours,
              priority: sauvegardeEnCours ? "tertiary" : "secondary",
              iconId: "fr-icon-arrow-left-line",
              iconPosition: "left",
              onClick: () =>
                naviguer({
                  to: "/agent/fdo/bris-de-porte/$reference/2-service-enqueteur",
                  params: {
                    reference,
                  } as any,
                }),
            },
            ...(declaration.estBrouillon()
              ? [
                  {
                    children: sauvegardeEnCours
                      ? "Sauvegarde en cours..."
                      : "Envoyer",
                    disabled: sauvegardeEnCours || declaration.estSoumise(),
                    priority: sauvegardeEnCours ? "tertiary" : "primary",
                    nativeButtonProps: {
                      type: "submit",
                      role: "submit",
                    },
                    className: "fr-mr-0",
                  } as ButtonProps,
                ]
              : [
                  {
                    children: "Mes déclarations",
                    priority: "secondary",
                    nativeButtonProps: {
                      type: "button",
                      role: "link",
                    },
                    className: "fr-mr-0",
                    onClick: () =>
                      naviguer({
                        to: "/agent/fdo/bris-de-porte/mes-declarations",
                        params: {
                          reference,
                        } as any,
                      }),
                  } as ButtonProps,
                ]),
          ]}
        />
      </div>
    </form>
  );
}
