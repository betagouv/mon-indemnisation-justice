import React from "react";
import { TanstackFormField } from "@/apps/requerant/composants/champs/form/TanstackFormField";
import {
  CheckRadioButtons,
  CheckRadioButtonsProps,
} from "@/apps/requerant/composants/champs/check/CheckRadioButtons.tsx";

export type FormRadioButtonsProps = Omit<
  CheckRadioButtonsProps,
  "message" | "validation" | "estValide"
> & {
  champ?: TanstackFormField;
};

export const FormRadioButtons = ({
  champ,
  ...props
}: FormRadioButtonsProps) => {
  return (
    <CheckRadioButtons
      estValide={champ?.state.meta.isValid}
      validation={!!champ}
      message={
        champ && !champ.state.meta.isValid
          ? (champ?.state.meta.errors.at(0).message ?? "")
          : ""
      }
      {...props}
    />
  );
};
