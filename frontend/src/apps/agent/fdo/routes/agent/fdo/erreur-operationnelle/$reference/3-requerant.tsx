import { useForm } from "@tanstack/react-form";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React, { ChangeEvent, useState } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { z } from "zod";
import {
  Civilite,
  DeclarationErreurOperationnelle,
} from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";
import { container } from "@/apps/agent/fdo/_init/_container.ts";
import { router } from "@/apps/agent/fdo/_init/_router.ts";
import { useInjection } from "inversify-react";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";

export const Route = createFileRoute(
  "/agent/fdo/erreur-operationnelle/$reference/3-requerant",
)({
  beforeLoad: ({ params }) => {
    if (
      !container
        .get(DeclarationManagerInterface.$)
        .aDeclaration(params.reference)
    ) {
      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/mes-declarations",
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
    declaration: DeclarationErreurOperationnelle;
    reference: string;
  }> => {
    const declaration = (await container
      .get(DeclarationManagerInterface.$)
      .getDeclaration(params.reference)) as DeclarationErreurOperationnelle;

    console.dir(declaration);

    if (!declaration) {
      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/mes-declarations",
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

const schemaRequerant = z.object({
  enPresenceRequerant: z.boolean({
    error: "Précisez si vous disposez des coordonnées du requérant",
  }),
  infosRequerant: z.object({
    civilite: z.custom<Civilite>((c) => c instanceof Civilite, {
      error: "La civilité du requérant est requise",
    }),
    nom: z.string().trim().min(1, { error: "Le nom du requérant est requis" }),
    prenom: z
      .string()
      .trim()
      .min(1, { error: "Le prénom du requérant est requis" }),
    telephone: z
      .string()
      .min(7, { error: "Le numéro de téléphone est requis" }),
    courriel: z.email({ error: "L'adresse courriel est requise" }),
    message: z.string(),
  }),
});

function Page() {
  const {
    declaration,
    reference,
  }: { declaration: DeclarationErreurOperationnelle; reference: string } =
    Route.useLoaderData();

  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

  const [declarationEnPresenceRequerant, setDeclarationEnPresenceRequerant] =
    useState<boolean | undefined>(declaration.enPresenceRequerant);

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);

  const form = useForm({
    defaultValues: {
      enPresenceRequerant: declaration.enPresenceRequerant,
      infosRequerant: declaration.infosRequerant,
    },
    listeners: {
      onChange: async ({ formApi }) => {
        await declarationManager.enregistrer(declaration, formApi.state.values);
      },
      onChangeDebounceMs: 500,
    },
    validators: {
      onSubmit: schemaRequerant,
    },
    onSubmit: async () => {
      console.log("La");
      setSauvegardeEnCours(true);
      try {
        await declarationManager.soumettre(declaration);
        naviguer({
          to: "/agent/fdo/erreur-operationnelle/mes-declarations",
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
        rowGap: "1vh",
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
      <h1>Nouvelle déclaration d'erreur opérationnelle</h1>

      <Stepper
        currentStep={3}
        stepCount={3}
        title="Informations du requérant"
      />

      <div className="fr-grid-row">
        <h6 className="fr-m-0 fr-text-label--blue-france">
          Civilité et contact de l'usager
        </h6>
      </div>

      {declarationEnPresenceRequerant === undefined && (
        <div className="fr-grid-row">
          <Alert
            severity="info"
            title="La civilité de l’usager n’est pas obligatoire pour enregistrer un dossier."
            description="Si l’usager se présente, merci de remplir ces informations a posteriori afin de permettre l’instruction de dossier. "
          />
        </div>
      )}

      <div className="fr-grid-row">
        <form.Field
          name="enPresenceRequerant"
          children={(field) => {
            return (
              <RadioButtons
                className="fr-col-12"
                legend="J’ai les coordonnées de l’usager:"
                orientation="horizontal"
                disabled={declaration.estSauvegarde()}
                options={[
                  {
                    label: "Oui",
                    nativeInputProps: {
                      defaultChecked: declarationEnPresenceRequerant === true,
                      onChange: (event: ChangeEvent<HTMLInputElement>) => {
                        setDeclarationEnPresenceRequerant(event.target.checked);
                        field.handleChange(event.target.checked);
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
        <div
          className="fr-grid-row fr-grid-row--gutters"
          style={{ alignItems: "baseline" }}
        >
          <form.Field
            name="infosRequerant.civilite"
            children={(field) => {
              return (
                <Select
                  className="fr-col-lg-2 fr-m-0"
                  label="Civilité *"
                  disabled={sauvegardeEnCours || declaration.estSauvegarde()}
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
            name="infosRequerant.nom"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0"
                  label="Nom *"
                  disabled={sauvegardeEnCours || declaration.estSauvegarde()}
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
            name="infosRequerant.prenom"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0"
                  label="Prénom *"
                  disabled={sauvegardeEnCours || declaration.estSauvegarde()}
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
            name="infosRequerant.telephone"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-m-0"
                  label="Téléphone *"
                  disabled={sauvegardeEnCours || declaration.estSauvegarde()}
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
            name="infosRequerant.courriel"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-m-0"
                  label="Courriel *"
                  disabled={sauvegardeEnCours || declaration.estSauvegarde()}
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

          <form.Field
            name="infosRequerant.message"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-12 fr-m-0"
                  label="Précisions concernant le requérant"
                  disabled={sauvegardeEnCours || declaration.estSauvegarde()}
                  textArea
                  //hintText="Un exemple"
                  nativeTextAreaProps={{
                    placeholder:
                      "Conjoint du locataire, propriétaire bailleur...",
                    rows: 6,
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
        </div>
      )}

      <div className="fr-grid-row fr-grid-row--gutters">
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
                  to: "/agent/fdo/erreur-operationnelle/$reference/2-complement",
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
                    disabled: sauvegardeEnCours || declaration.estSauvegarde(),
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
                        to: "/agent/fdo/erreur-operationnelle/mes-declarations",
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
