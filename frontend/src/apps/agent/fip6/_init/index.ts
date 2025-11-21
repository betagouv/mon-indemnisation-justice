import "@/apps/_init.ts";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { Link, type LinkProps } from "@tanstack/react-router";
import { JSX } from "react";

import { router } from "./_router.ts";
import { container } from "./_container.ts";
import { queryClient } from "./_queryClient.ts";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
  Link,
});

declare global {
  interface Window {
    dsfr: any;
  }
}

declare module "@codegouvfr/react-dsfr/spa" {
  interface RegisterLink {
    Link: (props: LinkProps) => JSX.Element;
  }
}

export { router, container, queryClient };
