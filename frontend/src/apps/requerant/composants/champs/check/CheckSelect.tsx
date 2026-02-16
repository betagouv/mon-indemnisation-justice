import React from "react";
import { Select, SelectProps } from "@codegouvfr/react-dsfr/Select";
import type { ReactNode } from "react";

export type CheckSelectProps = Omit<
  SelectProps,
  "state" | "stateRelatedMessage"
> & {
  estRequis?: boolean;
  message?: ReactNode;
  validation: boolean;
  estValide?: boolean;
};

export const CheckSelect = ({
  estRequis,
  message,
  estValide,
  validation,
  label,
  children,
  ...props
}: CheckSelectProps) => {
  return (
    <Select
      label={
        <>
          {label}
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
    >
      {children}
    </Select>
  );
};
