import {
  CheckSelect,
  CheckSelectProps,
} from "@/apps/requerant/composants/champs/check/CheckSelect.tsx";
import React from "react";
import { TanstackFormField } from "@/apps/requerant/composants/champs/form/TanstackFormField";

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
