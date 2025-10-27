import { useForm } from "@tanstack/react-form";
import { createFileRoute, redirect, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import Download from "@codegouvfr/react-dsfr/Download";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { z } from "zod";
import { plainToClassFromExist } from "class-transformer";
import { useInjection } from "inversify-react";
import {
  container,
  DeclarationManagerInterface,
} from "@/apps/agent/fdo/services";
import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";

export const Route = createFileRoute(
  "/agent/fdo/erreur-operationnelle/$reference/2-complement",
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

const schemaInfosJuridiques = z.object({
  procedure: z.object({
    serviceEnqueteur: z.string(),
    courrielAgent: z.email({ error: "L'adresse courriel est requise" }),
    telephoneAgent: z
      .string()
      .min(7, { error: "Le numéro de téléphone est requis" }),
    numeroProcedure: z
      .string()
      .trim()
      .min(1, { error: "Le numéro de procédure est requis" }),
    juridictionOuParquet: z.string(),
    nomMagistrat: z.string(),
    commentaire: z.string(),
  }),
});

const ModaleAjoutFichier = createModal({
  id: "modale-ajouter-fichier-declaration-erreur-operationnelle",
  isOpenedByDefault: false,
});

function Page() {
  const {
    declaration,
    reference,
  }: { declaration: DeclarationErreurOperationnelle; reference: string } =
    Route.useLoaderData();

  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

  const form = useForm({
    defaultValues: {
      procedure: declaration.procedure
        ? { ...declaration.procedure }
        : {
            serviceEnqueteur: "",
            courrielAgent: "",
            telephoneAgent: "",
            numeroProcedure: "",
            juridictionOuParquet: "",
            nomMagistrat: "",
            commentaire: "",
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
      onSubmit: schemaInfosJuridiques,
    },
    onSubmit: async ({ value }) => {
      console.log(value);
      await naviguer({
        to: "/agent/fdo/erreur-operationnelle/$reference/3-requerant",
      });
    },
  });

  return (
    <>
      <ModaleAjoutFichier.Component
        size="large"
        title="Ajouter un document"
        buttons={[
          {
            children: "Ajouter",
          },
        ]}
      >
        <div className="fr-grid-row fr-grid-row--gutters">
          <Upload
            className="fr-col-lg-6"
            label="Fichier à téléverser"
            hint="Taille maximale : 10 Mo. Format pdf uniquement."
            nativeInputProps={{
              accept: "application/pdf,image/*",
            }}
          />

          <Select
            label="Type de document"
            className="fr-col-lg-6"
            nativeSelectProps={{}}
          >
            <option value="" disabled hidden>
              Sélectionnez un type
            </option>

            <option value="pv_intervention">PV d'intervention</option>
            <option value="photo_porte">Photo de la porte endommagée</option>
          </Select>
        </div>
      </ModaleAjoutFichier.Component>

      <div style={{ display: "flex", flexDirection: "column", gap: "1.5vh" }}>
        <h1 className="fr-m-0">Nouvelle déclaration d'erreur opérationnelle</h1>

        <Stepper
          className="fr-m-0"
          currentStep={2}
          stepCount={3}
          title="Complément d’information pour le Ministère de la Justice"
          nextTitle="Informations du requérant"
        />

        <div className="fr-grid-row">
          <h6 className="fr-m-0 fr-text-label--blue-france">
            Informations concernant l’opération de police judiciaire
          </h6>
        </div>

        <form
          style={{ display: "flex", flexDirection: "column", gap: "1.5vh" }}
          onSubmit={(e) => {
            e.preventDefault();
            e.stopPropagation();
            void form.handleSubmit();
          }}
        >
          <div className="fr-grid-row fr-grid-row--gutters">
            <form.Field
              name="procedure.serviceEnqueteur"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-6 fr-m-0"
                    label="Nom du service enquêteur"
                    nativeInputProps={{
                      type: "text",
                      autoFocus: true,
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
              name="procedure.courrielAgent"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-3 fr-m-0"
                    label="Courriel *"
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
              name="procedure.telephoneAgent"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-3 fr-m-0"
                    label="Téléphone *"
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
              name="procedure.numeroProcedure"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-4 fr-m-0"
                    label="Numéro de procédure *"
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
              name="procedure.juridictionOuParquet"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-4 fr-m-0"
                    label="Juridiction / parquet (le cas échéant)"
                    nativeInputProps={{
                      type: "text",
                      value: field.state.value,
                      onChange: (e) => field.handleChange(e.target.value),
                    }}
                  />
                );
              }}
            />

            <form.Field
              name="procedure.nomMagistrat"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-4 fr-m-0"
                    label="Nom du magistrat (le cas échéant)"
                    nativeInputProps={{
                      type: "text",
                      value: field.state.value,
                      onChange: (e) => field.handleChange(e.target.value),
                    }}
                  />
                );
              }}
            />

            <form.Field
              name="procedure.commentaire"
              children={(field) => {
                return (
                  <Input
                    className="fr-col-lg-12 fr-m-0"
                    label="Commentaire à destination du dossier"
                    textArea={true}
                    nativeTextAreaProps={{
                      rows: 5,
                      value: field.state.value,
                      onChange: (e) => field.handleChange(e.target.value),
                    }}
                  />
                );
              }}
            />
          </div>

          <div className="fr-grid-row">
            <h6 className="fr-m-0 fr-text-label--blue-france">
              Document justificatifs
            </h6>
          </div>

          <div
            className="fr-grid-row fr-grid-row--gutters"
            style={{ alignItems: "center" }}
          >
            <div className="fr-col-lg-4">
              <Download
                details="PDF - 88 Ko"
                label="PV d'intervention"
                linkProps={{}}
              />
            </div>

            <div className="fr-col-lg-4">
              <Download
                label="Photo de la porte d'entrée"
                details="JPEG - 792 Ko"
                linkProps={{}}
              />
            </div>

            <div className="fr-col-lg-4">
              <Button
                children="Ajouter un document"
                iconId="fr-icon-add-line"
                size="small"
                priority="secondary"
                onClick={() => ModaleAjoutFichier.open()}
              />
            </div>
          </div>

          <div className="fr-grid-row fr-grid-row--gutters">
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
                      to: "/agent/fdo/erreur-operationnelle/$reference/1-operation",
                    }),
                },
                {
                  children: "Continuer",
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
    </>
  );
}
