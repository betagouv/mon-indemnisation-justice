import { useForm } from "@tanstack/react-form";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { z } from "zod";
import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";
import {
  container,
  DeclarationManagerInterface,
} from "@/apps/agent/fdo/services";
import { router } from "@/apps/agent/fdo/router.ts";
import { useInjection } from "inversify-react";
import { instanceToPlain, plainToClassFromExist } from "class-transformer";

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
    return {
      reference: params.reference,
      declaration: (await container
        .get(DeclarationManagerInterface.$)
        .getDeclaration(params.reference)) as DeclarationErreurOperationnelle,
    };
  },
  component: Page,
});

const schemaRequerant = z.object({
  requerant: z.object({
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

  const form = useForm({
    defaultValues: {
      requerant: declaration.requerant
        ? { ...declaration.requerant }
        : {
            nom: "",
            prenom: "",
            telephone: "",
            courriel: "",
            message: "",
          },
    },
    listeners: {
      onSubmit: ({ formApi }) => {
        console.log(formApi.getAllErrors());
      },
      onChange: async ({ fieldApi, formApi }) => {
        declarationManager.enregistrer(
          plainToClassFromExist(declaration, formApi.state.values),
        );
      },
      onChangeDebounceMs: 750,
    },
    validators: {
      onSubmit: schemaRequerant,
    },
    onSubmit: async () => {
      alert(JSON.stringify(instanceToPlain(declaration), null, 2));
    },
  });

  return (
    <div>
      <h1>Nouvelle déclaration d'erreur opérationnelle</h1>

      <Stepper
        currentStep={3}
        stepCount={3}
        title="Informations du requérant"
      />

      <form
        style={{
          display: "flex",
          flexDirection: "column",
          gap: "1.5vh",
        }}
        onSubmit={(e) => {
          e.preventDefault();
          e.stopPropagation();
          void form.handleSubmit();
        }}
      >
        <div
          className="fr-grid-row fr-grid-row--gutters fr-mt-2w"
          style={{ alignItems: "baseline" }}
        >
          <form.Field
            name="requerant.nom"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4"
                  label="Nom *"
                  nativeInputProps={{
                    type: "text",
                    placeholder: "DUPONT",
                    autoFocus: true,
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
            name="requerant.prenom"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4"
                  label="Prénom *"
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
            name="requerant.telephone"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4"
                  label="Téléphone *"
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
            name="requerant.courriel"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4"
                  label="Courriel *"
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
            name="requerant.message"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-12"
                  label="Commentaire à destination du dossier requérant"
                  textArea
                  nativeTextAreaProps={{
                    placeholder: "Bref descriptif de l’intervention...",
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

        <div className="fr-grid-row fr-grid-row--gutters fr-mt-2w">
          <ButtonsGroup
            className="fr-col-lg-12 fr-p-0"
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition={undefined}
            buttonsSize="small"
            buttons={[
              {
                children: "Revenir à l'étape précédente",
                priority: "secondary",
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
              {
                children: "Envoyer",
                priority: "primary",
                nativeButtonProps: {
                  type: "submit",
                  role: "submit",
                },
                className: "fr-mr-0",
              },
            ]}
          />
        </div>
      </form>
    </div>
  );
}
