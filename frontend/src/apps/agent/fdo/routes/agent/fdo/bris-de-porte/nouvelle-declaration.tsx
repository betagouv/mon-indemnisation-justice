import { redirect, createFileRoute } from "@tanstack/react-router";
import { container } from "@/apps/agent/fdo/_init/_container";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";

export const Route = createFileRoute(
  "/agent/fdo/bris-de-porte/nouvelle-declaration",
)({
  beforeLoad: async ({ cause }) => {
    if (cause === "enter") {
      const brouillon = await container
        .get(DeclarationManagerInterface.$)
        .nouvelleDeclaration();

      throw redirect({
        to: "/agent/fdo/bris-de-porte/$reference",
        params: {
          reference: brouillon.reference,
        },
      });
    }
  },
});
