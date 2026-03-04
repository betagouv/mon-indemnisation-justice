import React, { useMemo } from "react";
import {
  CheckInput,
  CheckInputProps,
} from "@/apps/requerant/composants/champs/check/CheckInput.tsx";
import { TanstackFormField } from "@/apps/requerant/composants/champs/form/TanstackFormField";

export type FormInputProps = Omit<
  CheckInputProps,
  "message" | "validation" | "estValide"
> & {
  champ?: TanstackFormField;
};
export const FormInput = ({ champ, ...props }: FormInputProps) => {
  return (
    <CheckInput
      estValide={champ?.state.meta.isValid}
      validation={!!champ}
      message={
        champ && !champ.state.meta.isValid
          ? (champ.state.meta.errors.at(0).message ?? "")
          : ""
      }
      {...props}
    />
  );
};
