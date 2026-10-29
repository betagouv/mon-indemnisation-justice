import { container } from "@fdo/container";
import { DeclarationManagerInterface } from "@fdo/services";
import { createFileRoute, redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/bris-de-porte/$id/")({
  beforeLoad: async ({ params }) => {
    // Logique de redirection : si brouillon alors première étape non complète, sinon première étape
    const declarationManager = container.get(DeclarationManagerInterface.$);
    console.log(params.id);

    if (!declarationManager.aDeclaration(params.id)) {
      throw redirect({
        to: "/bris-de-porte/mes-declarations",
        replace: true,
        params,
      });
    }

    const declaration = await declarationManager.getDeclaration(params.id);

    if (!declaration) {
      // TODO jeter une erreur "not found" à la place

      throw redirect({
        to: "/bris-de-porte/mes-declarations",
        params,
      });
    }

    if (declaration.estBrouillon()) {
      if (!declaration?.adresse?.localite) {
        throw redirect({
          to: "/bris-de-porte/$id/1-bris-de-porte",
          replace: true,
          params,
        });
      } else if (!declaration.procedure?.numeroProcedure) {
        throw redirect({
          to: "/bris-de-porte/$id/2-service-enqueteur",
          replace: true,
          params,
        });
      }
    }

    throw redirect({
      to: "/bris-de-porte/$id/3-usager",
      replace: true,
      params,
    });
  },
});
