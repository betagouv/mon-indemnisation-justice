import { dateChiffre } from "@/common/services/date.ts";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { useForm } from "@tanstack/react-form";
import React, { ChangeEvent, useState } from "react";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Input } from "@codegouvfr/react-dsfr/Input";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { z } from "zod";
import "@/style/index.css";
import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";
import { container } from "@/apps/agent/fdo/_init/_container.ts";
import { useInjection } from "inversify-react";
import { router } from "@/apps/agent/fdo/_init/_router.ts";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";

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
  doute: z.boolean(),
  motifDoute: z.any(),
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

  const [douteAMotiver, setDouteAMotiver] = useState<boolean>(
    declaration.doute,
  );

  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

  const form = useForm({
    defaultValues: {
      doute: declaration.doute,
      motifDoute: declaration.motifDoute,
      dateOperation: declaration.dateOperation,
      adresse: declaration.adresse,
    },
    listeners: {
      onChange: async ({ fieldApi, formApi }) => {
        declarationManager.enregistrer(declaration, formApi.state.values);
      },
      onChangeDebounceMs: 750,
    },
    validators: {
      onSubmit: schemaErreurOperationnelle,
    },
    onSubmit: async ({ value }) => {
      await naviguer({
        to: "/agent/fdo/erreur-operationnelle/$reference/2-complement",
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
        <h1>Nouvelle déclaration d'erreur opérationnelle</h1>

        <Stepper
          currentStep={1}
          stepCount={3}
          title="Eléments relatifs à l’erreur opérationnelle"
          nextTitle="Complément d’information pour le Ministère de la Justice "
        />

        <div className="fr-grid-row">
          <h6 className="fr-m-0 fr-text-label--blue-france">
            Informations concernant le logement perquisitionné par erreur{" "}
          </h6>
        </div>

        <div className="fr-grid-row fr-px-0 fr-my-2w">
          <form.Field
            name="doute"
            children={(field) => {
              return (
                <RadioButtons
                  legend="S’agissait-il d’une erreur opérationnelle ? *"
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
                        defaultChecked: !declaration.doute,
                        onChange: (event: ChangeEvent<HTMLInputElement>) => {
                          setDouteAMotiver(false);
                          field.handleChange(!event.target.checked);
                        },
                      },
                    },
                    {
                      label: "J’ai un doute",
                      hintText:
                        "Vous avez un doute sur la situation. Vous ne savez pas si il s’agit d’une erreur ? Les services du précontentieux se chargeront d’évaluer la demande a postériori.",
                      nativeInputProps: {
                        className: "fr-col-6",
                        defaultChecked: declaration.doute,
                        onChange: (event: ChangeEvent<HTMLInputElement>) => {
                          setDouteAMotiver(true);
                          field.handleChange(event.target.checked);
                        },
                      },
                    },
                  ]}
                />
              );
            }}
          />
        </div>

        {douteAMotiver && (
          <div className="fr-grid-row fr-grid-row--gutters fr-px-0">
            <form.Field
              name="motifDoute"
              children={(field) => {
                return (
                  <Input
                    label="Décrivez les circonstances de l’opération"
                    textArea
                    className="fr-col-12"
                    disabled={declaration.estSauvegarde()}
                    nativeTextAreaProps={{
                      defaultValue: declaration.motifDoute ?? "",
                      onChange: (e) => field.handleChange(e.target.value),
                      rows: 5,
                    }}
                  />
                );
              }}
            />
          </div>
        )}

        <div className="fr-grid-row fr-grid-row--gutters fr-px-0 fr-my-2w">
          <form.Field
            name="dateOperation"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-my-0"
                  label="Date de l'opération *"
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
                  className="fr-col-lg-8 fr-my-0"
                  label="Adresse du logement ayant subi le bris de porte *"
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
                  className="fr-col-lg-3 fr-m-0"
                  label="Code postal *"
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
                  className="fr-col-lg-3 fr-m-0"
                  label="Ville *"
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
