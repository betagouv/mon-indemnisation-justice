import {
  CheckUpload,
  CheckUploadProps,
} from "@/apps/requerant/composants/champs/check/CheckUpload.tsx";
import { TanstackFormField } from "@/apps/requerant/composants/champs/form/TanstackFormField";
import React from "react";

export type FormUploadProps = Omit<
  CheckUploadProps,
  "message" | "validation" | "estValide"
> & {
  champ: TanstackFormField;
};
export const FormUpload = ({ champ, ...props }: FormUploadProps) => {
  return (
    <CheckUpload
      estValide={champ?.state.meta.isValid}
      validation={!!champ}
      message={
        champ && !champ.state.meta.isValid ? (
          champ.state.meta.errors.length > 1 ? (
            <ul>
              {champ.state.meta.errors.map(
                (error, index) =>
                  error.message && (
                    <li key={`erreur-${index}`}>{error.message}</li>
                  ),
              )}
            </ul>
          ) : (
            <>champ.state.meta.errors.at(0).message ?? ""</>
          )
        ) : (
          ""
        )
      }
      {...props}
    />
  );
};
