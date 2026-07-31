import { container } from "@/apps/agent/fdo/container";
import { DeclarationManagerInterface } from "@/apps/agent/fdo/services";
import { createFileRoute, redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/bris-de-porte/nouvelle-declaration")({
  beforeLoad: async ({ cause }) => {
    if (cause === "enter") {
      const brouillon = await container
        .get(DeclarationManagerInterface.$)
        .nouvelleDeclaration();

      throw redirect({
        to: "/bris-de-porte/$reference",
        params: {
          reference: brouillon.reference,
        },
      });
    }
  },
});
