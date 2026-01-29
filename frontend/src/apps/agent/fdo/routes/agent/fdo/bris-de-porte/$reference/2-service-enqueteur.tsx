import { useForm } from "@tanstack/react-form";
import {
  createFileRoute,
  redirect,
  useNavigate,
  useRouter,
} from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React, { useRef } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { z } from "zod";
import { useInjection } from "inversify-react";
import {
  DeclarationFDOBrisPorte,
  Procedure,
} from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { container } from "@/apps/agent/fdo/_init/_container.ts";
import { AgentContext } from "@/apps/agent/_commun/contexts";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";
import { Button } from "@codegouvfr/react-dsfr/Button";
import {
  ModaleAjoutPieceJointe,
  ModaleAjoutPieceJointeRef,
} from "@/apps/agent/fdo/components/ModaleAjoutPieceJointe.tsx";
import {
  ModalePrevisualiserPieceJointe,
  ModalePrevisualiserPieceJointeRef,
} from "@/apps/agent/fdo/components/ModalePrévisualiserPieceJointe.tsx";
import { Document } from "@/common/models";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

export const Route = createFileRoute(
  "/agent/fdo/bris-de-porte/$reference/2-service-enqueteur",
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
    context,
  }: {
    params: any;
    context: AgentContext;
  }): Promise<{
    declaration: DeclarationFDOBrisPorte;
    reference: string;
    contexte: AgentContext;
  }> => {
    return {
      reference: params.reference,
      declaration: (await container
        .get(DeclarationManagerInterface.$)
        .getDeclaration(params.reference)) as DeclarationFDOBrisPorte,
      contexte: context,
    };
  },
  component: Page,
});

const schemaInfosJuridiques = z.object({
  procedure: z.object({
    serviceEnqueteur: z
      .string()
      .trim()
      .min(1, { error: "Le nom du service enquêteur est requis" }),
    numeroProcedure: z
      .string()
      .trim()
      .min(1, { error: "Le numéro de procédure est requis" }),
    juridictionOuParquet: z.string(),
    nomMagistrat: z.string(),
    telephone: z
      .string({ error: "Le numéro de téléphone est requis" })
      .min(7, { error: "Le numéro de téléphone est requis" }),
  }),
});

function Page() {
  const {
    declaration,
    reference,
    contexte,
  }: {
    declaration: DeclarationFDOBrisPorte;
    reference: string;
    contexte: AgentContext;
  } = Route.useLoaderData();

  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const router = useRouter();

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

  const refModaleAjoutPJ = useRef<ModaleAjoutPieceJointeRef>(null);
  const refModalePrevisualiserPJ =
    useRef<ModalePrevisualiserPieceJointeRef>(null);

  // TODO: gérer les pièces jointes dans un state "façade" qui peut-être modifiable

  const form = useForm({
    defaultValues: {
      procedure: {
        ...(declaration.procedure ?? new Procedure()),
      },
    },
    listeners: {
      onChange: async ({ formApi }) => {
        if (declaration.estBrouillon()) {
          declarationManager.mettreAJour(declaration, formApi.state.values);
        }
      },
      onChangeDebounceMs: 500,
      onSubmit: async ({ formApi }) => {
        if (declaration.estBrouillon()) {
          await declarationManager.enregistrer(
            declaration,
            formApi.state.values,
          );
        }
      },
    },
    validators: {
      onSubmit: schemaInfosJuridiques,
    },
    onSubmit: async ({ value }) => {
      // TODO
      await naviguer({
        to: "/agent/fdo/bris-de-porte/$reference/3-usager",
        params: { reference } as any,
      });
    },
  });

  return (
    <>
      {!declaration.estSoumise() && (
        <>
          <ModaleAjoutPieceJointe
            ref={refModaleAjoutPJ}
            declarationFDO={declaration}
            onTeleverse={async (
              declarationMiseAJour: DeclarationFDOBrisPorte,
            ) => {
              declarationManager.mettreAJour(declaration, declarationMiseAJour);
              // Petit hack : forcer le routeur à se recharger et ainsi le loader à s'exécuter pour rafraichir la liste des déclarations
              await router.invalidate();
            }}
          />
          <ModalePrevisualiserPieceJointe
            ref={refModalePrevisualiserPJ}
            declarationFDO={declaration}
            onSupprime={async (pieceJointe: Document) => {
              declarationManager.mettreAJour(declaration, {
                piecesJointes: declaration.piecesJointes.filter(
                  (value) => value.id !== pieceJointe.id,
                ),
              });
              await router.invalidate();
            }}
          />
        </>
      )}
      <form
        style={{ display: "flex", flexDirection: "column", gap: "1.5vh" }}
        onSubmit={(e) => {
          e.preventDefault();
          e.stopPropagation();
          void form.handleSubmit();
        }}
      >
        <h1 className="fr-m-0">Nouvelle déclaration de bris de porte</h1>

        <Stepper
          className="fr-m-0"
          currentStep={2}
          stepCount={3}
          title="Éléments relatifs au service enquêteur"
          nextTitle="Informations concernant l'usager"
        />

        <div className="fr-grid-row">
          <h6 className="fr-m-0 fr-text-label--blue-france">
            Documents justificatifs
          </h6>
        </div>

        <Alert
          severity="info"
          title=""
          description="Merci de mettre à disposition les pièces justificatives pertinentes
          dans le cadre de la déclaration : PV d’intervention ou photos de la
          porte endommagée"
        ></Alert>

        <p className="fr-text--sm fr-m-0">
          Les documents que vous joignez au dossier faciliteront la travail
          d'instruction des rédacteurs du bureau du Précontentieux.
        </p>

        <div
          className="fr-grid-row fr-grid-row--gutters"
          style={{ alignItems: "center" }}
        >
          {declaration.piecesJointes.map((p) => (
            <div
              className="fr-col-lg-4"
              key={`declaration-piece-jointe-${p.id}`}
            >
              <a
                className="fr-link"
                target="_blank"
                href={`/agent/fdo/document/${p.id}/${p.fileHash}`}
                title={`Consulter la pièce jointe "${p.filename}" dans un nouvel onglet`}
              >
                {p.originalFilename}
              </a>
              {!declaration.estSoumise() && (
                <button
                  role={"button"}
                  type={"button"}
                  className="fr-btn fr-btn--sm fr-icon-delete-line fr-btn--tertiary-no-outline fr-mx-1w"
                  onClick={() =>
                    refModalePrevisualiserPJ.current?.previsualiserPieceJointe(
                      p,
                    )
                  }
                >
                  Retirer
                </button>
              )}
            </div>
          ))}

          {!declaration.estSoumise() && (
            <div className="fr-col-lg-4">
              <Button
                children="Ajouter un document"
                iconId="fr-icon-add-line"
                size="small"
                priority="secondary"
                iconPosition="right"
                onClick={() => refModaleAjoutPJ.current?.ouvrir()}
                nativeButtonProps={{
                  type: "button",
                  role: "button",
                }}
              />
            </div>
          )}
        </div>

        <div className="fr-grid-row">
          <h6 className="fr-m-0 fr-text-label--blue-france">
            Informations concernant la procédure
          </h6>
        </div>

        <div className="fr-grid-row fr-grid-row--gutters fr-py-2w">
          <form.Field
            name="procedure.serviceEnqueteur"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-6 fr-m-0 fr-champ-requis"
                  label="Service enquêteur"
                  disabled={declaration.estSoumise()}
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
            name="procedure.telephone"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-3 fr-m-0  fr-champ-requis"
                  label="Téléphone du service ou de l'agent"
                  disabled={declaration.estSoumise()}
                  nativeInputProps={{
                    type: "text",
                    value: field.state.value ?? "",
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

          <Input
            className="fr-col-lg-3 fr-m-0 fr-champ-requis"
            label="Courriel de l'agent"
            disabled={true}
            nativeInputProps={{
              type: "text",
              value: contexte.agent.courriel,
            }}
          />

          <form.Field
            name="procedure.numeroProcedure"
            children={(field) => {
              return (
                <Input
                  className="fr-col-lg-4 fr-m-0"
                  label="Numéro de procédure *"
                  disabled={declaration.estSoumise()}
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
                  disabled={declaration.estSoumise()}
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
                  disabled={declaration.estSoumise()}
                  nativeInputProps={{
                    type: "text",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
                />
              );
            }}
          />
        </div>

        <div className="fr-grid-row fr-grid-row--gutters">
          <ButtonsGroup
            className="fr-col-lg-12 fr-p-0"
            inlineLayoutWhen="always"
            alignment="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Revenir à l'étape précédente",
                priority: "secondary",
                iconId: "fr-icon-arrow-left-line",
                iconPosition: "left",
                onClick: () =>
                  naviguer({
                    to: "/agent/fdo/bris-de-porte/$reference/1-bris-de-porte",
                    params: {
                      reference,
                    } as any,
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
    </>
  );
}
