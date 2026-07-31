import {
  CheckInput,
  CheckInputProps,
} from "@common/composants/dsfr/champs/check/CheckInput.tsx";
import { TanstackFormField } from "@common/composants/dsfr/champs/form/TanstackFormField";
import React from "react";

export type FormInputProps = Omit<
  CheckInputProps,
  "message" | "validation" | "estValide"
> & {
  champ: TanstackFormField;
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
