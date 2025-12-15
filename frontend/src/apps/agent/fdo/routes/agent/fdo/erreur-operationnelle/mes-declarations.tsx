import React from "react";
import {
  createFileRoute,
  useNavigate,
  useRouter,
} from "@tanstack/react-router";
import Table from "@codegouvfr/react-dsfr/Table";
import { useInjection } from "inversify-react";
import { container } from "@/apps/agent/fdo/_init/_container.ts";
import { dateDansNJours, dateSimple } from "@/common/services/date.ts";
import Badge from "@codegouvfr/react-dsfr/Badge";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";

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
  let { declarations }: { declarations: DeclarationFDOBrisPorte[] } =
    Route.useLoaderData();

  const naviguer = useNavigate<typeof router>({
    from: Route.fullPath,
  });

  const router = useRouter();

  const declarationManager = useInjection<DeclarationManagerInterface>(
    DeclarationManagerInterface.$,
  );

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
                <>
                  sauvegardé le{" "}
                  {dateSimple(
                    declaration.dateSoumission ?? declaration.dateCreation,
                    true,
                  )}
                </>
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
                    className: "fr-my-0",
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
                  ...(declaration.estBrouillon()
                    ? [
                        {
                          className: "fr-my-0",
                          children: "Supprimer",
                          iconId: "fr-icon-delete-line",
                          priority: "secondary",
                          nativeButtonProps: {
                            onClick: async () => {
                              declarationManager.supprimer(declaration);
                              // Petit hack : forcer le routeur à se recharger et ainsi le loader à s'exécuter pour rafraichir la liste des déclarations
                              router.invalidate();
                            },
                          },
                        } as ButtonProps,
                      ]
                    : []),
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
