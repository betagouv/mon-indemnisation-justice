import { dateChiffre } from "@/common/services/date.ts";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";
import { useForm } from "@tanstack/react-form";
import React, { ChangeEvent, useState } from "react";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Input } from "@codegouvfr/react-dsfr/Input";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { z } from "zod";
import "@/style/index.css";
import {
  DeclarationFDOBrisPorte,
  DeclarationFDOBrisPorteErreurType,
  DeclarationFDOBrisPorteErreurTypes,
} from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { container } from "@/apps/agent/fdo/_init/_container.ts";
import { useInjection } from "inversify-react";
import { router } from "@/apps/agent/fdo/_init/_router.ts";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";

export const Route = createFileRoute(
  "/agent/fdo/bris-de-porte/$reference/1-bris-de-porte",
)({
  beforeLoad: async ({ params }) => {
    if (
      !(await container
        .get(DeclarationManagerInterface.$)
        .aDeclaration(params.reference))
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
    return {
      reference: params.reference,
      declaration: (await container
        .get(DeclarationManagerInterface.$)
        .getDeclaration(params.reference)) as DeclarationFDOBrisPorte,
    };
  },
  component: () => <Page />,
});

const schemaErreurOperationnelle = z.object({
  estErreur: z.literal(DeclarationFDOBrisPorteErreurTypes),
  descriptionErreur: z.any(),
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
  }: { declaration: DeclarationFDOBrisPorte; reference: string } =
    Route.useLoaderData();

  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

  const form = useForm({
    defaultValues: {
      estErreur: declaration.estErreur,
      descriptionErreur: declaration.descriptionErreur,
      dateOperation: declaration.dateOperation,
      adresse: declaration.adresse,
    },
    listeners: {
      onBlur: async ({ fieldApi, formApi }) => {
        declarationManager.mettreAJour(declaration, formApi.state.values);
      },
      onSubmit: async ({ formApi }) => {
        await declarationManager.enregistrer(declaration, formApi.state.values);
      },
    },
    validators: {
      onSubmit: schemaErreurOperationnelle,
    },
    onSubmit: async ({ value }) => {
      await naviguer({
        to: "/agent/fdo/bris-de-porte/$reference/2-service-enqueteur",
        params: { reference } as any,
      });
    },
  });

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        e.stopPropagation();
        void form.handleSubmit();
      }}
    >
      <div style={{ display: "flex", flexDirection: "column", gap: "1.5vh" }}>
        <h1 className="fr-my-1w">Nouvelle déclaration de bris de porte</h1>

        <Stepper
          className="fr-my-1w"
          currentStep={1}
          stepCount={3}
          title="Eléments relatifs au bris de porte"
          nextTitle="Éléments relatifs au service enquêteur "
        />

        {/*
        <div className="fr-grid-row">
          <h6 className="fr-m-0 fr-text-label--blue-france">
            Informations concernant le logement perquisitionné{" "}
          </h6>
        </div>
        */}

        <div className="fr-grid-row fr-px-0">
          <form.Field
            name="estErreur"
            children={(field) => {
              return (
                <RadioButtons
                  className="fr-champ-requis"
                  legend={
                    <>
                      S’agissait-il d’
                      <Tooltip
                        title="Ex: la porte du logement 301 est fracturée alors que l’objectif visé par l’enquête était le 307"
                        kind="hover"
                      >
                        <a href="">une erreur opérationnelle</a>
                      </Tooltip>{" "}
                      ?
                    </>
                  }
                  small={false}
                  orientation="vertical"
                  disabled={declaration.estSauvegarde()}
                  options={[
                    {
                      label: "Oui",
                      hintText:
                        "Le logement perquisitionné n’était pas visé par l’opération.",
                      nativeInputProps: {
                        className: "fr-col-6",
                        defaultChecked: declaration.estErreur === "OUI",
                        onChange: (event: ChangeEvent<HTMLInputElement>) => {
                          field.handleChange(
                            event.target.checked ? "OUI" : undefined,
                          );
                        },
                      },
                    },
                    {
                      label: "Non",
                      hintText:
                        "Le logement perquisitionné était visé par l’opération.",
                      nativeInputProps: {
                        className: "fr-col-6",
                        defaultChecked: declaration.estErreur === "DOUTE",
                        onChange: (event: ChangeEvent<HTMLInputElement>) => {
                          field.handleChange(
                            event.target.checked ? "DOUTE" : undefined,
                          );
                        },
                      },
                    },
                    {
                      label: "J’ai un doute",
                      hintText: (
                        <>
                          Vous avez un doute sur la situation? Vous ne savez pas
                          s'il s’agit d’une erreur opérationnelle ? Vous pouvez
                          consulter <a href="">la foire aux questions</a>. Le
                          Bureau du précontentieux se chargera d'instruire la
                          demande a posteriori.
                        </>
                      ),
                      nativeInputProps: {
                        className: "fr-col-6",
                        defaultChecked: declaration.estErreur === "DOUTE",
                        onChange: (event: ChangeEvent<HTMLInputElement>) => {
                          field.handleChange(
                            event.target.checked ? "DOUTE" : undefined,
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

        <div className="fr-grid-row fr-grid-row--gutters fr-px-0">
          <form.Field
            name="descriptionErreur"
            children={(field) => {
              return (
                <Input
                  label="Décrivez l’opération"
                  textArea
                  className="fr-col-12"
                  disabled={declaration.estSauvegarde()}
                  nativeTextAreaProps={{
                    defaultValue: declaration.descriptionErreur ?? "",
                    onChange: (e) => field.handleChange(e.target.value),
                    rows: 5,
                    placeholder:
                      "1 porte et 1 portail cassés, aucun numéro sur la porte",
                  }}
                />
              );
            }}
          />
        </div>

        <div className="fr-grid-row fr-grid-row--gutters fr-px-0 fr-my-2w">
          <form.Field
            name="dateOperation"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-my-0 fr-champ-requis"
                  label="Date de l'opération"
                  disabled={declaration.estSauvegarde()}
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
                  className="fr-col-lg-8 fr-my-0 fr-champ-requis"
                  label="Adresse du logement ayant subi le bris de porte"
                  disabled={declaration.estSauvegarde()}
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
                  label="Complément d'adresse"
                  disabled={declaration.estSauvegarde()}
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
                  className="fr-col-lg-3 fr-m-0 fr-champ-requis"
                  label="Code postal"
                  disabled={declaration.estSauvegarde()}
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
                  className="fr-col-lg-3 fr-m-0 fr-champ-requis"
                  label="Ville"
                  disabled={declaration.estSauvegarde()}
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
      </div>
    </form>
  );
};
