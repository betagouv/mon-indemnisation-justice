import { container } from "@/apps/agent/fdo/container.ts";
import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";
import { dateSimple } from "@/common/services/date.ts";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Table from "@codegouvfr/react-dsfr/Table";
import {
  createFileRoute,
  useNavigate,
  useRouter,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React from "react";

export const Route = createFileRoute("/bris-de-porte/mes-declarations")({
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
              declaration.adresse ? (
                `${declaration.adresse?.ligne1} ${declaration.adresse?.codePostal} ${declaration.adresse?.localite}`
              ) : (
                <i>Non renseignée</i>
              ),
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
                          to: "/bris-de-porte/$reference",
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
                              await declarationManager.supprimer(declaration);
                              await router.invalidate();
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
