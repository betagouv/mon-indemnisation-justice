import React from "react";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import type { ButtonsGroupProps } from "@codegouvfr/react-dsfr/ButtonsGroup";
import type { ButtonProps } from "@codegouvfr/react-dsfr/Button";

type ConditionalButton = { button: ButtonProps; condition: boolean } | ButtonProps;

export type ConditionalButtonsGroupProps = Omit<ButtonsGroupProps, "buttons"> & {
  buttons: ConditionalButton[];
};

function isConditional(b: ConditionalButton): b is { button: ButtonProps; condition: boolean } {
  return "condition" in b;
}

export function ConditionalButtonsGroup({ buttons, ...props }: ConditionalButtonsGroupProps) {
  const resolved = buttons
    .filter((b) => !isConditional(b) || b.condition)
    .map((b): ButtonProps => (isConditional(b) ? b.button : b));

  if (resolved.length === 0) return null;

  return <ButtonsGroup {...props} buttons={resolved as [ButtonProps, ...ButtonProps[]]} />;
}
