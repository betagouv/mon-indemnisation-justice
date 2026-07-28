import {
  CheckRadioButtons,
  CheckRadioButtonsProps,
} from "@common/composants/dsfr/champs/check/CheckRadioButtons.tsx";
import { TanstackFormField } from "@common/composants/dsfr/champs/form/TanstackFormField";
import React from "react";

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
