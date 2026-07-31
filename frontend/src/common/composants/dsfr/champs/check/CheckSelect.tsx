import type { ReactNode } from "react";
import React from "react";
import { Select, SelectProps } from "@codegouvfr/react-dsfr/Select";
import { Requis } from "@/common/composants/dsfr/Requis.tsx";

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
          {estRequis && <Requis />}
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
