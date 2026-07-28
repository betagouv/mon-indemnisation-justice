import {
  CheckSelect,
  CheckSelectProps,
} from "@common/composants/dsfr/champs/check/CheckSelect.tsx";
import { TanstackFormField } from "@common/composants/dsfr/champs/form/TanstackFormField";
import React from "react";

export type FormSelectProps = Omit<
  CheckSelectProps,
  "message" | "validation" | "estValide"
> & { champ?: TanstackFormField };

export const FormSelect = ({ champ, ...props }: FormSelectProps) => {
  return (
    <CheckSelect
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
