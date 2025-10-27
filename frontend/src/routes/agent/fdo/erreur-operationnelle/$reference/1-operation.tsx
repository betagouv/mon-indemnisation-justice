import { dateChiffre } from "@/common/services/date";
import { useForm } from "@tanstack/react-form";
import React from "react";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Input } from "@codegouvfr/react-dsfr/Input";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { z } from "zod";
import "@/style/index.css";
import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";
import {
  container,
  DeclarationManagerInterface,
} from "@/apps/agent/fdo/services";
import { useInjection } from "inversify-react";
import { plainToClassFromExist } from "class-transformer";
import { router } from "@/apps/agent/fdo/router.ts";

export const Route = createFileRoute(
  "/agent/fdo/erreur-operationnelle/$reference/1-operation",
)({
  beforeLoad: async ({ params }) => {
    if (
      !(await container
        .get(DeclarationManagerInterface.$)
        .aDeclaration(params.reference))
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
  component: () => <Page />,
});

const schemaErreurOperationnelle = z.object({
  dateOperation: z
    .date()
    .max(new Date(+new Date().setHours(23, 59, 59, 9999)), {
      error: "L'opération ne peut avoir lieu dans le futur",
    }),
  adresse: z.object({
    ligne1: z
      .string()
      .trim()
      .min(1, { error: "L'adresse du logement est requise" }),
    ligne2: z.string(),
    codePostal: z
      .string()
      .regex(/\d{5}/, { error: "Le code postal doit réunir 5 chiffres" }),
    localite: z.string().trim().min(1, { error: "La ville est requise" }),
  }),
});

const Page = () => {
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
      dateOperation: declaration.dateOperation ?? new Date(),
      adresse: declaration.adresse
        ? { ...declaration.adresse }
        : {
            ligne1: "",
            ligne2: "",
            codePostal: "",
            localite: "",
          },
    },
    listeners: {
      onChange: async ({ fieldApi, formApi }) => {
        declarationManager.enregistrer(
          plainToClassFromExist(declaration, formApi.state.values),
        );
      },
      onChangeDebounceMs: 750,
    },
    validators: {
      onSubmit: schemaErreurOperationnelle,
    },
    onSubmit: async ({ value }) => {
      console.log(value);
      await naviguer({
        to: "/agent/fdo/erreur-operationnelle/$reference/2-complement",
        params: { reference } as any,
      });
    },
  });

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "1.5vh" }}>
      <h1>Nouvelle déclaration d'erreur opérationnelle</h1>

      <Stepper
        currentStep={1}
        stepCount={3}
        title="Eléments relatifs à l’erreur opérationnelle"
        nextTitle="Complément d’information pour le Ministère de la Justice "
      />

      <form
        onSubmit={(e) => {
          e.preventDefault();
          e.stopPropagation();
          void form.handleSubmit();
        }}
      >
        <div className="fr-grid-row">
          <h6 className="fr-m-0 fr-text-label--blue-france">
            Informations concernant le logement perquisitionné par erreur{" "}
          </h6>
        </div>

        <div className="fr-grid-row fr-grid-row--gutters fr-px-0 fr-my-2w">
          <form.Field
            name="dateOperation"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-my-0"
                  label="Date de l'opération *"
                  nativeInputProps={{
                    value: dateChiffre(field.state.value),
                    onChange: (e) =>
                      field.handleChange(new Date(e.target.value)),
                    type: "date",
                    autoFocus: true,
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
            name="adresse.ligne1"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-8 fr-my-0"
                  label="Adresse du logement perquisitionné par erreur *"
                  nativeInputProps={{
                    type: "text",
                    placeholder: "Numéro de voie, rue",
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
            name="adresse.ligne2"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-6 fr-m-0"
                  label="Complément"
                  nativeInputProps={{
                    type: "text",
                    placeholder: "Étage, escalier",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
                />
              );
            }}
          />

          <form.Field
            name="adresse.codePostal"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0"
                  label="Code postal *"
                  nativeInputProps={{
                    type: "text",
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
            name="adresse.localite"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0"
                  label="Ville *"
                  nativeInputProps={{
                    type: "text",
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

        <div className="fr-col-12 fr-grid-row fr-grid-row--gutters fr-grid-row--right fr-px-0">
          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Continuer",
                className: "fr-mr-0",
                priority: "primary",
                nativeButtonProps: {
                  type: "submit",
                },
              },
            ]}
          />
        </div>
      </form>
    </div>
  );
};
