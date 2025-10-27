import React from "react";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import Table from "@codegouvfr/react-dsfr/Table";
import { useInjection } from "inversify-react";
import {
  container,
  DeclarationManagerInterface,
} from "@/apps/agent/fdo/services";
import { dateDansNJours, dateSimple } from "@/common/services/date.ts";
import Badge from "@codegouvfr/react-dsfr/Badge";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { router } from "@/apps/agent/fdo/router.ts";
import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";

export const Route = createFileRoute(
  "/agent/fdo/erreur-operationnelle/mes-declarations",
)({
  loader: async ({ params }) => {
    return {
      declarations: await container
        .get(DeclarationManagerInterface.$)
        .getListe(),
    };
  },
  component: () => <Page />,
});

const Page = () => {
  const { declarations }: { declarations: DeclarationErreurOperationnelle[] } =
    Route.useLoaderData();

  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  return (
    <div>
      <h1>Mes déclarations</h1>

      {declarations.length ? (
        <div className="fr-grid-row">
          <Table
            className="fr-col-12"
            fixed={true}
            headers={["État", "Date opération", "Adresse", ""]}
            data={declarations.map((declaration) => [
              declaration.estBrouillon() ? (
                <Badge>Brouillon</Badge>
              ) : (
                <>sauvegardé le {dateSimple(dateDansNJours(-3))}</>
              ),
              declaration.dateOperation ? (
                dateSimple(declaration.dateOperation, true)
              ) : (
                <i>Non renseignée</i>
              ),
              `${declaration.adresse?.ligne1} ${declaration.adresse?.codePostal} ${declaration.adresse?.localite}`,
              <ButtonsGroup
                buttonsEquisized={false}
                inlineLayoutWhen={"always"}
                alignment={"right"}
                buttonsSize="small"
                buttons={[
                  {
                    className: "fr-m-0",
                    children: declaration.estBrouillon() ? "Reprendre" : "Voir",
                    iconId: declaration.estBrouillon()
                      ? "fr-icon-pencil-line"
                      : "fr-icon-eye-line",
                    priority: "secondary",
                    nativeButtonProps: {
                      onClick: () =>
                        naviguer({
                          to: "/agent/fdo/erreur-operationnelle/$reference",
                          params: {
                            reference: declaration.reference,
                          } as any,
                        }),
                    },
                  },
                ]}
              />,
            ])}
          />
        </div>
      ) : (
        <p>Aucune déclaration à votre actif.</p>
      )}
    </div>
  );
};
