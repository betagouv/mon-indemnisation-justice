import { redirect, createFileRoute } from "@tanstack/react-router";
import {
  container,
  DeclarationManagerInterface,
} from "@/apps/agent/fdo/services";

export const Route = createFileRoute(
  "/agent/fdo/erreur-operationnelle/nouvelle-declaration",
)({
  beforeLoad: async ({ cause }) => {
    if (cause === "enter") {
      const brouillon = await container
        .get(DeclarationManagerInterface.$)
        .nouvelleDeclaration();

      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/$reference",
        params: {
          reference: brouillon.reference,
        },
      });
    }
  },
});
