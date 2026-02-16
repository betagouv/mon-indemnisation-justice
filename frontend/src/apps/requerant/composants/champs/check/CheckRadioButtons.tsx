import React, { type ReactNode } from "react";
import type { InputProps } from "@codegouvfr/react-dsfr/src/Input.tsx";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { RadioButtonsProps } from "@codegouvfr/react-dsfr/src/RadioButtons.tsx";

export type CheckRadioButtonsProps = Omit<
  RadioButtonsProps,
  "state" | "stateRelatedMessage"
> & {
  estRequis?: boolean;
  message?: ReactNode;
  validation: boolean;
  estValide?: boolean;
};
export const CheckRadioButtons = ({
  estRequis,
  message,
  validation = true,
  estValide,
  legend,
  ...props
}: CheckRadioButtonsProps) => {
  return (
    <RadioButtons
      legend={
        <>
          {legend}
          {estRequis && (
            <span className="fr-text-default--error"> &#x2217;</span>
          )}
        </>
      }
      state={
        validation && !!message ? (estValide ? "success" : "error") : "default"
      }
      stateRelatedMessage={validation && !!message ? message : ""}
      {...props}
    />
  );
};
