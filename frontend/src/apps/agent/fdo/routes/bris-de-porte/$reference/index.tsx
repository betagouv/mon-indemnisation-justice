import { container } from "@fdo/container";
import { DeclarationManagerInterface } from "@fdo/services";
import { createFileRoute, redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/bris-de-porte/$reference/")({
  beforeLoad: async ({ params }) => {
    // Logique de redirection : si brouillon alors première étape non complète, sinon première étape
    const declarationManager = container.get(DeclarationManagerInterface.$);
    if (!declarationManager.aDeclaration(params.reference)) {
      throw redirect({
        to: "/bris-de-porte/mes-declarations",
        replace: true,
        params,
      });
    }

    const declaration = await declarationManager.getDeclaration(
      params.reference,
    );

    if (!declaration?.adresse?.localite || !declaration.estBrouillon()) {
      throw redirect({
        to: "/bris-de-porte/$reference/1-bris-de-porte",
        replace: true,
        params,
      });
    } else if (!declaration.procedure?.numeroProcedure) {
      throw redirect({
        to: "/bris-de-porte/$reference/2-service-enqueteur",
        replace: true,
        params,
      });
    }

    throw redirect({
      to: "/bris-de-porte/$reference/3-usager",
      replace: true,
      params,
    });
  },
});
