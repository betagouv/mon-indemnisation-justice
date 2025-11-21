import { redirect, createFileRoute } from "@tanstack/react-router";
import { container } from "@/apps/agent/fdo/_init/_container";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";

export const Route = createFileRoute(
  "/agent/fdo/erreur-operationnelle/$reference/",
)({
  beforeLoad: async ({ params }) => {
    // Logique de redirection : si brouillon alors première étape non complète, sinon première étape
    const declarationManager = container.get(DeclarationManagerInterface.$);
    if (!declarationManager.aDeclaration(params.reference)) {
      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/mes-declarations",
        replace: true,
        params,
      });
    }

    const declaration = await declarationManager.getDeclaration(
      params.reference,
    );

    if (!declaration?.adresse?.localite || !declaration.estBrouillon()) {
      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/$reference/1-operation",
        replace: true,
        params,
      });
    } else if (!declaration.procedure?.numeroProcedure) {
      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/$reference/2-complement",
        replace: true,
        params,
      });
    }

    throw redirect({
      to: "/agent/fdo/erreur-operationnelle/$reference/3-requerant",
      replace: true,
      params,
    });
  },
});
