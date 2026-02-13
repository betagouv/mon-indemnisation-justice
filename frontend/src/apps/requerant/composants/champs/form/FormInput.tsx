import React, { useMemo } from "react";
import {
  CheckInput,
  CheckInputProps,
} from "@/apps/requerant/composants/champs/check/CheckInput.tsx";
import { FieldApi } from "@tanstack/react-form";

export type FormInputProps = Omit<CheckInputProps, "message" | "estValide"> & {
  // @ts-ignore
  champ: FieldApi;
};
export const FormInput = ({ champ, ...props }: FormInputProps) => {
  const estInvalide = useMemo(() => champ.state.meta.isValid == false, [champ]);

  return (
    <CheckInput
      estValide={estInvalide ? false : undefined}
      message={
        <>{estInvalide ? (champ.state.meta.errors.at(0).message ?? "") : ""}</>
      }
      {...props}
    />
  );
};
